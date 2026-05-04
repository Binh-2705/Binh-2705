<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * InternalApiClient
 * ─────────────────
 * Thin HTTP wrapper that calls the application's own /api/biz/* endpoints.
 * Uses the same SERVICE_GATEWAY_TOKEN that external consumers use, so all
 * requests pass through ApiTokenMiddleware without any special bypass.
 *
 * Base URL is resolved from APP_URL (e.g. http://localhost) + /api.
 * Timeout: 10 s for queries, 60 s for long-running operations (payroll etc.).
 */
class InternalApiClient
{
    private function baseUrl(): string
    {
        return rtrim((string) config('app.url', 'http://localhost'), '/') . '/api';
    }

    private function token(): string
    {
        return (string) config('services.service_gateway.token', '');
    }

    private function client(int $timeout = 10): \Illuminate\Http\Client\PendingRequest
    {
        return Http::acceptJson()
            ->withToken($this->token())
            ->timeout($timeout);
    }

    // ─── Low-level HTTP verbs ─────────────────────────────────────────────────

    public function get(string $path, array $query = [], int $timeout = 10): array
    {
        try {
            $response = $this->client($timeout)->get($this->baseUrl() . '/' . ltrim($path, '/'), $query);
            return $this->unwrap($response, $path);
        } catch (ConnectionException $e) {
            throw new RuntimeException("API connection failed [{$path}]: " . $e->getMessage(), 0, $e);
        }
    }

    public function post(string $path, array $payload = [], int $timeout = 10): array
    {
        try {
            $response = $this->client($timeout)->post($this->baseUrl() . '/' . ltrim($path, '/'), $payload);
            return $this->unwrap($response, $path);
        } catch (ConnectionException $e) {
            throw new RuntimeException("API connection failed [{$path}]: " . $e->getMessage(), 0, $e);
        }
    }

    public function put(string $path, array $payload = [], int $timeout = 10): array
    {
        try {
            $response = $this->client($timeout)->put($this->baseUrl() . '/' . ltrim($path, '/'), $payload);
            return $this->unwrap($response, $path);
        } catch (ConnectionException $e) {
            throw new RuntimeException("API connection failed [{$path}]: " . $e->getMessage(), 0, $e);
        }
    }

    public function patch(string $path, array $payload = [], int $timeout = 10): array
    {
        try {
            $response = $this->client($timeout)->patch($this->baseUrl() . '/' . ltrim($path, '/'), $payload);
            return $this->unwrap($response, $path);
        } catch (ConnectionException $e) {
            throw new RuntimeException("API connection failed [{$path}]: " . $e->getMessage(), 0, $e);
        }
    }

    public function delete(string $path, int $timeout = 10): void
    {
        try {
            $response = $this->client($timeout)->delete($this->baseUrl() . '/' . ltrim($path, '/'));
            $this->unwrap($response, $path);
        } catch (ConnectionException $e) {
            throw new RuntimeException("API connection failed [{$path}]: " . $e->getMessage(), 0, $e);
        }
    }

    // ─── Paginator helper ─────────────────────────────────────────────────────

    /**
     * Call a paginate endpoint and reconstruct a LengthAwarePaginator
     * that Blade templates can use directly (->links(), ->appends(), etc.).
     *
     * Expected API response shape:
     *   { ok:true, data:[], total:N, per_page:N, current_page:N }
     */
    public function paginate(string $path, array $payload = []): LengthAwarePaginator
    {
        $response = $this->post($path, $payload);
        $items = array_map(fn($item) => (object) $item, $response['data'] ?? []);

        return new LengthAwarePaginator(
            items: $items,
            total: (int) ($response['total'] ?? 0),
            perPage: (int) ($response['per_page'] ?? 15),
            currentPage: (int) ($response['current_page'] ?? 1),
            options: [
                'path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(),
                'pageName' => 'page',
            ],
        );
    }

    // ─── Response unwrapper ───────────────────────────────────────────────────

    private function unwrap(\Illuminate\Http\Client\Response $response, string $path): array
    {
        if ($response->status() === 404) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException(
                "API resource not found [{$path}]"
            );
        }

        if (!$response->successful()) {
            $body = $response->json() ?? [];
            $message = (string) ($body['message'] ?? $response->body());
            throw new RuntimeException("API error [{$path}] HTTP {$response->status()}: {$message}");
        }

        $data = $response->json();
        if (!is_array($data)) {
            throw new RuntimeException("API returned non-JSON response [{$path}]");
        }

        return $data;
    }
}
