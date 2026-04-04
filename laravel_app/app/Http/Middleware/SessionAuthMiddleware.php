<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SessionAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->session()->has('MaTK')) {
            return redirect()->route('login.form')
                ->withErrors(['auth' => 'Ban can dang nhap truoc.']);
        }

        return $next($request);
    }
}
