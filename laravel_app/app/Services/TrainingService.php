<?php

namespace App\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TrainingService
{
    private function connection(): string
    {
        return (string) config('service_registry.services.training.connection', config('database.default'));
    }

    private function hrConnection(): string
    {
        return (string) config('service_registry.services.hr.connection', config('database.default'));
    }

    private function query(): Builder
    {
        return DB::connection($this->connection())
            ->table('khoadaotao as kdt')
            ->leftJoin('thamgiadaotao as tg', 'tg.MaKDT', '=', 'kdt.MaKDT')
            ->select([
                'kdt.MaKDT',
                'kdt.TenKhoaDaoTao',
                'kdt.TuNgay',
                'kdt.DenNgay',
                'kdt.NoiDung',
                'kdt.DonViToChuc',
                'kdt.TrangThai',
                DB::raw('COUNT(tg.MaTGDT) as SoHocVien'),
            ])
            ->groupBy(
                'kdt.MaKDT',
                'kdt.TenKhoaDaoTao',
                'kdt.TuNgay',
                'kdt.DenNgay',
                'kdt.NoiDung',
                'kdt.DonViToChuc',
                'kdt.TrangThai'
            );
    }

    public function paginate(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        return $this->query()
            ->when(!empty($filters['q']), function (Builder $query) use ($filters) {
                $keyword = trim((string) $filters['q']);
                $query->where(function (Builder $inner) use ($keyword) {
                    $inner->where('kdt.TenKhoaDaoTao', 'like', "%{$keyword}%")
                        ->orWhere('kdt.DonViToChuc', 'like', "%{$keyword}%");
                });
            })
            ->when(!empty($filters['status']), function (Builder $query) use ($filters) {
                $query->where('kdt.TrangThai', (string) $filters['status']);
            })
            ->orderByDesc('kdt.MaKDT')
            ->paginate($perPage);
    }

    public function find(int $courseId): ?array
    {
        $item = DB::connection($this->connection())
            ->table('khoadaotao')
            ->where('MaKDT', $courseId)
            ->first();

        return $item ? (array) $item : null;
    }

    public function create(array $payload): int
    {
        return (int) DB::connection($this->connection())
            ->table('khoadaotao')
            ->insertGetId($payload, 'MaKDT');
    }

    public function update(int $courseId, array $payload): void
    {
        DB::connection($this->connection())
            ->table('khoadaotao')
            ->where('MaKDT', $courseId)
            ->update($payload);
    }

    public function delete(int $courseId): void
    {
        DB::connection($this->connection())
            ->table('khoadaotao')
            ->where('MaKDT', $courseId)
            ->delete();
    }

    public function participantsPageData(int $courseId): array
    {
        $course = $this->find($courseId);
        if ($course === null) {
            return ['course' => null, 'participants' => [], 'employees' => [], 'canEvaluate' => false];
        }

        $participants = DB::connection($this->connection())
            ->table('thamgiadaotao')
            ->where('MaKDT', $courseId)
            ->orderBy('MaTGDT')
            ->get(['MaTGDT', 'MaNV', 'MaKDT', 'KetQua', 'DiemDanhGia', 'GhiChu']);

        $employeeIds = array_values(array_unique($participants->pluck('MaNV')->map(fn ($id) => (int) $id)->all()));
        $employeesById = $this->employeesById($employeeIds);

        return [
            'course' => $course,
            'participants' => $participants->map(function ($participant) use ($employeesById) {
                return [
                    'MaTGDT' => (int) $participant->MaTGDT,
                    'MaNV' => (int) $participant->MaNV,
                    'KetQua' => (string) ($participant->KetQua ?? 'Chua danh gia'),
                    'DiemDanhGia' => $participant->DiemDanhGia !== null ? (float) $participant->DiemDanhGia : null,
                    'GhiChu' => (string) ($participant->GhiChu ?? ''),
                    'HoTen' => (string) ($employeesById[(int) $participant->MaNV]['HoTen'] ?? ('NV #' . $participant->MaNV)),
                ];
            })->all(),
            'employees' => $this->availableEmployeesForCourse($courseId),
            'canEvaluate' => isset($course['DenNgay']) && strtotime((string) $course['DenNgay']) <= strtotime(date('Y-m-d')),
        ];
    }

    public function addParticipant(int $courseId, int $employeeId): bool
    {
        $exists = DB::connection($this->connection())
            ->table('thamgiadaotao')
            ->where('MaKDT', $courseId)
            ->where('MaNV', $employeeId)
            ->exists();

        if ($exists) {
            return false;
        }

        DB::connection($this->connection())
            ->table('thamgiadaotao')
            ->insert([
                'MaNV' => $employeeId,
                'MaKDT' => $courseId,
                'KetQua' => 'Chưa đánh giá',
                'DiemDanhGia' => null,
                'GhiChu' => null,
            ]);

        return true;
    }

    public function updateParticipantResult(int $participantId, array $payload): void
    {
        DB::connection($this->connection())
            ->table('thamgiadaotao')
            ->where('MaTGDT', $participantId)
            ->update($payload);
    }

    private function availableEmployeesForCourse(int $courseId): array
    {
        $assignedEmployeeIds = DB::connection($this->connection())
            ->table('thamgiadaotao')
            ->where('MaKDT', $courseId)
            ->pluck('MaNV')
            ->map(fn ($id) => (int) $id)
            ->all();

        return DB::connection($this->hrConnection())
            ->table('nhanvien')
            ->when($assignedEmployeeIds !== [], fn (Builder $query) => $query->whereNotIn('MaNV', $assignedEmployeeIds))
            ->where('TrangThai', 'Đang làm')
            ->orderBy('HoTen')
            ->get(['MaNV', 'HoTen'])
            ->map(fn ($employee) => [
                'MaNV' => (int) $employee->MaNV,
                'HoTen' => (string) $employee->HoTen,
            ])
            ->all();
    }

    private function employeesById(array $employeeIds): array
    {
        if ($employeeIds === []) {
            return [];
        }

        return DB::connection($this->hrConnection())
            ->table('nhanvien')
            ->whereIn('MaNV', $employeeIds)
            ->get(['MaNV', 'HoTen'])
            ->mapWithKeys(fn ($employee) => [
                (int) $employee->MaNV => [
                    'MaNV' => (int) $employee->MaNV,
                    'HoTen' => (string) $employee->HoTen,
                ],
            ])
            ->all();
    }
}