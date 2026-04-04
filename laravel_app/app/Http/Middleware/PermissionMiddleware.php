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

        if (!$this->permissionService->hasPermission($maTK, $permission)) {
            abort(403, 'Ban khong co quyen truy cap chuc nang nay.');
        }

        return $next($request);
    }
}
