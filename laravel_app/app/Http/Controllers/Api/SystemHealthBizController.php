<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class SystemHealthBizController extends Controller
{
    public function status(): JsonResponse
    {
        $payload = $this->collectStatuses();

        return response()->json([
            'ok' => true,
            'statuses' => $payload['statuses'],
            'botStatus' => $payload['bot_status'],
            'botUrl' => $payload['bot_url'],
            'failed' => $payload['failed'],
        ]);
    }

    public function runChecks(): JsonResponse
    {
        $payload = $this->collectStatuses();

        return response()->json([
            'ok' => true,
            'failed' => $payload['failed'],
            'message' => $payload['failed'] > 0
                ? 'Health check completed with ' . $payload['failed'] . ' errors.'
                : 'Health check completed successfully.',
        ]);
    }

    private function collectStatuses(): array
    {
        $services = config('service_registry.services', []);
        $statuses = [];
        $failed = 0;

        foreach ($services as $name => $service) {
            try {
                DB::connection((string) $service['connection'])->select('SELECT 1');
                $statuses[$name] = ['status' => 'ok', 'detail' => (string) $service['connection']];
            } catch (\Throwable $exception) {
                $statuses[$name] = ['status' => 'error', 'detail' => $exception->getMessage()];
                $failed++;
            }
        }

        $botUrl = (string) (env('BOT_SERVICE_URL') ?: 'http://127.0.0.1:8001/health');
        try {
            $response = Http::timeout(2)->get($botUrl);
            $botStatus = ['status' => $response->successful() ? 'ok' : 'error', 'detail' => 'HTTP ' . $response->status()];
            if (!$response->successful()) {
                $failed++;
            }
        } catch (\Throwable $exception) {
            $botStatus = ['status' => 'error', 'detail' => $exception->getMessage()];
            $failed++;
        }

        return [
            'statuses' => $statuses,
            'bot_status' => $botStatus,
            'bot_url' => $botUrl,
            'failed' => $failed,
        ];
    }
}
