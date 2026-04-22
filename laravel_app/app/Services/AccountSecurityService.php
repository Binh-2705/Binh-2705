<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AccountSecurityService
{
    private function hrConnection(): string
    {
        return (string) config('service_registry.services.hr.connection', config('database.default'));
    }

    public function getById(int $maTK): ?array
    {
        $row = DB::connection($this->hrConnection())
            ->table('taikhoan as tk')
            ->leftJoin('taikhoanvaitro as tkvt', 'tk.MaTK', '=', 'tkvt.MaTK')
            ->leftJoin('vaitro as vt', 'tkvt.MaVaiTro', '=', 'vt.MaVaiTro')
            ->select('tk.*', DB::raw("COALESCE(vt.TenVaiTro, 'NhanVien') as VaiTro"))
            ->where('tk.MaTK', $maTK)
            ->first();

        return $row ? (array) $row : null;
    }

    public function getByUsername(string $username): ?array
    {
        $row = DB::connection($this->hrConnection())
            ->table('taikhoan as tk')
            ->leftJoin('taikhoanvaitro as tkvt', 'tk.MaTK', '=', 'tkvt.MaTK')
            ->leftJoin('vaitro as vt', 'tkvt.MaVaiTro', '=', 'vt.MaVaiTro')
            ->select('tk.*', DB::raw("COALESCE(vt.TenVaiTro, 'NhanVien') as VaiTro"))
            ->where('tk.TenDangNhap', trim($username))
            ->first();

        return $row ? (array) $row : null;
    }

    public function isUsernameAvailable(string $username, int $excludeMaTK = 0): bool
    {
        $query = DB::connection($this->hrConnection())
            ->table('taikhoan')
            ->where('TenDangNhap', trim($username));

        if ($excludeMaTK > 0) {
            $query->where('MaTK', '<>', $excludeMaTK);
        }

        return !$query->exists();
    }

    public function updateUsername(int $maTK, string $newUsername): bool
    {
        return DB::connection($this->hrConnection())
            ->table('taikhoan')
            ->where('MaTK', $maTK)
            ->update(['TenDangNhap' => trim($newUsername)]) > 0;
    }

    public function updatePassword(int $maTK, string $newHash, bool $forceChange = false): bool
    {
        return DB::connection($this->hrConnection())
            ->table('taikhoan')
            ->where('MaTK', $maTK)
            ->update([
                'MatKhau' => $newHash,
                'BuocDoiMatKhau' => $forceChange ? 1 : 0,
                'NgayCapMatKhauTam' => $forceChange ? now() : null,
            ]) > 0;
    }

    public function isPasswordChangeRequired(int $maTK): bool
    {
        return DB::connection($this->hrConnection())
            ->table('taikhoan')
            ->where('MaTK', $maTK)
            ->where('BuocDoiMatKhau', 1)
            ->exists();
    }

    public function findAccountForInternalRecovery(string $username, string $employeeCode, string $birthDate, string $phoneSuffix): ?array
    {
        $account = $this->getByUsername($username);
        if (!$account) {
            return null;
        }

        $employee = $this->findEmployeeForAccount($account);
        if (!$employee) {
            return null;
        }

        $allowedCodes = array_unique(array_filter([
            strtoupper(trim((string) ($account['MaNV'] ?? ''))),
            strtoupper(trim((string) ($employee['MaNV'] ?? ''))),
            (string) ($account['MaNVRef'] ?? ''),
        ]));

        $normalizedEmployeeCode = strtoupper(trim($employeeCode));
        if ($normalizedEmployeeCode === '' || !in_array($normalizedEmployeeCode, $allowedCodes, true)) {
            return null;
        }

        if (substr((string) ($employee['NgaySinh'] ?? ''), 0, 10) !== trim($birthDate)) {
            return null;
        }

        $storedPhone = preg_replace('/\D+/', '', (string) ($employee['DienThoai'] ?? ''));
        $providedPhone = substr(preg_replace('/\D+/', '', $phoneSuffix), -4);
        if ($storedPhone === '' || substr($storedPhone, -4) !== $providedPhone) {
            return null;
        }

        return [
            'account' => $account,
            'employee' => $employee,
        ];
    }

    public function createPasswordResetToken(int $maTK): string
    {
        $rawToken = Str::random(64);

        DB::connection($this->hrConnection())
            ->table('password_reset_tokens')
            ->where('MaTK', $maTK)
            ->whereNull('used_at')
            ->update(['used_at' => now()]);

        DB::connection($this->hrConnection())
            ->table('password_reset_tokens')
            ->insert([
                'MaTK' => $maTK,
                'token_hash' => password_hash($rawToken, PASSWORD_DEFAULT),
                'expires_at' => now()->addMinutes(30),
                'created_at' => now(),
            ]);

        return $rawToken;
    }

    public function findValidResetToken(string $rawToken): ?array
    {
        $rows = DB::connection($this->hrConnection())
            ->table('password_reset_tokens')
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->get();

        foreach ($rows as $row) {
            if (password_verify($rawToken, (string) $row->token_hash)) {
                return (array) $row;
            }
        }

        return null;
    }

    public function markResetTokenUsed(int $id): void
    {
        DB::connection($this->hrConnection())
            ->table('password_reset_tokens')
            ->where('id', $id)
            ->update(['used_at' => now()]);
    }

    public function registerSessionAudit(int $maTK, string $marker, string $userAgent = '', string $ipAddress = ''): void
    {
        DB::connection($this->hrConnection())
            ->table('session_audit')
            ->updateOrInsert(
                ['MaTK' => $maTK, 'session_marker' => substr($marker, 0, 64)],
                [
                    'user_agent' => substr($userAgent, 0, 255),
                    'ip_address' => substr($ipAddress, 0, 45),
                    'last_activity' => now(),
                    'revoked_at' => null,
                ]
            );
    }

    public function touchSessionAudit(int $maTK, string $marker): void
    {
        DB::connection($this->hrConnection())
            ->table('session_audit')
            ->where('MaTK', $maTK)
            ->where('session_marker', substr($marker, 0, 64))
            ->update(['last_activity' => now()]);
    }

    public function revokeOtherSessions(int $maTK, string $currentMarker): void
    {
        DB::connection($this->hrConnection())
            ->table('session_audit')
            ->where('MaTK', $maTK)
            ->where('session_marker', '<>', substr($currentMarker, 0, 64))
            ->whereNull('revoked_at')
            ->update(['revoked_at' => now()]);
    }

    public function revokeCurrentSession(int $maTK, string $marker): void
    {
        DB::connection($this->hrConnection())
            ->table('session_audit')
            ->where('MaTK', $maTK)
            ->where('session_marker', substr($marker, 0, 64))
            ->whereNull('revoked_at')
            ->update(['revoked_at' => now()]);
    }

    public function isSessionRevoked(int $maTK, string $marker): bool
    {
        return DB::connection($this->hrConnection())
            ->table('session_audit')
            ->where('MaTK', $maTK)
            ->where('session_marker', substr($marker, 0, 64))
            ->whereNotNull('revoked_at')
            ->exists();
    }

    public function getRecentSessions(int $maTK, int $limit = 8): array
    {
        return DB::connection($this->hrConnection())
            ->table('session_audit')
            ->where('MaTK', $maTK)
            ->orderByDesc('last_activity')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => (array) $row)
            ->all();
    }

    private function findEmployeeForAccount(array $account): ?array
    {
        $query = DB::connection($this->hrConnection())->table('nhanvien');
        $employeeId = (int) ($account['MaNVRef'] ?? 0);

        if ($employeeId > 0) {
            $employee = $query->where('MaNV', $employeeId)->first();
            if ($employee) {
                return (array) $employee;
            }
        }

        $email = trim((string) ($account['TenDangNhap'] ?? ''));
        if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $employee = DB::connection($this->hrConnection())->table('nhanvien')->where('Email', $email)->first();
            if ($employee) {
                return (array) $employee;
            }
        }

        return null;
    }
}