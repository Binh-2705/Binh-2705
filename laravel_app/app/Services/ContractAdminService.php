<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class ContractAdminService
{
    private function payrollConnection(): string
    {
        return (string) config('service_registry.services.payroll.connection', config('database.default'));
    }

    private function hrConnection(): string
    {
        return (string) config('service_registry.services.hr.connection', config('database.default'));
    }

    public function contractDetail(int $contractId): ?array
    {
        $contract = DB::connection($this->payrollConnection())
            ->table('hopdong')
            ->where('MaHopDong', $contractId)
            ->first();

        if ($contract === null) {
            return null;
        }

        $contractArray = (array) $contract;
        $employee = DB::connection($this->hrConnection())
            ->table('nhanvien')
            ->where('MaNV', (int) $contract->MaNV)
            ->first(['MaNV', 'HoTen']);

        $salaryGrade = DB::connection($this->payrollConnection())
            ->table('bacluong')
            ->where('MaBac', (int) $contract->MaBac)
            ->first(['MaBac', 'TenBac', 'HeSoLuong', 'LuongCoSo']);

        $contractArray['HoTen'] = (string) ($employee->HoTen ?? '');
        $contractArray['TenBac'] = (string) ($salaryGrade->TenBac ?? '');
        $contractArray['HeSoLuong'] = (float) ($salaryGrade->HeSoLuong ?? 0);
        $contractArray['LuongCoSo'] = (float) ($salaryGrade->LuongCoSo ?? 0);
        $contractArray['LuongThucTe'] = (float) (($contractArray['HeSoLuong'] ?? 0) * ($contractArray['LuongCoSo'] ?? 0));

        return $contractArray;
    }

    public function salaryHistory(int $contractId): array
    {
        return DB::connection($this->payrollConnection())
            ->table('lichsu_luong')
            ->where('MaHopDong', $contractId)
            ->orderByDesc('NgayApDung')
            ->get()
            ->map(fn ($row) => (array) $row)
            ->all();
    }

    public function renewContract(int $contractId, array $payload): void
    {
        $current = $this->contractDetail($contractId);
        if ($current === null) {
            abort(404);
        }

        DB::connection($this->payrollConnection())->transaction(function () use ($contractId, $current, $payload) {
            DB::connection($this->payrollConnection())
                ->table('hopdong')
                ->where('SoHopDong', $payload['SoHopDong'])
                ->sharedLock()
                ->get();

            if (DB::connection($this->payrollConnection())
                ->table('hopdong')
                ->where('SoHopDong', $payload['SoHopDong'])
                ->exists()) {
                throw new \LogicException('So hop dong da ton tai.');
            }

            DB::connection($this->payrollConnection())
                ->table('hopdong')
                ->where('MaHopDong', $contractId)
                ->update([
                    'TrangThai' => 'Hết hiệu lực',
                    'NgayKetThuc' => now()->toDateString(),
                ]);

            DB::connection($this->payrollConnection())
                ->table('hopdong')
                ->insert([
                    'HopDongGoc' => $contractId,
                    'SoHopDong' => $payload['SoHopDong'],
                    'MaNV' => (int) $current['MaNV'],
                    'MaBac' => (int) $current['MaBac'],
                    'LoaiHopDong' => $payload['LoaiHopDong'],
                    'NgayKy' => now()->toDateString(),
                    'NgayBatDau' => $payload['NgayBatDau'],
                    'NgayKetThuc' => $payload['NgayKetThuc'] ?? null,
                    'TrangThai' => 'Còn hiệu lực',
                    'GhiChu' => $payload['GhiChu'] ?? null,
                ]);
        });
    }

    public function terminateContract(int $contractId): void
    {
        DB::connection($this->payrollConnection())
            ->table('hopdong')
            ->where('MaHopDong', $contractId)
            ->where('TrangThai', '<>', 'Hết hiệu lực')
            ->update([
                'TrangThai' => 'Hết hiệu lực',
                'NgayKetThuc' => now()->toDateString(),
            ]);
    }
}