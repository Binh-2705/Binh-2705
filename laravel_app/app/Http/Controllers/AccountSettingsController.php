<?php

namespace App\Http\Controllers;

use App\Services\AccountSecurityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountSettingsController extends Controller
{
    public function __construct(private AccountSecurityService $security)
    {
    }

    public function show(Request $request): View
    {
        $maTK = (int) $request->session()->get('MaTK', 0);
        $account = $this->security->getById($maTK);
        abort_if($account === null, 404);

        if (!$request->session()->has('session_marker')) {
            $request->session()->put('session_marker', bin2hex(random_bytes(32)));
        }

        $marker = (string) $request->session()->get('session_marker');
        $this->security->registerSessionAudit($maTK, $marker, (string) $request->userAgent(), (string) $request->ip());

        $recentSessions = array_map(function (array $row) use ($marker) {
            $row['is_current'] = (string) ($row['session_marker'] ?? '') === $marker;

            return $row;
        }, $this->security->getRecentSessions($maTK));

        return view('account.settings', [
            'account' => $account,
            'recentSessions' => $recentSessions,
            'sessionInfo' => [
                'session_id' => session()->getId(),
                'session_marker' => $marker,
                'must_change_password' => !empty($account['BuocDoiMatKhau']),
            ],
        ]);
    }

    public function updateUsername(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'TenDangNhapMoi' => ['required', 'regex:/^[A-Za-z0-9_.]{4,50}$/'],
            'MatKhauXacNhan' => ['required', 'string'],
        ]);

        $maTK = (int) $request->session()->get('MaTK', 0);
        $account = $this->security->getById($maTK);
        if (!$account || !password_verify($payload['MatKhauXacNhan'], (string) ($account['MatKhau'] ?? ''))) {
            return back()->withErrors(['form' => 'Mat khau xac nhan khong dung.']);
        }

        if (!$this->security->isUsernameAvailable($payload['TenDangNhapMoi'], $maTK)) {
            return back()->withErrors(['form' => 'Ten dang nhap moi da ton tai.']);
        }

        $this->security->updateUsername($maTK, $payload['TenDangNhapMoi']);
        $request->session()->put('taikhoan', $this->security->getById($maTK));

        return back()->with('success', 'Da cap nhat ten dang nhap.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'MatKhauHienTai' => ['required', 'string'],
            'MatKhauMoi' => ['required', 'string', 'min:8', 'same:XacNhanMatKhauMoi'],
            'XacNhanMatKhauMoi' => ['required', 'string'],
        ]);

        $maTK = (int) $request->session()->get('MaTK', 0);
        $account = $this->security->getById($maTK);
        if (!$account || !password_verify($payload['MatKhauHienTai'], (string) ($account['MatKhau'] ?? ''))) {
            return back()->withErrors(['form' => 'Mat khau hien tai khong dung.']);
        }

        if (password_verify($payload['MatKhauMoi'], (string) ($account['MatKhau'] ?? ''))) {
            return back()->withErrors(['form' => 'Mat khau moi khong duoc trung mat khau hien tai.']);
        }

        $this->security->updatePassword($maTK, password_hash($payload['MatKhauMoi'], PASSWORD_DEFAULT));
        $request->session()->put('taikhoan', $this->security->getById($maTK));
        $request->session()->put('must_change_password', false);

        return back()->with('success', 'Da doi mat khau thanh cong.');
    }

    public function refreshSession(Request $request): RedirectResponse
    {
        $request->session()->regenerate();
        $marker = bin2hex(random_bytes(32));
        $request->session()->put('session_marker', $marker);
        $this->security->registerSessionAudit((int) $request->session()->get('MaTK', 0), $marker, (string) $request->userAgent(), (string) $request->ip());

        return back()->with('success', 'Da lam moi phien dang nhap hien tai.');
    }

    public function revokeOtherSessions(Request $request): RedirectResponse
    {
        $maTK = (int) $request->session()->get('MaTK', 0);
        $marker = (string) $request->session()->get('session_marker', '');
        if ($maTK > 0 && $marker !== '') {
            $this->security->revokeOtherSessions($maTK, $marker);
        }

        return back()->with('success', 'Da dang xuat cac phien khac.');
    }
}