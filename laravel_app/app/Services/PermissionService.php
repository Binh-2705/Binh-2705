<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class PermissionService
{
    public function getPermissionsByAccountId(int $maTK): array
    {
        return DB::table('taikhoanvaitro as tkvt')
            ->join('vaitro as vt', 'tkvt.MaVaiTro', '=', 'vt.MaVaiTro')
            ->join('phanquyen as pq', 'vt.MaVaiTro', '=', 'pq.MaVaiTro')
            ->join('chucnang as cn', 'pq.MaCN', '=', 'cn.MaCN')
            ->where('tkvt.MaTK', $maTK)
            ->pluck('cn.TenChucNang')
            ->toArray();
    }

    public function hasPermission(int $maTK, string $tenChucNang): bool
    {
        return DB::table('taikhoanvaitro as tkvt')
            ->join('vaitro as vt', 'tkvt.MaVaiTro', '=', 'vt.MaVaiTro')
            ->join('phanquyen as pq', 'vt.MaVaiTro', '=', 'pq.MaVaiTro')
            ->join('chucnang as cn', 'pq.MaCN', '=', 'cn.MaCN')
            ->where('tkvt.MaTK', $maTK)
            ->where('cn.TenChucNang', $tenChucNang)
            ->exists();
    }
}
