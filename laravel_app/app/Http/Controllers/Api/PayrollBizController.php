<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayrollBizController extends Controller
{
    private function conn(): string
    {
        return (string) config('service_registry.services.payroll.connection', config('database.default'));
    }

    private function attendanceConn(): string
    {
        return (string) config('service_registry.services.attendance.connection', config('database.default'));
    }

    private function hrConn(): string
    {
        return (string) config('service_registry.services.hr.connection', config('database.default'));
    }

    private function baseQuery(): Builder
    {
        return DB::connection($this->conn())
            ->table('bangluong as bl')
            ->join('nhanvien as nv', 'nv.MaNV', '=', 'bl.MaNV')
            ->select([
                'bl.MaBL', 'bl.MaNV', 'bl.Thang', 'bl.Nam',
                'bl.LuongCoSo', 'bl.HeSoLuong', 'bl.HeSoChucVu',
                'bl.PhuCap', 'bl.Thuong', 'bl.Phat', 'bl.BaoHiem',
                'bl.TongLuong', 'bl.TrangThai', 'bl.NgayTinh', 'nv.HoTen',
            ]);
    }

    public function paginate(Request $request): JsonResponse
    {
        $filters = (array) $request->input('filters', []);
        $perPage = max(1, min((int) $request->input('perPage', 15), 100));
        $page    = max(1, (int) $request->input('page', 1));

        $query = $this->baseQuery()
            ->when(!empty($filters['ma_nv']), fn (Builder $q) => $q->where('bl.MaNV', (int) $filters['ma_nv']))
            ->when(!empty($filters['q']), function (Builder $q) use ($filters) {
                $kw = trim((string) $filters['q']);
                $q->where(fn (Builder $i) => $i->where('nv.HoTen', 'like', "%{$kw}%")->orWhere('bl.MaNV', 'like', "%{$kw}%"));
            })
            ->when(!empty($filters['month']), fn (Builder $q) => $q->where('bl.Thang', (int) $filters['month']))
            ->when(!empty($filters['year']),  fn (Builder $q) => $q->where('bl.Nam',   (int) $filters['year']))
            ->when(!empty($filters['status']), fn (Builder $q) => $q->where('bl.TrangThai', $filters['status']))
            ->orderByDesc('bl.Nam')->orderByDesc('bl.Thang')->orderBy('bl.MaNV');

        $total = (clone $query)->count();
        $data  = $query->forPage($page, $perPage)->get()->map(fn ($r) => (array) $r)->all();

        return response()->json(['ok' => true, 'data' => $data, 'total' => $total, 'per_page' => $perPage, 'current_page' => $page]);
    }

    public function show(int $id): JsonResponse
    {
        $item = $this->baseQuery()->where('bl.MaBL', $id)->first();
        if (!$item) {
            return response()->json(['ok' => false, 'message' => 'Bang luong khong ton tai.'], 404);
        }
        return response()->json(['ok' => true, 'data' => (array) $item]);
    }

    public function employeeOptions(): JsonResponse
    {
        $opts = DB::connection($this->conn())->table('nhanvien')->orderBy('HoTen')->get(['MaNV', 'HoTen']);
        return response()->json(['ok' => true, 'data' => $opts]);
    }

    public function store(Request $request): JsonResponse
    {
        $payload = (array) $request->json()->all();
        $id = (int) DB::connection($this->conn())->table('bangluong')->insertGetId($payload, 'MaBL');
        return response()->json(['ok' => true, 'id' => $id], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $payload = (array) $request->json()->all();
        DB::connection($this->conn())->table('bangluong')->where('MaBL', $id)->update($payload);
        return response()->json(['ok' => true]);
    }

    public function runMonthly(Request $request): JsonResponse
    {
        $month = (int) $request->input('month', date('n'));
        $year  = (int) $request->input('year',  date('Y'));

        $employeeIds = DB::connection($this->conn())
            ->table('nhanvien')->where('TrangThai', 'Đang làm')->pluck('MaNV')
            ->merge(DB::connection($this->attendanceConn())->table('chamcong')->distinct()->pluck('MaNV'))
            ->unique()->filter()->values();

        foreach ($employeeIds as $empId) {
            $this->upsertMonthlyPayroll((int) $empId, $month, $year);
        }

        return response()->json(['ok' => true, 'processed' => $employeeIds->count(), 'month' => $month, 'year' => $year]);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function upsertMonthlyPayroll(int $empId, int $month, int $year): void
    {
        $salary = $this->calculatePayroll($empId, $month, $year);

        DB::connection($this->conn())->table('bangluong')->updateOrInsert(
            ['MaNV' => $empId, 'Thang' => $month, 'Nam' => $year],
            array_merge($salary, ['TrangThai' => 'Chưa chốt', 'NgayTinh' => now()])
        );
    }

    private function calculatePayroll(int $empId, int $month, int $year): array
    {
        $insurance   = $this->insuranceAmount($empId, $month, $year);
        $contract    = $this->contractSalaryInfo($empId, $month, $year);
        $attendance  = $this->attendanceSummary($empId, $month, $year);
        $bonusPenalty = $this->bonusPenalty($empId, $month, $year);

        $standardDays   = 26;
        $baseSalary     = (float) $contract['LuongCoSo'] * (float) $contract['HeSoLuong'];
        $allowance      = (float) $contract['PhuCap'];
        $monthlySalary  = $baseSalary + $allowance;
        $dailySalary    = $standardDays > 0 ? $monthlySalary / $standardDays : 0;
        $actualDays     = (float) ($attendance['SoNgayCong'] ?? 0);

        $salaryByAttendance = $actualDays < $standardDays ? $dailySalary * $actualDays : $monthlySalary;
        $overtimeDays       = $actualDays > $standardDays ? $actualDays - $standardDays : 0;
        $overtimeByDay      = $overtimeDays * $dailySalary * 1.5;
        $overtimeByHour     = (float) ($attendance['GioOT'] ?? 0) * ($dailySalary / 8) * 1.5;
        $latePenalty        = $this->latePenalty($empId, $month, $year, $dailySalary);

        return [
            'LuongCoSo'   => (float) $contract['LuongCoSo'],
            'HeSoLuong'   => (float) $contract['HeSoLuong'],
            'HeSoChucVu'  => (float) $contract['HeSoChucVu'],
            'PhuCap'      => $allowance,
            'Thuong'      => (float) $bonusPenalty['Thuong'],
            'Phat'        => (float) $bonusPenalty['Phat'] + $latePenalty,
            'BaoHiem'     => $insurance,
            'TongLuong'   => $salaryByAttendance + $overtimeByDay + $overtimeByHour
                + (float) $bonusPenalty['Thuong']
                - (float) $bonusPenalty['Phat']
                - $latePenalty - $insurance,
        ];
    }

    private function contractSalaryInfo(int $empId, int $month, int $year): array
    {
        $startDate = sprintf('%04d-%02d-01', $year, $month);
        $endDate   = date('Y-m-t', strtotime($startDate));

        $item = DB::connection($this->conn())
            ->table('hopdong as hd')
            ->join('bacluong as bl', 'hd.MaBac', '=', 'bl.MaBac')
            ->leftJoin('phancong as pc', function ($join) use ($endDate) {
                $join->on('pc.MaNV', '=', 'hd.MaNV')
                    ->where('pc.NgayBatDau', '=', DB::raw("(SELECT MAX(pc2.NgayBatDau) FROM phancong pc2 WHERE pc2.MaNV = hd.MaNV AND pc2.NgayBatDau <= '{$endDate}')"));
            })
            ->leftJoin('chucvu as cv', 'cv.MaCV', '=', 'pc.MaCV')
            ->where('hd.MaNV', $empId)
            ->where('hd.NgayBatDau', '<=', $endDate)
            ->where(fn ($q) => $q->whereNull('hd.NgayKetThuc')->orWhere('hd.NgayKetThuc', '>=', $startDate))
            ->orderByDesc('hd.NgayBatDau')
            ->first(['hd.MaHopDong', 'bl.HeSoLuong', 'bl.LuongCoSo', DB::raw('IFNULL(cv.HeSoChucVu,1) as HeSoChucVu'), DB::raw('IFNULL(cv.PhuCap,0) as PhuCap')]);

        return $item ? (array) $item : ['MaHopDong' => 0, 'HeSoLuong' => 1.0, 'LuongCoSo' => 5310000, 'HeSoChucVu' => 1.0, 'PhuCap' => 0];
    }

    private function attendanceSummary(int $empId, int $month, int $year): array
    {
        $item = DB::connection($this->attendanceConn())->table('v_tonghopcong')
            ->where('MaNV', $empId)->where('Thang', $month)->where('Nam', $year)->first(['SoNgayCong', 'GioOT']);
        return $item ? (array) $item : ['SoNgayCong' => 0, 'GioOT' => 0];
    }

    private function bonusPenalty(int $empId, int $month, int $year): array
    {
        $fmt  = sprintf('%04d-%02d', $year, $month);
        $item = DB::connection($this->hrConn())->table('khenthuongkyluat as k')
            ->join('loaikhenthuongkyluat as l', 'k.MaLoai', '=', 'l.MaLoai')
            ->where('k.MaNV', $empId)
            ->whereRaw("DATE_FORMAT(k.NgayQuyetDinh, '%Y-%m') = ?", [$fmt])
            ->selectRaw("SUM(CASE WHEN l.Loai='Khen thưởng' THEN k.SoTien ELSE 0 END) AS Thuong")
            ->selectRaw("SUM(CASE WHEN l.Loai='Kỷ luật' THEN k.SoTien ELSE 0 END) AS Phat")
            ->first();
        return ['Thuong' => (float) ($item->Thuong ?? 0), 'Phat' => (float) ($item->Phat ?? 0)];
    }

    private function insuranceAmount(int $empId, int $month, int $year): float
    {
        $fmt = sprintf('%04d-%02d', $year, $month);
        return (float) DB::connection($this->hrConn())->table('baohiem')
            ->where('MaNV', $empId)->where('TrangThai', 'Đang đóng')
            ->whereRaw("DATE_FORMAT(TuNgay,'%Y-%m') <= ?", [$fmt])
            ->where(fn ($q) => $q->whereNull('DenNgay')->orWhereRaw("DATE_FORMAT(DenNgay,'%Y-%m') >= ?", [$fmt]))
            ->sum('NhanVienDong');
    }

    private function latePenalty(int $empId, int $month, int $year, float $dailySalary): float
    {
        $count = DB::connection($this->attendanceConn())->table('chamcong')
            ->where('MaNV', $empId)->whereMonth('Ngay', $month)->whereYear('Ngay', $year)
            ->where('TrangThai', 'M')->count();
        return $count * ($dailySalary * 0.1);
    }

    public function export(Request $request): JsonResponse
    {
        $rows = DB::connection($this->conn())
            ->table('bangluong as bl')
            ->join('nhanvien as nv', 'nv.MaNV', '=', 'bl.MaNV')
            ->select(['bl.MaBL', 'bl.MaNV', 'nv.HoTen', 'bl.Thang', 'bl.Nam', 'bl.TongLuong', 'bl.TrangThai'])
            ->when($request->filled('month') || $request->filled('thang'), function ($query) use ($request) {
                $query->where('bl.Thang', (int) $request->input('month', $request->input('thang')));
            })
            ->when($request->filled('year') || $request->filled('nam'), function ($query) use ($request) {
                $query->where('bl.Nam', (int) $request->input('year', $request->input('nam')));
            })
            ->orderByDesc('bl.Nam')
            ->orderByDesc('bl.Thang')
            ->orderBy('bl.MaNV')
            ->get()
            ->toArray();

        return response()->json(['data' => $rows]);
    }

    public function lock(int $id): JsonResponse
    {
        $affected = DB::connection($this->conn())
            ->table('bangluong')
            ->where('MaBL', $id)
            ->update(['TrangThai' => 'Đã chốt']);

        if ($affected === 0) {
            return response()->json(['message' => 'Không tìm thấy bảng lương.'], 404);
        }

        return response()->json(['ok' => true]);
    }

    public function unlock(int $id): JsonResponse
    {
        $affected = DB::connection($this->conn())
            ->table('bangluong')
            ->where('MaBL', $id)
            ->update(['TrangThai' => 'Chưa chốt']);

        if ($affected === 0) {
            return response()->json(['message' => 'Không tìm thấy bảng lương.'], 404);
        }

        return response()->json(['ok' => true]);
    }
}
