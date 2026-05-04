<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AccountBizController extends Controller
{
    private function conn(): string
    {
        return (string) config('service_registry.services.hr.connection', config('database.default'));
    }

    public function show(int $id): JsonResponse
    {
        $row = DB::connection($this->conn())
            ->table('taikhoan as tk')
            ->leftJoin('taikhoanvaitro as tkvt', 'tk.MaTK', '=', 'tkvt.MaTK')
            ->leftJoin('vaitro as vt', 'tkvt.MaVaiTro', '=', 'vt.MaVaiTro')
            ->select('tk.*', DB::raw("COALESCE(vt.TenVaiTro, 'NhanVien') as VaiTro"))
            ->where('tk.MaTK', $id)->first();

        if (!$row) {
            return response()->json(['ok' => false, 'message' => 'Tai khoan khong ton tai.'], 404);
        }
        return response()->json(['ok' => true, 'data' => (array) $row]);
    }

    public function showByUsername(Request $request): JsonResponse
    {
        $username = trim((string) $request->query('username', ''));
        $row = DB::connection($this->conn())
            ->table('taikhoan as tk')
            ->leftJoin('taikhoanvaitro as tkvt', 'tk.MaTK', '=', 'tkvt.MaTK')
            ->leftJoin('vaitro as vt', 'tkvt.MaVaiTro', '=', 'vt.MaVaiTro')
            ->select('tk.*', DB::raw("COALESCE(vt.TenVaiTro, 'NhanVien') as VaiTro"))
            ->where('tk.TenDangNhap', $username)->first();

        if (!$row) {
            return response()->json(['ok' => false, 'message' => 'Tai khoan khong ton tai.'], 404);
        }
        return response()->json(['ok' => true, 'data' => (array) $row]);
    }

    public function checkUsernameAvailable(Request $request): JsonResponse
    {
        $username    = trim((string) $request->query('username', ''));
        $excludeMaTK = (int) $request->query('exclude_ma_tk', 0);

        $query = DB::connection($this->conn())->table('taikhoan')->where('TenDangNhap', $username);
        if ($excludeMaTK > 0) {
            $query->where('MaTK', '<>', $excludeMaTK);
        }

        return response()->json(['ok' => true, 'available' => !$query->exists()]);
    }

    public function updateUsername(Request $request, int $id): JsonResponse
    {
        $username = trim((string) $request->input('TenDangNhap', ''));
        $affected = DB::connection($this->conn())->table('taikhoan')->where('MaTK', $id)->update(['TenDangNhap' => $username]);
        return response()->json(['ok' => true, 'affected' => $affected]);
    }

    public function updatePassword(Request $request, int $id): JsonResponse
    {
        $payload = (array) $request->json()->all();
        $affected = DB::connection($this->conn())->table('taikhoan')->where('MaTK', $id)->update([
            'MatKhau'            => $payload['MatKhau'],
            'BuocDoiMatKhau'     => (bool) ($payload['BuocDoiMatKhau'] ?? false) ? 1 : 0,
            'NgayCapMatKhauTam'  => !empty($payload['BuocDoiMatKhau']) ? now() : null,
        ]);
        return response()->json(['ok' => true, 'affected' => $affected]);
    }

    public function findEmployeeForAccount(Request $request): JsonResponse
    {
        $maTK = (int) $request->query('ma_tk', 0);
        $conn = DB::connection($this->conn());

        $account = $conn->table('taikhoan')->where('MaTK', $maTK)->first();
        if (!$account) {
            return response()->json(['ok' => false, 'message' => 'Tai khoan khong ton tai.'], 404);
        }

        $employee = $conn->table('nhanvien')
            ->where(fn ($q) => $q->where('MaNV', $account->MaNV ?? 0)->orWhere('Email', $account->TenDangNhap))
            ->first();

        if (!$employee) {
            return response()->json(['ok' => false, 'message' => 'Khong tim thay nhan vien.'], 404);
        }
        return response()->json(['ok' => true, 'data' => (array) $employee]);
    }

    // ─── Session audit ───────────────────────────────────────────────────────

    public function registerSession(Request $request): JsonResponse
    {
        $payload = (array) $request->json()->all();
        DB::connection($this->conn())->table('session_audit')->updateOrInsert(
            ['MaTK' => (int) $payload['MaTK'], 'session_marker' => substr((string) $payload['session_marker'], 0, 64)],
            [
                'user_agent'    => substr((string) ($payload['user_agent'] ?? ''), 0, 255),
                'ip_address'    => substr((string) ($payload['ip_address'] ?? ''), 0, 45),
                'last_activity' => now(),
                'revoked_at'    => null,
            ]
        );
        return response()->json(['ok' => true]);
    }

    public function touchSession(Request $request): JsonResponse
    {
        $payload = (array) $request->json()->all();
        DB::connection($this->conn())->table('session_audit')
            ->where('MaTK', (int) $payload['MaTK'])
            ->where('session_marker', substr((string) $payload['session_marker'], 0, 64))
            ->update(['last_activity' => now()]);
        return response()->json(['ok' => true]);
    }

    public function revokeOtherSessions(Request $request): JsonResponse
    {
        $payload = (array) $request->json()->all();
        DB::connection($this->conn())->table('session_audit')
            ->where('MaTK', (int) $payload['MaTK'])
            ->where('session_marker', '<>', substr((string) $payload['current_marker'], 0, 64))
            ->whereNull('revoked_at')
            ->update(['revoked_at' => now()]);
        return response()->json(['ok' => true]);
    }

    public function revokeCurrentSession(Request $request): JsonResponse
    {
        $payload = (array) $request->json()->all();
        DB::connection($this->conn())->table('session_audit')
            ->where('MaTK', (int) $payload['MaTK'])
            ->where('session_marker', substr((string) $payload['session_marker'], 0, 64))
            ->whereNull('revoked_at')
            ->update(['revoked_at' => now()]);
        return response()->json(['ok' => true]);
    }

    public function isSessionRevoked(Request $request): JsonResponse
    {
        $maTK   = (int) $request->query('ma_tk', 0);
        $marker = substr((string) $request->query('session_marker', ''), 0, 64);

        $revoked = DB::connection($this->conn())->table('session_audit')
            ->where('MaTK', $maTK)->where('session_marker', $marker)->whereNotNull('revoked_at')->exists();

        return response()->json(['ok' => true, 'revoked' => $revoked]);
    }

    // ─── Password reset tokens ───────────────────────────────────────────────

    public function createResetToken(Request $request): JsonResponse
    {
        $maTK     = (int) $request->input('MaTK', 0);
        $rawToken = Str::random(64);
        $conn     = DB::connection($this->conn());

        $conn->table('password_reset_tokens')
            ->where('MaTK', $maTK)->whereNull('used_at')->update(['used_at' => now()]);

        $conn->table('password_reset_tokens')->insert([
            'MaTK'       => $maTK,
            'token_hash' => password_hash($rawToken, PASSWORD_DEFAULT),
            'expires_at' => now()->addMinutes(30),
            'created_at' => now(),
        ]);

        return response()->json(['ok' => true, 'token' => $rawToken]);
    }

    public function findValidResetToken(Request $request): JsonResponse
    {
        $rawToken = (string) $request->query('token', '');
        $rows = DB::connection($this->conn())->table('password_reset_tokens')
            ->whereNull('used_at')->where('expires_at', '>', now())->get();

        foreach ($rows as $row) {
            if (password_verify($rawToken, (string) $row->token_hash)) {
                return response()->json(['ok' => true, 'data' => (array) $row]);
            }
        }

        return response()->json(['ok' => false, 'message' => 'Token khong hop le.'], 404);
    }

    public function markResetTokenUsed(int $id): JsonResponse
    {
        DB::connection($this->conn())->table('password_reset_tokens')->where('id', $id)->update(['used_at' => now()]);
        return response()->json(['ok' => true]);
    }

    public function listSessions(int $id): JsonResponse
    {
        $rows = DB::connection($this->conn())->table('session_audit')
            ->where('MaTK', $id)
            ->orderByDesc('last_activity')
            ->limit(20)
            ->get()
            ->map(fn ($r) => (array) $r)
            ->all();

        return response()->json(['ok' => true, 'data' => $rows]);
    }
}
