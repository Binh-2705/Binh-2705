<?php

namespace App\Http\Middleware;

use App\Services\PermissionService;
use Closure;
use Illuminate\Http\Request;

class PermissionMiddleware
{
    public function __construct(private PermissionService $permissionService)
    {
    }

    public function handle(Request $request, Closure $next, string $permission)
    {
        $maTK = (int) $request->session()->get('MaTK', 0);

        if ($maTK <= 0) {
            return redirect()->route('login.form')
                ->withErrors(['auth' => 'Ban can dang nhap truoc.']);
        }

        // Dùng quyền đã load trong session — không query DB
        $sessionPermissions = (array) $request->session()->get('quyen', []);
        if (in_array($permission, $sessionPermissions, true)) {
            return $next($request);
        }

        // Fallback: query DB (cache 5 phút) nếu session chưa có quyền
        if (!$this->permissionService->hasPermissionFromCache($maTK, $permission)) {
            abort(403, 'Ban khong co quyen truy cap chuc nang nay.');
        }

        return $next($request);
    }
}
