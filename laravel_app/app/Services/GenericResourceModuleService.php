<?php

namespace App\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GenericResourceModuleService
{
    public function __construct(
        private ServiceRegistry $registry,
        private ServiceResourceGateway $gateway,
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
        $resourceConfig = $this->gateway->describeResource($moduleConfig['service'], $moduleConfig['resource']);
        $resourceConfig['read_only'] = (bool) (($moduleConfig['read_only'] ?? false) || ($resourceConfig['read_only'] ?? false));

        return [
            'module' => $moduleConfig,
            'resource' => $resourceConfig,
        ];
    }

    public function paginate(string $module, array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        $meta = $this->describe($module);
        $resourceConfig = $meta['resource'];
        $primaryKeys = is_array($resourceConfig['primary_key']) ? $resourceConfig['primary_key'] : [$resourceConfig['primary_key']];
        $searchableColumns = collect($resourceConfig['columns'])
            ->filter(static fn (array $column) => !str_contains($column['type'], 'blob'))
            ->pluck('field')
            ->values();

        $query = DB::connection($resourceConfig['connection'])->table($resourceConfig['table']);

        if (!empty($filters['q'])) {
            $keyword = trim((string) $filters['q']);
            $query->where(function ($inner) use ($searchableColumns, $keyword) {
                foreach ($searchableColumns as $index => $field) {
                    $method = $index === 0 ? 'where' : 'orWhere';
                    $inner->{$method}($field, 'like', "%{$keyword}%");
                }
            });
        }

        foreach ($primaryKeys as $primaryKey) {
            $query->orderBy($primaryKey);
        }

        $paginator = $query->paginate($perPage);
        $items = collect($paginator->items())->map(function ($item) use ($resourceConfig) {
            $record = (array) $item;
            $record['__resource_id'] = $this->gateway->serializeRecordIdentifier($record, $resourceConfig);

            return (object) $record;
        });

        return new Paginator(
            $items,
            $paginator->total(),
            $paginator->perPage(),
            $paginator->currentPage(),
            ['path' => Paginator::resolveCurrentPath(), 'pageName' => 'page', 'query' => request()->query()]
        );
    }

    public function find(string $module, string $id): ?array
    {
        $moduleConfig = $this->module($module);
        $payload = $this->gateway->getRecordOrNull($moduleConfig['service'], $moduleConfig['resource'], $id);

        return $payload['data'] ?? null;
    }

    public function create(string $module, array $payload): string
    {
        $moduleConfig = $this->module($module);
        $created = $this->gateway->createRecord($moduleConfig['service'], $moduleConfig['resource'], $payload);

        return (string) ($created['record_id'] ?? data_get($created, 'data.__resource_id', ''));
    }

    public function update(string $module, string $id, array $payload): void
    {
        $moduleConfig = $this->module($module);
        $this->gateway->updateRecord($moduleConfig['service'], $moduleConfig['resource'], $id, $payload);
    }

    public function delete(string $module, string $id): void
    {
        $moduleConfig = $this->module($module);
        $this->gateway->deleteRecord($moduleConfig['service'], $moduleConfig['resource'], $id);
    }

    public function exportRows(string $module, array $filters = []): array
    {
        $meta = $this->describe($module);
        $resourceConfig = $meta['resource'];
        $primaryKeys = is_array($resourceConfig['primary_key']) ? $resourceConfig['primary_key'] : [$resourceConfig['primary_key']];
        $searchableColumns = collect($resourceConfig['columns'])
            ->filter(static fn (array $column) => !str_contains($column['type'], 'blob'))
            ->pluck('field')
            ->values();

        $query = DB::connection($resourceConfig['connection'])->table($resourceConfig['table']);

        if (!empty($filters['q'])) {
            $keyword = trim((string) $filters['q']);
            $query->where(function ($inner) use ($searchableColumns, $keyword) {
                foreach ($searchableColumns as $index => $field) {
                    $method = $index === 0 ? 'where' : 'orWhere';
                    $inner->{$method}($field, 'like', "%{$keyword}%");
                }
            });
        }

        foreach ($primaryKeys as $primaryKey) {
            $query->orderBy($primaryKey);
        }

        return [
            'meta' => $meta,
            'columns' => collect($resourceConfig['columns'])->pluck('field')->filter(fn ($field) => $field !== '__resource_id')->values()->all(),
            'rows' => $query->get()->map(fn ($row) => (array) $row),
        ];
    }
}