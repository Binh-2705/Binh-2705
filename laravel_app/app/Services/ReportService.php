<?php

namespace App\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportService
{
    private function connection(): string
    {
        return (string) config('service_registry.services.reporting.connection', config('database.default'));
    }

    public function paginate(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        return DB::connection($this->connection())
            ->table('baocao')
            ->when(!empty($filters['q']), function (Builder $query) use ($filters) {
                $keyword = trim((string) $filters['q']);
                $query->where(function (Builder $inner) use ($keyword) {
                    $inner->where('TenBaoCao', 'like', "%{$keyword}%")
                        ->orWhere('NguoiTao', 'like', "%{$keyword}%");
                });
            })
            ->when(!empty($filters['type']), function (Builder $query) use ($filters) {
                $query->where('LoaiBaoCao', (string) $filters['type']);
            })
            ->orderByDesc('MaBC')
            ->paginate($perPage);
    }

    public function find(int $reportId): ?array
    {
        $item = DB::connection($this->connection())
            ->table('baocao')
            ->where('MaBC', $reportId)
            ->first();

        return $item ? (array) $item : null;
    }

    public function create(array $payload): int
    {
        return (int) DB::connection($this->connection())
            ->table('baocao')
            ->insertGetId($payload, 'MaBC');
    }

    public function update(int $reportId, array $payload): void
    {
        DB::connection($this->connection())
            ->table('baocao')
            ->where('MaBC', $reportId)
            ->update($payload);
    }

    public function delete(int $reportId): void
    {
        DB::connection($this->connection())
            ->table('baocao')
            ->where('MaBC', $reportId)
            ->delete();
    }

    public function exportRows(array $filters = []): Collection
    {
        return DB::connection($this->connection())
            ->table('baocao')
            ->when(!empty($filters['q']), function (Builder $query) use ($filters) {
                $keyword = trim((string) $filters['q']);
                $query->where(function (Builder $inner) use ($keyword) {
                    $inner->where('TenBaoCao', 'like', "%{$keyword}%")
                        ->orWhere('NguoiTao', 'like', "%{$keyword}%");
                });
            })
            ->when(!empty($filters['type']), function (Builder $query) use ($filters) {
                $query->where('LoaiBaoCao', (string) $filters['type']);
            })
            ->orderByDesc('MaBC')
            ->get();
    }
}