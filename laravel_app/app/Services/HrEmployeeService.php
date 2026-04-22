<?php

namespace App\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class HrEmployeeService
{
    private function connection(): string
    {
        return (string) config('service_registry.services.hr.connection', config('database.default'));
    }

    private function latestAssignmentSubquery(): Builder
    {
        return DB::connection($this->connection())
            ->table('phancong as pc1')
            ->select('pc1.MaNV', DB::raw('MAX(pc1.MaQT) as LatestAssignmentId'))
            ->groupBy('pc1.MaNV');
    }

    private function employeeQuery(): Builder
    {
        return DB::connection($this->connection())
            ->table('nhanvien as nv')
            ->leftJoin('bacluong as bl', 'nv.MaBac', '=', 'bl.MaBac')
            ->leftJoin('ngachluong as nl', 'bl.MaNgach', '=', 'nl.MaNgach')
            ->leftJoin('hosonhanvien as hs', 'nv.MaNV', '=', 'hs.MaNV')
            ->leftJoinSub($this->latestAssignmentSubquery(), 'latest_pc', function ($join) {
                $join->on('latest_pc.MaNV', '=', 'nv.MaNV');
            })
            ->leftJoin('phancong as pc', 'pc.MaQT', '=', 'latest_pc.LatestAssignmentId')
            ->leftJoin('phongban as pb', 'pb.MaPB', '=', 'pc.MaPB')
            ->leftJoin('chucvu as cv', 'cv.MaCV', '=', 'pc.MaCV')
            ->select([
                'nv.MaNV',
                'nv.HoTen',
                'nv.GioiTinh',
                'nv.NgaySinh',
                'nv.Email',
                'nv.DienThoai',
                'nv.TrangThai',
                'nv.MaBac',
                'bl.TenBac',
                'nl.TenNgach',
                'hs.DiaChi',
                'hs.NgayVaoLam',
                'pb.MaPB as CurrentMaPB',
                'pb.TenPB',
                'cv.MaCV as CurrentMaCV',
                'cv.TenCV',
            ]);
    }

    public function paginate(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        return $this->employeeQuery()
            ->when(!empty($filters['q']), function (Builder $query) use ($filters) {
                $keyword = trim((string) $filters['q']);
                $query->where(function (Builder $inner) use ($keyword) {
                    $inner->where('nv.HoTen', 'like', "%{$keyword}%")
                        ->orWhere('nv.Email', 'like', "%{$keyword}%")
                        ->orWhere('nv.DienThoai', 'like', "%{$keyword}%")
                        ->orWhere('nv.MaNV', 'like', "%{$keyword}%");
                });
            })
            ->when(!empty($filters['status']), function (Builder $query) use ($filters) {
                $query->where('nv.TrangThai', (string) $filters['status']);
            })
            ->when(!empty($filters['department']), function (Builder $query) use ($filters) {
                $query->where('pb.MaPB', (int) $filters['department']);
            })
            ->orderBy('nv.MaNV')
                ->paginate($perPage);
    }

    public function find(int $employeeId): ?array
    {
        $item = $this->employeeQuery()
            ->where('nv.MaNV', $employeeId)
            ->first();

        return $item ? (array) $item : null;
    }

    public function options(): array
    {
        $connection = DB::connection($this->connection());

        return [
            'departments' => $connection->table('phongban')->orderBy('TenPB')->get(),
            'positions' => $connection->table('chucvu')->orderBy('TenCV')->get(),
            'salaryGrades' => $connection->table('bacluong')->orderBy('MaBac')->get(),
        ];
    }

    public function salaryGradesByBand(int|string $bandId): Collection
    {
        return DB::connection($this->connection())
            ->table('bacluong')
            ->where('MaNgach', $bandId)
            ->orderBy('HeSoLuong')
            ->get(['MaBac', 'TenBac', 'HeSoLuong']);
    }

    public function create(array $payload): int
    {
        return DB::connection($this->connection())->transaction(function () use ($payload) {
            $connection = DB::connection($this->connection());
            $employeeId = (int) $connection->table('nhanvien')->insertGetId([
                'HoTen' => $payload['HoTen'],
                'GioiTinh' => $payload['GioiTinh'] ?? null,
                'NgaySinh' => $payload['NgaySinh'] ?? null,
                'Email' => $payload['Email'] ?? null,
                'DienThoai' => $payload['DienThoai'] ?? null,
                'TrangThai' => $payload['TrangThai'],
                'MaBac' => $payload['MaBac'] ?? null,
                'MaHS' => null,
            ], 'MaNV');

            $this->upsertProfile($employeeId, $payload);
            $this->upsertAssignment($employeeId, $payload, true);

            return $employeeId;
        });
    }

    public function update(int $employeeId, array $payload): void
    {
        DB::connection($this->connection())->transaction(function () use ($employeeId, $payload) {
            $connection = DB::connection($this->connection());
            $connection->table('nhanvien')
                ->where('MaNV', $employeeId)
                ->update([
                    'HoTen' => $payload['HoTen'],
                    'GioiTinh' => $payload['GioiTinh'] ?? null,
                    'NgaySinh' => $payload['NgaySinh'] ?? null,
                    'Email' => $payload['Email'] ?? null,
                    'DienThoai' => $payload['DienThoai'] ?? null,
                    'TrangThai' => $payload['TrangThai'],
                    'MaBac' => $payload['MaBac'] ?? null,
                ]);

            $this->upsertProfile($employeeId, $payload);
            $this->upsertAssignment($employeeId, $payload, false);
        });
    }

    public function delete(int $employeeId): void
    {
        DB::connection($this->connection())->table('nhanvien')
            ->where('MaNV', $employeeId)
            ->delete();
    }

    private function upsertProfile(int $employeeId, array $payload): void
    {
        $profilePayload = [
            'MaNV' => $employeeId,
            'DiaChi' => $payload['DiaChi'] ?? null,
            'NgayVaoLam' => $payload['NgayVaoLam'] ?? null,
            'MaPB' => $payload['MaPB'] ?? null,
            'MaCV' => $payload['MaCV'] ?? null,
        ];

        $hasProfileData = collect($profilePayload)
            ->except('MaNV')
            ->filter(static fn($value) => $value !== null && $value !== '')
            ->isNotEmpty();

        if (!$hasProfileData) {
            return;
        }

        DB::connection($this->connection())
            ->table('hosonhanvien')
            ->updateOrInsert(['MaNV' => $employeeId], $profilePayload);
    }

    private function upsertAssignment(int $employeeId, array $payload, bool $isCreate): void
    {
        if (empty($payload['MaPB']) || empty($payload['MaCV'])) {
            return;
        }

        $connection = DB::connection($this->connection());
        $latestAssignmentId = $connection->table('phancong')
            ->where('MaNV', $employeeId)
            ->max('MaQT');

        $assignmentPayload = [
            'MaNV' => $employeeId,
            'MaPB' => (int) $payload['MaPB'],
            'MaCV' => (int) $payload['MaCV'],
            'NgayBatDau' => $payload['NgayVaoLam'] ?? now()->toDateString(),
            'NgayKetThuc' => null,
            'LyDoThayDoi' => $isCreate ? 'Khoi tao tu Laravel' : 'Cap nhat tu Laravel',
            'LoaiDieuChuyen' => $isCreate ? 'Tuyen dung' : 'Dieu chinh',
        ];

        if ($latestAssignmentId) {
            $connection->table('phancong')
                ->where('MaQT', $latestAssignmentId)
                ->update($assignmentPayload);

            return;
        }

        $connection->table('phancong')->insert($assignmentPayload);
    }
}