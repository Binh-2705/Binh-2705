<?php

namespace App\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;

class GenericResourceModuleService
{
    public function __construct(
        private InternalApiClient $client,
    ) {
    }

    public function module(string $module): array
    {
        $config = config("laravel_resource_modules.{$module}");
        abort_unless(is_array($config), 404);

        return $config;
    }

    public function describe(string $module): array
    {
        $moduleConfig = $this->module($module);
        $response = $this->client->get(
            sprintf('services/%s/%s/meta', $moduleConfig['service'], $moduleConfig['resource'])
        );
        $resourceConfig = (array) ($response['data'] ?? []);
        $resourceConfig['read_only'] = (bool) (($moduleConfig['read_only'] ?? false) || ($resourceConfig['read_only'] ?? false));

        return [
            'module' => $moduleConfig,
            'resource' => $resourceConfig,
        ];
    }

    public function paginate(string $module, array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        $meta = $this->describe($module);
        $moduleConfig = $meta['module'];
        $currentPage = max(1, (int) request()->query('page', 1));
        $response = $this->client->get(
            sprintf('services/%s/%s', $moduleConfig['service'], $moduleConfig['resource']),
            array_filter([
                'page' => $currentPage,
                'limit' => $perPage,
                'q' => trim((string) ($filters['q'] ?? '')),
                'ma_nv' => isset($filters['ma_nv']) ? (int) $filters['ma_nv'] : null,
            ], fn ($v) => $v !== null && $v !== '')
        );
        $items = collect($response['data'] ?? [])->map(fn ($item) => (object) $item);
        $pagination = (array) ($response['pagination'] ?? []);

        return new Paginator(
            $items,
            (int) ($pagination['total'] ?? 0),
            (int) ($pagination['limit'] ?? $perPage),
            (int) ($pagination['page'] ?? $currentPage),
            ['path' => Paginator::resolveCurrentPath(), 'pageName' => 'page', 'query' => request()->query()]
        );
    }

    public function find(string $module, string $id): ?array
    {
        $moduleConfig = $this->module($module);
        try {
            $payload = $this->client->get(sprintf('services/%s/%s/%s', $moduleConfig['service'], $moduleConfig['resource'], $id));
        } catch (ModelNotFoundException) {
            return null;
        }

        return $payload['data'] ?? null;
    }

    public function create(string $module, array $payload): string
    {
        $moduleConfig = $this->module($module);
        $created = $this->client->post(sprintf('services/%s/%s', $moduleConfig['service'], $moduleConfig['resource']), $payload);

        return (string) ($created['record_id'] ?? data_get($created, 'data.__resource_id', ''));
    }

    public function update(string $module, string $id, array $payload): void
    {
        $moduleConfig = $this->module($module);
        $this->client->put(sprintf('services/%s/%s/%s', $moduleConfig['service'], $moduleConfig['resource'], $id), $payload);
    }

    public function delete(string $module, string $id): void
    {
        $moduleConfig = $this->module($module);
        $this->client->delete(sprintf('services/%s/%s/%s', $moduleConfig['service'], $moduleConfig['resource'], $id));
    }

    public function exportRows(string $module, array $filters = []): array
    {
        $meta = $this->describe($module);
        $moduleConfig = $meta['module'];
        $response = $this->client->get(
            sprintf('services/%s/%s/export', $moduleConfig['service'], $moduleConfig['resource']),
            ['q' => trim((string) ($filters['q'] ?? ''))]
        );

        return [
            'meta' => $meta,
            'columns' => collect($response['columns'] ?? [])->filter(fn ($field) => $field !== '__resource_id')->values()->all(),
            'rows' => collect($response['rows'] ?? [])->map(fn ($row) => (array) $row),
        ];
    }
}