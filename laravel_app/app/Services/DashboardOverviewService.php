<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardOverviewService
{
    public function metrics(): array
    {
        return Cache::remember('dashboard_metrics', 300, function () {
            $now = Carbon::now();

            return [
                'employees'                  => $this->safeFetch(fn () => $this->count('hr', 'nhanvien')),
                'departments'                => $this->safeFetch(fn () => $this->count('hr', 'phongban')),
                'attendanceThisMonth'        => $this->safeFetch(fn () => $this->countWhereMonth('attendance', 'chamcong', 'Ngay', $now)),
                'payrollThisMonth'           => $this->safeFetch(fn () => $this->countPayroll($now)),
                'activeRecruitmentCampaigns' => $this->safeFetch(fn () => $this->countWhere('recruitment', 'dottuyendung', 'TrangThai', 'Đang tuyển')),
                'activeTrainingCourses'      => $this->safeFetch(fn () => $this->countWhere('training', 'khoadaotao', 'TrangThai', 'Đang đào tạo')),
                'reports'                    => $this->safeFetch(fn () => $this->count('reporting', 'baocao')),
                'chatbotSessions'            => $this->safeFetch(fn () => $this->count('chatbot', 'chatbot_sessions')),
                'chatbotMessagesToday'       => $this->safeFetch(fn () => $this->countSince('chatbot', 'chatbot_messages', 'created_at', $now->copy()->startOfDay())),
                'chatbotDraftsPending'       => $this->safeFetch(fn () => $this->countWhere('chatbot', 'chatbot_action_drafts', 'status_name', 'pending')),
            ];
        });
    }

    public function recentActivity(int $limit = 8): array
    {
        return Cache::remember('dashboard_recent_activity_' . $limit, 120, function () use ($limit) {
            $items = array_merge(
                $this->safeArray(fn () => $this->recentRecruitment($limit)),
                $this->safeArray(fn () => $this->recentTraining($limit)),
                $this->safeArray(fn () => $this->recentReports($limit)),
                $this->safeArray(fn () => $this->recentChatbotSessions($limit))
            );

            usort($items, function (array $left, array $right) {
                return strcmp((string) $right['sort_at'], (string) $left['sort_at']);
            });

            return array_slice($items, 0, $limit);
        });
    }

    private function safeFetch(callable $fn, int $default = 0): int
    {
        try {
            return (int) $fn();
        } catch (\Throwable $e) {
            return $default;
        }
    }

    private function safeArray(callable $fn): array
    {
        try {
            return $fn();
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function connection(string $service): string
    {
        return (string) config("service_registry.services.{$service}.connection", config('database.default'));
    }

    private function count(string $service, string $table): int
    {
        return DB::connection($this->connection($service))->table($table)->count();
    }

    private function countWhere(string $service, string $table, string $column, string $value): int
    {
        return DB::connection($this->connection($service))->table($table)->where($column, $value)->count();
    }

    private function countWhereMonth(string $service, string $table, string $column, Carbon $date): int
    {
        return DB::connection($this->connection($service))
            ->table($table)
            ->whereMonth($column, $date->month)
            ->whereYear($column, $date->year)
            ->count();
    }

    private function countPayroll(Carbon $date): int
    {
        return DB::connection($this->connection('payroll'))
            ->table('bangluong')
            ->where('Thang', $date->month)
            ->where('Nam', $date->year)
            ->count();
    }

    private function countSince(string $service, string $table, string $column, Carbon $since): int
    {
        return DB::connection($this->connection($service))
            ->table($table)
            ->where($column, '>=', $since->toDateTimeString())
            ->count();
    }

    private function recentRecruitment(int $limit): array
    {
        return DB::connection($this->connection('recruitment'))
            ->table('dottuyendung')
            ->select(['MaDTD', 'TenDotTuyenDung', 'ViTriTuyenDung', 'TuNgay', 'TrangThai'])
            ->orderByDesc('TuNgay')
            ->limit($limit)
            ->get()
            ->map(fn ($item) => [
                'type' => 'Tuyen dung',
                'permission' => 'xem_dot_tuyen',
                'title' => $item->TenDotTuyenDung,
                'description' => trim($item->ViTriTuyenDung . ' • ' . $item->TrangThai),
                'at' => (string) $item->TuNgay,
                'sort_at' => (string) $item->TuNgay,
                'href' => route('tuyendung.index'),
            ])
            ->all();
    }

    private function recentTraining(int $limit): array
    {
        return DB::connection($this->connection('training'))
            ->table('khoadaotao')
            ->select(['MaKDT', 'TenKhoaDaoTao', 'DonViToChuc', 'TuNgay', 'TrangThai'])
            ->orderByDesc('TuNgay')
            ->limit($limit)
            ->get()
            ->map(fn ($item) => [
                'type' => 'Dao tao',
                'permission' => 'xem_khoa_dao_tao',
                'title' => $item->TenKhoaDaoTao,
                'description' => trim(($item->DonViToChuc ?: 'Noi bo') . ' • ' . $item->TrangThai),
                'at' => (string) $item->TuNgay,
                'sort_at' => (string) $item->TuNgay,
                'href' => route('daotao.index'),
            ])
            ->all();
    }

    private function recentReports(int $limit): array
    {
        return DB::connection($this->connection('reporting'))
            ->table('baocao')
            ->select(['MaBC', 'TenBaoCao', 'LoaiBaoCao', 'NguoiTao', 'ThoiDiemTao'])
            ->orderByDesc('ThoiDiemTao')
            ->limit($limit)
            ->get()
            ->map(fn ($item) => [
                'type' => 'Bao cao',
                'permission' => 'xem_baocao',
                'title' => $item->TenBaoCao,
                'description' => trim($item->LoaiBaoCao . ' • ' . ($item->NguoiTao ?: 'system')),
                'at' => (string) $item->ThoiDiemTao,
                'sort_at' => (string) $item->ThoiDiemTao,
                'href' => route('baocao.index'),
            ])
            ->all();
    }

    private function recentChatbotSessions(int $limit): array
    {
        return DB::connection($this->connection('chatbot'))
            ->table('chatbot_sessions')
            ->select(['id', 'username', 'role_name', 'session_key', 'last_interaction_at'])
            ->orderByDesc('last_interaction_at')
            ->limit($limit)
            ->get()
            ->map(fn ($item) => [
                'type' => 'Chatbot',
                'permission' => 'su_dung_chatbot',
                'title' => 'Session ' . $item->session_key,
                'description' => trim($item->username . ' • ' . $item->role_name),
                'at' => (string) $item->last_interaction_at,
                'sort_at' => (string) $item->last_interaction_at,
                'href' => route('chatbot.show', ['session' => $item->id]),
            ])
            ->all();
    }
}