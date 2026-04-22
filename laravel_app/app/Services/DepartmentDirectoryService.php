<?php

namespace App\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DepartmentDirectoryService
{
    private function connection(): string
    {
        return (string) config('service_registry.services.hr.connection', config('database.default'));
    }

    public function paginate(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        return DB::connection($this->connection())
            ->table('phongban as pb')
            ->leftJoin('hosonhanvien as hs', 'pb.MaPB', '=', 'hs.MaPB')
            ->select([
                'pb.MaPB',
                'pb.TenPB',
                'pb.MoTa',
                DB::raw('COUNT(DISTINCT hs.MaNV) as SoNhanVien'),
            ])
            ->when(!empty($filters['q']), function (Builder $query) use ($filters) {
                $keyword = trim((string) $filters['q']);
                $query->where(function (Builder $inner) use ($keyword) {
                    $inner->where('pb.TenPB', 'like', "%{$keyword}%")
                        ->orWhere('pb.MoTa', 'like', "%{$keyword}%");
                });
            })
            ->groupBy('pb.MaPB', 'pb.TenPB', 'pb.MoTa')
            ->orderBy('pb.MaPB')
                ->paginate($perPage);
    }

    public function find(int $departmentId): ?array
    {
        $item = DB::connection($this->connection())
            ->table('phongban')
            ->where('MaPB', $departmentId)
            ->first();

        return $item ? (array) $item : null;
    }

    public function create(array $payload): int
    {
        return (int) DB::connection($this->connection())
            ->table('phongban')
            ->insertGetId([
                'TenPB' => $payload['TenPB'],
                'MoTa' => $payload['MoTa'] ?? null,
            ], 'MaPB');
    }

    public function update(int $departmentId, array $payload): void
    {
        DB::connection($this->connection())
            ->table('phongban')
            ->where('MaPB', $departmentId)
            ->update([
                'TenPB' => $payload['TenPB'],
                'MoTa' => $payload['MoTa'] ?? null,
            ]);
    }

    public function delete(int $departmentId): void
    {
        DB::connection($this->connection())
            ->table('phongban')
            ->where('MaPB', $departmentId)
            ->delete();
    }

    public function exportRows(array $filters = []): Collection
    {
        return DB::connection($this->connection())
            ->table('phongban as pb')
            ->when(!empty($filters['q']), function (Builder $query) use ($filters) {
                $keyword = trim((string) $filters['q']);
                $query->where(function (Builder $inner) use ($keyword) {
                    $inner->where('pb.TenPB', 'like', "%{$keyword}%")
                        ->orWhere('pb.MoTa', 'like', "%{$keyword}%");
                });
            })
            ->orderBy('pb.MaPB')
            ->get(['pb.MaPB', 'pb.TenPB', 'pb.MoTa']);
    }

    public function importRows(array $rows): int
    {
        $count = 0;

        DB::connection($this->connection())->transaction(function () use ($rows, &$count) {
            foreach ($rows as $row) {
                $name = trim((string) ($row['TenPB'] ?? ''));
                if ($name === '') {
                    continue;
                }

                DB::connection($this->connection())
                    ->table('phongban')
                    ->insert([
                        'TenPB' => $name,
                        'MoTa' => trim((string) ($row['MoTa'] ?? '')) ?: null,
                    ]);

                $count++;
            }
        });

        return $count;
    }
}