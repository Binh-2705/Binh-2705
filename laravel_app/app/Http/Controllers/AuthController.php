<?php

namespace App\Http\Controllers;

use App\Services\PermissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(private PermissionService $permissionService)
    {
    }

    public function showLogin(): View|RedirectResponse
    {
        if (session()->has('MaTK')) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'TenDangNhap' => ['required', 'string', 'max:100'],
            'MatKhau' => ['required', 'string', 'max:255'],
        ]);

        $account = DB::table('taikhoan')
            ->where('TenDangNhap', $credentials['TenDangNhap'])
            ->first();

        if (!$account || !password_verify($credentials['MatKhau'], $account->MatKhau)) {
            return back()
                ->withErrors(['auth' => 'Sai ten dang nhap hoac mat khau'])
                ->withInput(['TenDangNhap' => $credentials['TenDangNhap']]);
        }

        $permissions = $this->permissionService->getPermissionsByAccountId((int) $account->MaTK);

        $request->session()->put('MaTK', (int) $account->MaTK);
        $request->session()->put('taikhoan', (array) $account);
        $request->session()->put('quyen', $permissions);

        return redirect()->route('dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->flush();
        $request->session()->regenerateToken();

        return redirect()->route('login.form');
    }
}
