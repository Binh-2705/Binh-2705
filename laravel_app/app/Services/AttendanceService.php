<?php

namespace App\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    private function connection(): string
    {
        return (string) config('service_registry.services.attendance.connection', config('database.default'));
    }

    private function query(): Builder
    {
        return DB::connection($this->connection())
            ->table('chamcong as cc')
            ->join('nhanvien as nv', 'nv.MaNV', '=', 'cc.MaNV')
            ->leftJoin('hosonhanvien as hs', 'hs.MaNV', '=', 'nv.MaNV')
            ->leftJoin('phongban as pb', 'pb.MaPB', '=', 'hs.MaPB')
            ->select([
                'cc.MaCC',
                'cc.MaNV',
                'cc.Ngay',
                'cc.GioVao',
                'cc.GioRa',
                'cc.TrangThai',
                'cc.GhiChu',
                'nv.HoTen',
                'pb.TenPB',
            ]);
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->when(!empty($filters['q']), function (Builder $query) use ($filters) {
                $keyword = trim((string) $filters['q']);
                $query->where(function (Builder $inner) use ($keyword) {
                    $inner->where('nv.HoTen', 'like', "%{$keyword}%")
                        ->orWhere('cc.MaNV', 'like', "%{$keyword}%");
                });
            })
            ->when(!empty($filters['status']), function (Builder $query) use ($filters) {
                $query->where('cc.TrangThai', (string) $filters['status']);
            })
            ->when(!empty($filters['date']), function (Builder $query) use ($filters) {
                $query->whereDate('cc.Ngay', (string) $filters['date']);
            })
            ->orderByDesc('cc.Ngay')
            ->orderBy('cc.MaNV')
            ->paginate($perPage);
    }

    public function find(int $attendanceId): ?array
    {
        $item = $this->query()->where('cc.MaCC', $attendanceId)->first();

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
            ->table('chamcong')
            ->insertGetId($payload, 'MaCC');
    }

    public function update(int $attendanceId, array $payload): void
    {
        DB::connection($this->connection())
            ->table('chamcong')
            ->where('MaCC', $attendanceId)
            ->update($payload);
    }

    public function delete(int $attendanceId): void
    {
        DB::connection($this->connection())
            ->table('chamcong')
            ->where('MaCC', $attendanceId)
            ->delete();
    }

    public function workedDaysByMonth(int $employeeId, int $month, ?int $year = null): array
    {
        $targetYear = $year ?: (int) now()->year;

        $summary = DB::connection($this->connection())
            ->table('v_tonghopcong')
            ->where('MaNV', $employeeId)
            ->where('Thang', $month)
            ->where('Nam', $targetYear)
            ->first(['SoNgayCong', 'GioOT']);

        return [
            'SoNgayLam' => (float) ($summary->SoNgayCong ?? 0),
            'GioOT' => (float) ($summary->GioOT ?? 0),
            'Thang' => $month,
            'Nam' => $targetYear,
        ];
    }

    public function monthlyAttendanceMatrix(int $month, int $year, ?int $employeeId = null): array
    {
        $config = $this->attendanceConfig();
        $standardCheckIn = strtotime((string) ($config['GioChuanVao'] ?? '08:00:00'));

        $query = DB::connection($this->connection())
            ->table('nhanvien as nv')
            ->join('phancong as pc', 'pc.MaNV', '=', 'nv.MaNV')
            ->join('phongban as pb', 'pb.MaPB', '=', 'pc.MaPB')
            ->leftJoin('chamcong as cc', function ($join) use ($month, $year) {
                $join->on('cc.MaNV', '=', 'nv.MaNV')
                    ->whereMonth('cc.Ngay', $month)
                    ->whereYear('cc.Ngay', $year);
            })
            ->where('nv.TrangThai', 'Đang làm')
            ->when($employeeId !== null, fn (Builder $query) => $query->where('nv.MaNV', $employeeId))
            ->orderBy('pb.TenPB')
            ->orderBy('nv.MaNV')
            ->orderBy('cc.Ngay')
            ->get([
                'nv.MaNV',
                'nv.HoTen',
                'pb.TenPB',
                DB::raw('DAY(cc.Ngay) as Ngay'),
                'cc.TrangThai',
                'cc.GioVao',
            ]);

        $data = [];
        foreach ($query as $row) {
            $department = (string) $row->TenPB;
            $staffId = (int) $row->MaNV;

            if (!isset($data[$department][$staffId])) {
                $data[$department][$staffId] = [
                    'MaNV' => $staffId,
                    'HoTen' => $row->HoTen,
                    'Ngay' => [],
                    'TongCong' => 0,
                ];
            }

            if (empty($row->Ngay)) {
                continue;
            }

            $day = str_pad((string) $row->Ngay, 2, '0', STR_PAD_LEFT);
            if ($row->TrangThai === 'Di lam' && !empty($row->GioVao)) {
                $checkIn = strtotime((string) $row->GioVao);
                $lateMinutes = ($checkIn - $standardCheckIn) / 60;

                if ($lateMinutes <= (float) ($config['MucTre1'] ?? 0)) {
                    $workUnits = (float) ($config['CongTre1'] ?? 1);
                    $symbol = 'X';
                } elseif ($lateMinutes <= (float) ($config['MucTre2'] ?? 0)) {
                    $workUnits = (float) ($config['CongTre2'] ?? 0.5);
                    $symbol = 'M';
                } elseif ($lateMinutes <= (float) ($config['MucTre3'] ?? 0)) {
                    $workUnits = (float) ($config['CongTre3'] ?? 0.25);
                    $symbol = 'M';
                } else {
                    $workUnits = (float) ($config['CongQuaTre'] ?? 0);
                    $symbol = 'V';
                }

                $data[$department][$staffId]['Ngay'][$day] = $symbol;
                $data[$department][$staffId]['TongCong'] += $workUnits;
            } elseif ($row->TrangThai === 'Nghi phep') {
                $data[$department][$staffId]['Ngay'][$day] = 'P';
            }
        }

        foreach ($data as $department => $members) {
            $data[$department] = array_values($members);
        }

        return $data;
    }

    private function attendanceConfig(): array
    {
        $item = DB::connection($this->connection())
            ->table('cauhinh_chamcong')
            ->first();

        return $item ? (array) $item : [
            'GioChuanVao' => '08:00:00',
            'MucTre1' => 15,
            'CongTre1' => 1,
            'MucTre2' => 60,
            'CongTre2' => 0.5,
            'MucTre3' => 120,
            'CongTre3' => 0.25,
            'CongQuaTre' => 0,
        ];
    }
}