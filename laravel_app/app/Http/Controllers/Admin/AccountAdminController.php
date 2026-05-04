<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Services\AccountSecurityService;
use App\Services\GenericResourceModuleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AccountAdminController extends Controller
{
    public function __construct(
        private AccountSecurityService $accountSecurityService,
        private GenericResourceModuleService $modules,
    ) {
    }

    public function resetTemporaryPassword(Request $request, int $account): RedirectResponse
    {
        $record = $this->accountSecurityService->getById($account);
        if ($record === null) {
            return redirect()->route('taikhoan.index')->withErrors('Khong tim thay tai khoan can cap lai mat khau.');
        }

        $temporaryPassword = 'HRM' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
        $updated = $this->accountSecurityService->updatePassword(
            $account,
            password_hash($temporaryPassword, PASSWORD_DEFAULT),
            true
        );

        if (!$updated) {
            return redirect()->route('taikhoan.index')->withErrors('Khong the cap lai mat khau tam.');
        }

        if ((int) $request->session()->get('MaTK', 0) === $account) {
            $request->session()->put('must_change_password', true);
            $currentAccount = (array) $request->session()->get('taikhoan', []);
            $currentAccount['BuocDoiMatKhau'] = 1;
            $request->session()->put('taikhoan', $currentAccount);
        }

        $username = (string) ($record['TenDangNhap'] ?? ('#' . $account));

        return redirect()->route('taikhoan.index')
            ->with('success', 'Da cap mat khau tam cho ' . $username . ': ' . $temporaryPassword);
    }

    public function destroyLegacy(int $account): RedirectResponse
    {
        $this->modules->delete('accounts', (string) $account);

        return redirect()->route('taikhoan.index')->with('success', 'Da xoa tai khoan thanh cong.');
    }
}