<?php

namespace App\Services;

class AccountSecurityService
{
    public function __construct(private InternalApiClient $client) {}

    public function getById(int $maTK): ?array
    {
        try { return $this->client->get("biz/accounts/{$maTK}")['data'] ?? null; }
        catch (\Illuminate\Database\Eloquent\ModelNotFoundException) { return null; }
    }

    public function getByUsername(string $username): ?array
    {
        try { return $this->client->get('biz/accounts/by-username', ['username' => $username])['data'] ?? null; }
        catch (\Illuminate\Database\Eloquent\ModelNotFoundException) { return null; }
    }

    public function isUsernameAvailable(string $username, int $excludeMaTK = 0): bool
    {
        return (bool) $this->client->get('biz/accounts/check-username', ['username' => $username, 'exclude_ma_tk' => $excludeMaTK])['available'];
    }

    public function updateUsername(int $maTK, string $newUsername): bool
    {
        return (bool) ($this->client->patch("biz/accounts/{$maTK}/username", ['TenDangNhap' => $newUsername])['affected'] ?? 0);
    }

    public function updatePassword(int $maTK, string $newHash, bool $forceChange = false): bool
    {
        return (bool) ($this->client->patch("biz/accounts/{$maTK}/password", ['MatKhau' => $newHash, 'BuocDoiMatKhau' => $forceChange])['affected'] ?? 0);
    }

    public function isPasswordChangeRequired(int $maTK): bool
    {
        $account = $this->getById($maTK);
        return (bool) ($account['BuocDoiMatKhau'] ?? false);
    }

    public function findAccountForInternalRecovery(string $username, string $employeeCode, string $birthDate, string $phoneSuffix): ?array
    {
        $account = $this->getByUsername($username);
        if (!$account) return null;

        try {
            $employeeData = $this->client->get('biz/accounts/employee-for-account', ['ma_tk' => $account['MaTK']]);
            $employee = $employeeData['data'] ?? null;
        } catch (\Throwable) {
            return null;
        }

        if (!$employee) return null;

        $allowedCodes = array_unique(array_filter([
            strtoupper(trim((string) ($account['MaNV'] ?? ''))),
            strtoupper(trim((string) ($employee['MaNV'] ?? ''))),
        ]));

        if (!in_array(strtoupper(trim($employeeCode)), $allowedCodes, true)) return null;
        if (substr((string) ($employee['NgaySinh'] ?? ''), 0, 10) !== trim($birthDate)) return null;

        $storedPhone = preg_replace('/\D+/', '', (string) ($employee['DienThoai'] ?? ''));
        $providedPhone = substr(preg_replace('/\D+/', '', $phoneSuffix), -4);
        if ($storedPhone === '' || substr($storedPhone, -4) !== $providedPhone) return null;

        return ['account' => $account, 'employee' => $employee];
    }

    public function createPasswordResetToken(int $maTK): string
    {
        return (string) ($this->client->post('biz/accounts/reset-token', ['MaTK' => $maTK])['token'] ?? '');
    }

    public function findValidResetToken(string $rawToken): ?array
    {
        try { return $this->client->get('biz/accounts/reset-token/find', ['token' => $rawToken])['data'] ?? null; }
        catch (\Illuminate\Database\Eloquent\ModelNotFoundException) { return null; }
    }

    public function markResetTokenUsed(int $id): void
    {
        $this->client->post("biz/accounts/reset-token/{$id}/used");
    }

    public function registerSessionAudit(int $maTK, string $marker, string $userAgent = '', string $ipAddress = ''): void
    {
        $this->client->post('biz/accounts/sessions/register', [
            'MaTK' => $maTK, 'session_marker' => $marker, 'user_agent' => $userAgent, 'ip_address' => $ipAddress,
        ]);
    }

    public function touchSessionAudit(int $maTK, string $marker): void
    {
        $this->client->post('biz/accounts/sessions/touch', ['MaTK' => $maTK, 'session_marker' => $marker]);
    }

    public function revokeOtherSessions(int $maTK, string $currentMarker): void
    {
        $this->client->post('biz/accounts/sessions/revoke-others', ['MaTK' => $maTK, 'current_marker' => $currentMarker]);
    }

    public function revokeCurrentSession(int $maTK, string $marker): void
    {
        $this->client->post('biz/accounts/sessions/revoke', ['MaTK' => $maTK, 'session_marker' => $marker]);
    }

    public function isSessionRevoked(int $maTK, string $marker): bool
    {
        return (bool) $this->client->get('biz/accounts/sessions/is-revoked', ['ma_tk' => $maTK, 'session_marker' => $marker])['revoked'];
    }

    public function getRecentSessions(int $maTK): array
    {
        return $this->client->get("biz/accounts/{$maTK}/sessions")['data'] ?? [];
    }
}
