<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiTokenMiddleware
{
    public function handle(Request $request, Closure $next): mixed
    {
        $configuredToken = (string) config('services.service_gateway.token', '');

        if ($configuredToken === '') {
            return new JsonResponse([
                'ok' => false,
                'message' => 'Service gateway token is not configured.',
            ], 503);
        }

        $providedToken = (string) ($request->bearerToken() ?? $request->header('X-Service-Token', ''));

        if ($providedToken === '' || !hash_equals($configuredToken, $providedToken)) {
            return new JsonResponse([
                'ok' => false,
                'message' => 'Unauthorized service request.',
            ], 401);
        }

        return $next($request);
    }
}