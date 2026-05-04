<?php

namespace App\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PayrollService
{
    public function __construct(private InternalApiClient $client) {}

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->client->paginate('biz/payroll/paginate', [
            'filters' => $filters,
            'perPage' => $perPage,
            'page'    => request()->input('page', 1),
        ]);
    }

    public function find(int $payrollId): ?array
    {
        try {
            return $this->client->get("biz/payroll/{$payrollId}")['data'] ?? null;
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return null;
        }
    }

    public function employeeOptions(): array
    {
        $data = $this->client->get('biz/payroll/employee-options')['data'] ?? [];
        return array_map(fn($e) => (object) $e, $data);
    }

    public function create(array $payload): int
    {
        return (int) ($this->client->post('biz/payroll', $payload)['id'] ?? 0);
    }

    public function update(int $payrollId, array $payload): void
    {
        $this->client->put("biz/payroll/{$payrollId}", $payload);
    }

    public function runMonthly(int $month, int $year): array
    {
        return $this->client->post('biz/payroll/run-monthly', ['month' => $month, 'year' => $year], 120);
    }

    public function processMonthlyPayroll(int $month, int $year): int
    {
        $result = $this->runMonthly($month, $year);
        return (int) ($result['processed'] ?? $result['count'] ?? 0);
    }
}
