<?php

namespace App\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class PayrollService
{
    private function connection(): string
    {
        return (string) config('service_registry.services.payroll.connection', config('database.default'));
    }

    private function attendanceConnection(): string
    {
        return (string) config('service_registry.services.attendance.connection', config('database.default'));
    }

    private function hrConnection(): string
    {
        return (string) config('service_registry.services.hr.connection', config('database.default'));
    }

    private function query(): Builder
    {
        return DB::connection($this->connection())
            ->table('bangluong as bl')
            ->join('nhanvien as nv', 'nv.MaNV', '=', 'bl.MaNV')
            ->select([
                'bl.MaBL',
                'bl.MaNV',
                'bl.Thang',
                'bl.Nam',
                'bl.LuongCoSo',
                'bl.HeSoLuong',
                'bl.HeSoChucVu',
                'bl.PhuCap',
                'bl.Thuong',
                'bl.Phat',
                'bl.BaoHiem',
                'bl.TongLuong',
                'bl.TrangThai',
                'bl.NgayTinh',
                'nv.HoTen',
            ]);
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->when(!empty($filters['q']), function (Builder $query) use ($filters) {
                $keyword = trim((string) $filters['q']);
                $query->where(function (Builder $inner) use ($keyword) {
                    $inner->where('nv.HoTen', 'like', "%{$keyword}%")
                        ->orWhere('bl.MaNV', 'like', "%{$keyword}%");
                });
            })
            ->when(!empty($filters['month']), function (Builder $query) use ($filters) {
                $query->where('bl.Thang', (int) $filters['month']);
            })
            ->when(!empty($filters['year']), function (Builder $query) use ($filters) {
                $query->where('bl.Nam', (int) $filters['year']);
            })
            ->when(!empty($filters['status']), function (Builder $query) use ($filters) {
                $query->where('bl.TrangThai', (string) $filters['status']);
            })
            ->orderByDesc('bl.Nam')
            ->orderByDesc('bl.Thang')
            ->orderBy('bl.MaNV')
            ->paginate($perPage);
    }

    public function find(int $payrollId): ?array
    {
        $item = $this->query()->where('bl.MaBL', $payrollId)->first();

        return $item ? (array) $item : null;
    }

    public function employeeOptions()
    {
        return DB::connection($this->connection())
            ->table('nhanvien')
            ->orderBy('HoTen')
            ->get(['MaNV', 'HoTen']);
    }

    public function create(array $payload): int
    {
        return (int) DB::connection($this->connection())
            ->table('bangluong')
            ->insertGetId($payload, 'MaBL');
    }

    public function update(int $payrollId, array $payload): void
    {
        DB::connection($this->connection())
            ->table('bangluong')
            ->where('MaBL', $payrollId)
            ->update($payload);
    }

    public function processMonthlyPayroll(int $month, int $year): int
    {
        $employeeIds = DB::connection($this->connection())
            ->table('nhanvien')
            ->where('TrangThai', 'Đang làm')
            ->pluck('MaNV')
            ->merge(
                DB::connection($this->attendanceConnection())
                    ->table('chamcong')
                    ->distinct()
                    ->pluck('MaNV')
            )
            ->unique()
            ->filter()
            ->values();

        foreach ($employeeIds as $employeeId) {
            $this->upsertMonthlyPayroll((int) $employeeId, $month, $year);
        }

        return $employeeIds->count();
    }

    private function upsertMonthlyPayroll(int $employeeId, int $month, int $year): void
    {
        $salary = $this->calculatePayroll($employeeId, $month, $year);

        DB::connection($this->connection())
            ->table('bangluong')
            ->updateOrInsert(
                ['MaNV' => $employeeId, 'Thang' => $month, 'Nam' => $year],
                [
                    'LuongCoSo' => $salary['LuongCoSo'],
                    'HeSoLuong' => $salary['HeSoLuong'],
                    'HeSoChucVu' => $salary['HeSoChucVu'],
                    'PhuCap' => $salary['PhuCap'],
                    'Thuong' => $salary['Thuong'],
                    'Phat' => $salary['Phat'],
                    'BaoHiem' => $salary['BaoHiem'],
                    'TongLuong' => $salary['TongLuong'],
                    'TrangThai' => 'Chưa chốt',
                    'NgayTinh' => now(),
                ]
            );
    }

    private function calculatePayroll(int $employeeId, int $month, int $year): array
    {
        $insurance = $this->insuranceAmount($employeeId, $month, $year);
        $contract = $this->contractSalaryInfo($employeeId, $month, $year);
        $attendance = $this->attendanceSummary($employeeId, $month, $year);
        $bonusPenalty = $this->bonusPenalty($employeeId, $month, $year);

        $standardDays = 26;
        $baseSalary = (float) $contract['LuongCoSo'] * (float) $contract['HeSoLuong'];
        $allowance = (float) $contract['PhuCap'];
        $monthlySalary = $baseSalary + $allowance;
        $dailySalary = $standardDays > 0 ? $monthlySalary / $standardDays : 0;
        $actualDays = (float) ($attendance['SoNgayCong'] ?? 0);

        if ($actualDays < $standardDays) {
            $salaryByAttendance = $dailySalary * $actualDays;
            $overtimeDays = 0;
        } elseif ($actualDays == $standardDays) {
            $salaryByAttendance = $monthlySalary;
            $overtimeDays = 0;
        } else {
            $salaryByAttendance = $monthlySalary;
            $overtimeDays = $actualDays - $standardDays;
        }

        $overtimeByDay = $overtimeDays * $dailySalary * 1.5;
        $hourlySalary = $dailySalary / 8;
        $overtimeByHour = (float) ($attendance['GioOT'] ?? 0) * $hourlySalary * 1.5;
        $latePenalty = $this->latePenalty($employeeId, $month, $year, $dailySalary);

        return [
            'LuongCoSo' => (float) $contract['LuongCoSo'],
            'HeSoLuong' => (float) $contract['HeSoLuong'],
            'HeSoChucVu' => (float) $contract['HeSoChucVu'],
            'PhuCap' => $allowance,
            'Thuong' => (float) $bonusPenalty['Thuong'],
            'Phat' => (float) $bonusPenalty['Phat'] + $latePenalty,
            'BaoHiem' => $insurance,
            'TongLuong' => $salaryByAttendance
                + $overtimeByDay
                + $overtimeByHour
                + (float) $bonusPenalty['Thuong']
                - (float) $bonusPenalty['Phat']
                - $latePenalty
                - $insurance,
        ];
    }

