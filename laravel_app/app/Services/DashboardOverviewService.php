<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class DashboardOverviewService
{
    public function __construct(private InternalApiClient $client) {}

    public function metrics(): array
    {
        return Cache::remember('dashboard_metrics', 300, fn () =>
            $this->client->get('biz/dashboard/metrics')['data'] ?? []
        );
    }

    public function recentActivity(int $limit = 8): array
    {
        return Cache::remember('dashboard_recent_activity_' . $limit, 120, fn () =>
            $this->client->get('biz/dashboard/recent-activity', ['limit' => $limit])['data'] ?? []
        );
    }
}
