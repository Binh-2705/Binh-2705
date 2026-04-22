<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class SystemHealthController extends Controller
{
    public function index(): View
    {
        $services = config('service_registry.services', []);
        $statuses = [];

        foreach ($services as $name => $service) {
            try {
                DB::connection((string) $service['connection'])->select('SELECT 1');
                $statuses[$name] = ['status' => 'ok', 'detail' => (string) $service['connection']];
            } catch (\Throwable $exception) {
                $statuses[$name] = ['status' => 'error', 'detail' => $exception->getMessage()];
            }
        }

        $botUrl = (string) (env('BOT_SERVICE_URL') ?: 'http://127.0.0.1:8001/health');
        try {
            $response = Http::timeout(2)->get($botUrl);
            $botStatus = ['status' => $response->successful() ? 'ok' : 'error', 'detail' => 'HTTP ' . $response->status()];
        } catch (\Throwable $exception) {
            $botStatus = ['status' => 'error', 'detail' => $exception->getMessage()];
        }

        return view('systemhealth.index', [
            'statuses' => $statuses,
            'botStatus' => $botStatus,
            'botUrl' => $botUrl,
        ]);
    }

    public function runChecks(): RedirectResponse
    {
        $services = config('service_registry.services', []);
        $failed = 0;

        foreach ($services as $service) {
            try {
                DB::connection((string) $service['connection'])->select('SELECT 1');
            } catch (\Throwable) {
                $failed++;
            }
        }

        $botUrl = (string) (env('BOT_SERVICE_URL') ?: 'http://127.0.0.1:8001/health');
        try {
            $response = Http::timeout(2)->get($botUrl);
            if (!$response->successful()) {
                $failed++;
            }
        } catch (\Throwable) {
            $failed++;
        }

        return redirect()->route('systemhealth.index')->with(
            $failed > 0 ? 'error' : 'success',
            $failed > 0
                ? 'Health check hoàn tất với ' . $failed . ' lỗi.'
                : 'Health check hoàn tất: tất cả kiểm tra đều đạt.'
        );
    }
}