    private function contractSalaryInfo(int $employeeId, int $month, int $year): array
    {
        $startDate = sprintf('%04d-%02d-01', $year, $month);
        $endDate = date('Y-m-t', strtotime($startDate));

        $item = DB::connection($this->connection())
            ->table('hopdong as hd')
            ->join('bacluong as bl', 'hd.MaBac', '=', 'bl.MaBac')
            ->leftJoin('phancong as pc', function ($join) use ($endDate) {
                $join->on('pc.MaNV', '=', 'hd.MaNV')
                    ->where('pc.NgayBatDau', '=', DB::raw("(
                        SELECT MAX(pc2.NgayBatDau)
                        FROM phancong pc2
                        WHERE pc2.MaNV = hd.MaNV
                        AND pc2.NgayBatDau <= '{$endDate}'
                    )"));
            })
            ->leftJoin('chucvu as cv', 'cv.MaCV', '=', 'pc.MaCV')
            ->where('hd.MaNV', $employeeId)
            ->where('hd.NgayBatDau', '<=', $endDate)
            ->where(function ($query) use ($startDate) {
                $query->whereNull('hd.NgayKetThuc')
                    ->orWhere('hd.NgayKetThuc', '>=', $startDate);
            })
            ->orderByDesc('hd.NgayBatDau')
            ->first([
                'hd.MaHopDong',
                'bl.HeSoLuong',
                'bl.LuongCoSo',
                DB::raw('IFNULL(cv.HeSoChucVu, 1) as HeSoChucVu'),
                DB::raw('IFNULL(cv.PhuCap, 0) as PhuCap'),
            ]);

        return $item ? (array) $item : [
            'MaHopDong' => 0,
            'HeSoLuong' => 1.0,
            'LuongCoSo' => 5310000,
            'HeSoChucVu' => 1.0,
            'PhuCap' => 0,
        ];
    }

    private function attendanceSummary(int $employeeId, int $month, int $year): array
    {
        $item = DB::connection($this->attendanceConnection())
            ->table('v_tonghopcong')
            ->where('MaNV', $employeeId)
            ->where('Thang', $month)
            ->where('Nam', $year)
            ->first(['SoNgayCong', 'GioOT']);

        return $item ? (array) $item : ['SoNgayCong' => 0, 'GioOT' => 0];
    }

    private function bonusPenalty(int $employeeId, int $month, int $year): array
    {
        $monthFormat = sprintf('%04d-%02d', $year, $month);
        $item = DB::connection($this->hrConnection())
            ->table('khenthuongkyluat as k')
            ->join('loaikhenthuongkyluat as l', 'k.MaLoai', '=', 'l.MaLoai')
            ->where('k.MaNV', $employeeId)
            ->whereRaw("DATE_FORMAT(k.NgayQuyetDinh, '%Y-%m') = ?", [$monthFormat])
            ->selectRaw("SUM(CASE WHEN l.Loai = 'Khen thưởng' THEN k.SoTien ELSE 0 END) AS Thuong")
            ->selectRaw("SUM(CASE WHEN l.Loai = 'Kỷ luật' THEN k.SoTien ELSE 0 END) AS Phat")
            ->first();

        return [
            'Thuong' => (float) ($item->Thuong ?? 0),
            'Phat' => (float) ($item->Phat ?? 0),
        ];
    }

    private function insuranceAmount(int $employeeId, int $month, int $year): float
    {
        $monthFormat = sprintf('%04d-%02d', $year, $month);

        return (float) DB::connection($this->hrConnection())
            ->table('baohiem')
            ->where('MaNV', $employeeId)
            ->where('TrangThai', 'Đang đóng')
            ->whereRaw("DATE_FORMAT(TuNgay, '%Y-%m') <= ?", [$monthFormat])
            ->where(function ($query) use ($monthFormat) {
                $query->whereNull('DenNgay')
                    ->orWhereRaw("DATE_FORMAT(DenNgay, '%Y-%m') >= ?", [$monthFormat]);
            })
            ->sum('NhanVienDong');
    }

    private function latePenalty(int $employeeId, int $month, int $year, float $dailySalary): float
    {
        $count = DB::connection($this->attendanceConnection())
            ->table('chamcong')
            ->where('MaNV', $employeeId)
            ->whereMonth('Ngay', $month)
            ->whereYear('Ngay', $year)
            ->where('TrangThai', 'M')
            ->count();

        return $count * ($dailySalary * 0.1);
    }
}