<?php

namespace App\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class HrEmployeeService
{
    public function __construct(private InternalApiClient $client) {}

    public function paginate(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        return $this->client->paginate('biz/employees/paginate', [
            'filters' => $filters,
            'perPage' => $perPage,
            'page'    => request()->input('page', 1),
        ]);
    }

    public function find(int $employeeId): ?array
    {
        try {
            return $this->client->get("biz/employees/{$employeeId}")['data'] ?? null;
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return null;
        }
    }

    public function options(): array
    {
        $response = $this->client->get('biz/employees/options');
        return [
            'departments'  => array_map(fn($d) => (object) $d, $response['departments'] ?? []),
            'positions'    => array_map(fn($p) => (object) $p, $response['positions'] ?? []),
            'salaryGrades' => array_map(fn($g) => (object) $g, $response['salaryGrades'] ?? []),
        ];
    }

    public function salaryGradesByBand(int|string $bandId): array
    {
        return $this->client->get('biz/employees/salary-grades', ['band_id' => $bandId])['data'] ?? [];
    }

    public function create(array $payload): int
    {
        return (int) ($this->client->post('biz/employees', $payload)['id'] ?? 0);
    }

    public function update(int $employeeId, array $payload): void
    {
        $this->client->put("biz/employees/{$employeeId}", $payload);
    }

    public function delete(int $employeeId): void
    {
        $this->client->delete("biz/employees/{$employeeId}");
    }
}
