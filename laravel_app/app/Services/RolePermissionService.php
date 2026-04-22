<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class RolePermissionService
{
    private function hrConnection(): string
    {
        return (string) config('service_registry.services.hr.connection', config('database.default'));
    }

    public function indexData(): array
    {
        $roles = DB::connection($this->hrConnection())
            ->table('vaitro')
            ->orderBy('TenVaiTro')
            ->get(['MaVaiTro', 'TenVaiTro'])
            ->map(fn ($role) => [
                'MaVaiTro' => (int) $role->MaVaiTro,
                'TenVaiTro' => (string) $role->TenVaiTro,
            ])
            ->all();

        $functions = DB::connection($this->hrConnection())
            ->table('chucnang')
            ->orderBy('TenChucNang')
            ->get(['MaCN', 'TenChucNang'])
            ->map(fn ($function) => [
                'MaCN' => (int) $function->MaCN,
                'TenChucNang' => (string) $function->TenChucNang,
            ])
            ->all();

        $permissions = DB::connection($this->hrConnection())
            ->table('phanquyen')
            ->get(['MaVaiTro', 'MaCN'])
            ->groupBy('MaVaiTro')
            ->map(fn ($rows) => $rows->pluck('MaCN')->map(fn ($id) => (int) $id)->all())
            ->all();

        return [
            'roles' => $roles,
            'functions' => $functions,
            'permissionsByRole' => $permissions,
            'groupOrder' => $this->groupOrder(),
        ];
    }

    public function accountDetail(int $accountId): array
    {
        $roles = DB::connection($this->hrConnection())
            ->table('taikhoanvaitro as tkvt')
            ->join('vaitro as vt', 'tkvt.MaVaiTro', '=', 'vt.MaVaiTro')
            ->where('tkvt.MaTK', $accountId)
            ->orderBy('vt.TenVaiTro')
            ->pluck('vt.TenVaiTro')
            ->map(fn ($name) => (string) $name)
            ->all();

        $permissions = DB::connection($this->hrConnection())
            ->table('taikhoanvaitro as tkvt')
            ->join('phanquyen as pq', 'tkvt.MaVaiTro', '=', 'pq.MaVaiTro')
            ->join('chucnang as cn', 'pq.MaCN', '=', 'cn.MaCN')
            ->where('tkvt.MaTK', $accountId)
            ->distinct()
            ->orderBy('cn.TenChucNang')
            ->pluck('cn.TenChucNang')
            ->map(fn ($name) => (string) $name)
            ->all();

        return [
            'accountId' => $accountId,
            'roles' => $roles,
            'permissions' => $permissions,
        ];
    }

    public function updateRolePermissions(int $roleId, array $functionIds): void
    {
        $normalizedFunctionIds = array_values(array_unique(array_map('intval', $functionIds)));

        DB::connection($this->hrConnection())->transaction(function () use ($roleId, $normalizedFunctionIds) {
            DB::connection($this->hrConnection())
                ->table('phanquyen')
                ->where('MaVaiTro', $roleId)
                ->delete();

            if ($normalizedFunctionIds === []) {
                return;
            }

            DB::connection($this->hrConnection())
                ->table('phanquyen')
                ->insert(array_map(fn (int $functionId) => [
                    'MaVaiTro' => $roleId,
                    'MaCN' => $functionId,
                ], $normalizedFunctionIds));
        });
    }

    public function restoreDefaultPermissions(int $roleId): bool
    {
        $defaults = $this->defaultPermissionsFromSeed($roleId);

        if ($defaults === []) {
            return false;
        }

        $this->updateRolePermissions($roleId, $defaults);

        return true;
    }

    public function groupFunctions(array $functions): array
    {
        $grouped = [];
        foreach ($functions as $function) {
            $group = $this->permissionGroupLabel((string) ($function['TenChucNang'] ?? ''));
            $grouped[$group] ??= [];
            $grouped[$group][] = $function;
        }

        return $grouped;
    }

    private function defaultPermissionsFromSeed(int $roleId): array
    {
        $seedPath = dirname(base_path()) . DIRECTORY_SEPARATOR . 'database.sql';
        if (!is_file($seedPath)) {
            return [];
        }

        $sql = file_get_contents($seedPath);
        if ($sql === false) {
            return [];
        }

        preg_match_all(
            '/INSERT\s+INTO\s+`?phanquyen`?\s*(?:\(\s*`?MaVaiTro`?\s*,\s*`?MaCN`?\s*\))?\s*VALUES\s*(.+?);/is',
            $sql,
            $matches,
            PREG_SET_ORDER
        );

        $defaults = [];
        foreach ($matches as $insertMatch) {
            $valuesBlock = $insertMatch[1] ?? '';
            preg_match_all('/\((\d+)\s*,\s*(\d+)\)/', $valuesBlock, $pairs, PREG_SET_ORDER);

            foreach ($pairs as $pair) {
                if ((int) $pair[1] === $roleId) {
                    $defaults[] = (int) $pair[2];
                }
            }
        }

        return array_values(array_unique($defaults));
    }

    private function permissionGroupLabel(string $permissionName): string
    {
        $key = strtolower($permissionName);

        if (str_contains($key, 'nhanvien')) {
            return 'Nhan vien';
        }
        if (str_contains($key, 'hoso') || str_contains($key, 'ho_so')) {
            return 'Ho so';
        }
        if (str_contains($key, 'phancong')) {
            return 'Phan cong';
        }
        if (str_contains($key, 'hopdong') || str_contains($key, 'lich_su_luong')) {
            return 'Hop dong';
        }
        if (str_contains($key, 'dot_tuyen') || str_contains($key, 'ung_vien') || str_contains($key, 'phong_van') || str_contains($key, 'danh_gia')) {
            return 'Tuyen dung';
        }
        if (str_contains($key, 'dao_tao')) {
            return 'Dao tao';
        }
        if (str_contains($key, 'chamcong') || str_contains($key, 'cham_cong')) {
            return 'Cham cong';
        }
        if (str_contains($key, 'nghiphep')) {
            return 'Nghi phep';
        }
        if (str_contains($key, 'baohiem')) {
            return 'Bao hiem';
        }
        if (str_contains($key, 'khenthuong')) {
            return 'Khen thuong';
        }
        if (str_contains($key, 'luong') || str_contains($key, 'ngachluong') || str_contains($key, 'bacluong')) {
            return 'Luong';
        }
        if (str_contains($key, 'phongban')) {
            return 'Phong ban';
        }
        if (str_contains($key, 'chucvu')) {
            return 'Chuc vu';
        }
        if (str_contains($key, 'taikhoan')) {
            return 'Tai khoan';
        }
        if (str_contains($key, 'baocao')) {
            return 'Bao cao';
        }

        return 'Khac';
    }

    private function groupOrder(): array
    {
        return [
            'Nhan vien',
            'Ho so',
            'Phan cong',
            'Hop dong',
            'Tuyen dung',
            'Dao tao',
            'Cham cong',
            'Nghi phep',
            'Bao hiem',
            'Khen thuong',
            'Luong',
            'Phong ban',
            'Chuc vu',
            'Tai khoan',
            'Bao cao',
            'Khac',
        ];
    }
}