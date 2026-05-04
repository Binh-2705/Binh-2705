<?php

namespace App\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AttendanceService
{
    public function __construct(private InternalApiClient $client) {}

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->client->paginate('biz/attendance/paginate', [
            'filters' => $filters,
            'perPage' => $perPage,
            'page'    => request()->input('page', 1),
        ]);
    }

    public function find(int $attendanceId): ?array
    {
        try {
            return $this->client->get("biz/attendance/{$attendanceId}")['data'] ?? null;
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return null;
        }
    }

    public function employeeOptions(): array
    {
        $data = $this->client->get('biz/attendance/employee-options')['data'] ?? [];
        return array_map(fn($e) => (object) $e, $data);
    }

    public function create(array $payload): int
    {
        return (int) ($this->client->post('biz/attendance', $payload)['id'] ?? 0);
    }

    public function update(int $attendanceId, array $payload): void
    {
        $this->client->put("biz/attendance/{$attendanceId}", $payload);
    }

    public function delete(int $attendanceId): void
    {
        $this->client->delete("biz/attendance/{$attendanceId}");
    }

    public function workedDaysSummary(int $maNV, int $month, int $year): array
    {
        return $this->client->get('biz/attendance/worked-days', ['ma_nv' => $maNV, 'month' => $month, 'year' => $year])['data'] ?? [];
    }

    public function exportRows(array $filters = []): array
    {
        return $this->client->get('biz/attendance/export-rows', $filters)['data'] ?? [];
    }

    public function workedDaysByMonth(int $maNV, int $month, ?int $year = null): array
    {
        return $this->client->get('biz/attendance/worked-days', [
            'ma_nv' => $maNV,
            'month' => $month,
            'year'  => $year ?? (int) now()->year,
        ])['data'] ?? [];
    }

    public function monthlyAttendanceMatrix(int $month, int $year, ?int $maNV = null): array
    {
        $params = ['month' => $month, 'year' => $year];
        if ($maNV !== null) {
            $params['ma_nv'] = $maNV;
        }
        return $this->client->get('biz/attendance/monthly-matrix', $params)['data'] ?? [];
    }
}
