<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;

use App\Services\AccountSecurityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PasswordRecoveryController extends Controller
{
    public function __construct(private AccountSecurityService $security)
    {
    }

    public function showForgot(): View
    {
        return view('auth.forgot-password');
    }

    public function handleForgot(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'TenDangNhap' => ['required', 'string'],
            'MaNhanVien' => ['required', 'string'],
            'NgaySinh' => ['required', 'date'],
            'SoDienThoai4So' => ['required', 'string', 'min:4'],
            'MatKhauMoi' => ['required', 'string', 'min:8', 'same:XacNhanMatKhau'],
            'XacNhanMatKhau' => ['required', 'string'],
        ]);

        $match = $this->security->findAccountForInternalRecovery(
            $payload['TenDangNhap'],
            $payload['MaNhanVien'],
            $payload['NgaySinh'],
            $payload['SoDienThoai4So'],
        );

        if (!$match) {
            return back()->withInput()->withErrors(['form' => 'Thong tin xac thuc khong khop.']);
        }

        if (password_verify($payload['MatKhauMoi'], (string) ($match['account']['MatKhau'] ?? ''))) {
            return back()->withInput()->withErrors(['form' => 'Mat khau moi khong duoc trung mat khau hien tai.']);
        }

        $this->security->updatePassword((int) $match['account']['MaTK'], password_hash($payload['MatKhauMoi'], PASSWORD_DEFAULT));

        return redirect()->route('login.form')->with('success', 'Dat lai mat khau thanh cong. Vui long dang nhap lai.');
    }

    public function showForcedChange(Request $request): View|RedirectResponse
    {
        $maTK = (int) $request->session()->get('MaTK', 0);
        if ($maTK <= 0) {
            return redirect()->route('login.form');
        }

        $account = $this->security->getById($maTK);
        if (!$account || !$this->security->isPasswordChangeRequired($maTK)) {
            return redirect()->route('dashboard');
        }

        return view('auth.force-password', ['account' => $account]);
    }

    public function handleForcedChange(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'MatKhauMoi' => ['required', 'string', 'min:8', 'same:XacNhanMatKhau'],
            'XacNhanMatKhau' => ['required', 'string'],
        ]);

        $maTK = (int) $request->session()->get('MaTK', 0);
        $account = $this->security->getById($maTK);
        if (!$account) {
            return redirect()->route('login.form');
        }

        if (password_verify($payload['MatKhauMoi'], (string) ($account['MatKhau'] ?? ''))) {
            return back()->withErrors(['form' => 'Mat khau moi khong duoc trung mat khau tam hien tai.']);
        }

        $this->security->updatePassword($maTK, password_hash($payload['MatKhauMoi'], PASSWORD_DEFAULT));
        $request->session()->put('taikhoan', $this->security->getById($maTK));
        $request->session()->put('must_change_password', false);

        return redirect()->route('dashboard')->with('success', 'Da doi mat khau thanh cong.');
    }

    public function showReset(Request $request): View
    {
        return view('auth.reset-password', ['token' => (string) $request->query('token', '')]);
    }

    public function handleReset(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'token' => ['required', 'string'],
            'MatKhauMoi' => ['required', 'string', 'min:8', 'same:XacNhanMatKhau'],
            'XacNhanMatKhau' => ['required', 'string'],
        ]);

        $tokenRow = $this->security->findValidResetToken($payload['token']);
        if (!$tokenRow) {
            return back()->withErrors(['form' => 'Lien ket khong hop le hoac da het han.']);
        }

        $this->security->updatePassword((int) $tokenRow['MaTK'], password_hash($payload['MatKhauMoi'], PASSWORD_DEFAULT));
        $this->security->markResetTokenUsed((int) $tokenRow['id']);

        return redirect()->route('login.form')->with('success', 'Dat lai mat khau thanh cong.');
    }
}