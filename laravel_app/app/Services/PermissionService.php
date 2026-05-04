<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class PermissionService
{
    public function __construct(private InternalApiClient $client) {}

    public function getPermissionsByAccountId(int $maTK): array
    {
        return Cache::remember("permissions_tk_{$maTK}", 300, fn () =>
            $this->client->get('biz/permissions', ['ma_tk' => $maTK])['permissions'] ?? []
        );
    }

    public function clearPermissionsCache(int $maTK): void
    {
        Cache::forget("permissions_tk_{$maTK}");
    }

    public function hasPermission(int $maTK, string $tenChucNang): bool
    {
        return in_array($tenChucNang, $this->getPermissionsByAccountId($maTK), true);
    }

    public function hasPermissionFromCache(int $maTK, string $tenChucNang): bool
    {
        return $this->hasPermission($maTK, $tenChucNang);
    }
}
