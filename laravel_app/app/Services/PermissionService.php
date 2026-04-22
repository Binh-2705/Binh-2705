<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PermissionService
{
    private function hrConnection(): string
    {
        return (string) config('service_registry.services.hr.connection', config('database.default'));
    }

    public function getPermissionsByAccountId(int $maTK): array
    {
        return Cache::remember("permissions_tk_{$maTK}", 300, function () use ($maTK) {
            return DB::connection($this->hrConnection())
                ->table('taikhoanvaitro as tkvt')
                ->join('vaitro as vt', 'tkvt.MaVaiTro', '=', 'vt.MaVaiTro')
                ->join('phanquyen as pq', 'vt.MaVaiTro', '=', 'pq.MaVaiTro')
                ->join('chucnang as cn', 'pq.MaCN', '=', 'cn.MaCN')
                ->where('tkvt.MaTK', $maTK)
                ->pluck('cn.TenChucNang')
                ->toArray();
        });
    }

    public function clearPermissionsCache(int $maTK): void
    {
        Cache::forget("permissions_tk_{$maTK}");
    }

    public function hasPermission(int $maTK, string $tenChucNang): bool
    {
        return $this->hasPermissionFromCache($maTK, $tenChucNang);
    }

    public function hasPermissionFromCache(int $maTK, string $tenChucNang): bool
    {
        $permissions = $this->getPermissionsByAccountId($maTK);
        return in_array($tenChucNang, $permissions, true);
    }
}
