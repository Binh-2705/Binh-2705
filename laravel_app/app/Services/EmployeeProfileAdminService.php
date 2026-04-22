<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class EmployeeProfileAdminService
{
    private function hrConnection(): string
    {
        return (string) config('service_registry.services.hr.connection', config('database.default'));
    }

    public function profileDetail(int $profileId): ?array
    {
        $row = DB::connection($this->hrConnection())
            ->table('hosonhanvien as hs')
            ->leftJoin('nhanvien as nv', 'hs.MaNV', '=', 'nv.MaNV')
            ->leftJoin('phongban as pb', 'hs.MaPB', '=', 'pb.MaPB')
            ->leftJoin('chucvu as cv', 'hs.MaCV', '=', 'cv.MaCV')
            ->where('hs.MaHoSo', $profileId)
            ->select('hs.*', 'nv.HoTen', 'pb.TenPB', 'cv.TenCV')
            ->first();

        return $row ? (array) $row : null;
    }

    public function pendingRequests(): array
    {
        return DB::connection($this->hrConnection())
            ->table('hoso_update_requests as r')
            ->leftJoin('nhanvien as nv', 'r.MaNV', '=', 'nv.MaNV')
            ->where('r.status_name', 'pending')
            ->orderByDesc('r.created_at')
            ->select('r.*', 'nv.HoTen', 'nv.Email', 'nv.DienThoai')
            ->get()
            ->map(function ($row) {
                $record = (array) $row;
                $record['payload'] = json_decode((string) ($record['payload_json'] ?? '{}'), true) ?: [];

                return $record;
            })
            ->all();
    }

    public function resolveRequest(int $requestId, string $decision, int $reviewedBy, string $reviewNote = ''): void
    {
        DB::connection($this->hrConnection())->transaction(function () use ($requestId, $decision, $reviewedBy, $reviewNote) {
            $request = DB::connection($this->hrConnection())
                ->table('hoso_update_requests')
                ->where('id', $requestId)
                ->lockForUpdate()
                ->first();

            if ($request === null || (string) $request->status_name !== 'pending') {
                throw new \LogicException('Yeu cau khong ton tai hoac da duoc xu ly.');
            }

            if ($decision === 'approve') {
                $payload = json_decode((string) ($request->payload_json ?? '{}'), true) ?: [];
                DB::connection($this->hrConnection())
                    ->table('hosonhanvien')
                    ->where('MaNV', (int) $request->MaNV)
                    ->update([
                        'CCCD' => $payload['CCCD'] ?? null,
                        'NoiCap' => $payload['NoiCap'] ?? null,
                        'NgayCap' => $payload['NgayCap'] ?? null,
                        'DiaChi' => $payload['DiaChi'] ?? null,
                        'DanToc' => $payload['DanToc'] ?? null,
                        'TonGiao' => $payload['TonGiao'] ?? null,
                        'TrinhDo' => $payload['TrinhDo'] ?? null,
                        'ChuyenMon' => $payload['ChuyenMon'] ?? null,
                        'TrangThaiHonNhan' => $payload['TrangThaiHonNhan'] ?? null,
                    ]);
            }

            DB::connection($this->hrConnection())
                ->table('hoso_update_requests')
                ->where('id', $requestId)
                ->update([
                    'status_name' => $decision === 'approve' ? 'approved' : 'rejected',
                    'reviewed_by' => $reviewedBy,
                    'review_note' => $reviewNote,
                    'reviewed_at' => now(),
                    'updated_at' => now(),
                ]);
        });
    }

    public function employeeInfo(int $employeeId): ?array
    {
        $row = DB::connection($this->hrConnection())
            ->table('phancong as pc')
            ->leftJoin('phongban as pb', 'pc.MaPB', '=', 'pb.MaPB')
            ->leftJoin('chucvu as cv', 'pc.MaCV', '=', 'cv.MaCV')
            ->where('pc.MaNV', $employeeId)
            ->orderByDesc('pc.NgayBatDau')
            ->select('pc.MaNV', 'pb.MaPB', 'pb.TenPB', 'cv.MaCV', 'cv.TenCV')
            ->first();

        return $row ? (array) $row : null;
    }
}