<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;

use App\Services\DashboardOverviewService;
use App\Services\InternalApiClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardOverviewService $dashboardOverviewService,
        private InternalApiClient $client,
    ) {}

    public function index(): View
    {
        $permissions  = (array) session('quyen', []);
        $maTK         = (int) session('MaTK', 0);
        $metrics      = (array) $this->dashboardOverviewService->metrics();
        $recentActivity = (array) $this->dashboardOverviewService->recentActivity();

        // Fetch chart data inline so the view can render without an extra AJAX round-trip.
        $cacheKey    = "dashboard_charts_{$maTK}";
        $inlineCharts = Cache::remember($cacheKey, 300, function () use ($permissions) {
            $response = $this->client->post('biz/dashboard/charts', ['permissions' => $permissions]);
            return $response['charts'] ?? [];
        });

        return view('trangchu.index', [
            'taiKhoan'     => session('taikhoan'),
            'quyen'        => $permissions,
            'metricCards'  => $this->buildMetricCards($metrics, $permissions),
            'moduleLinks'  => $this->buildModuleLinks($permissions),
            'quickSignals' => $this->buildQuickSignals($metrics, $permissions),
            'recentActivity' => $this->filterRecentActivity($recentActivity, $permissions),
            'inlineCharts' => $inlineCharts,
        ]);
    }

    public function chartData(Request $request): JsonResponse
    {
        if (!session()->has('taikhoan')) {
            return response()->json(['ok' => false, 'message' => 'UNAUTHORIZED'], 401);
        }

        $permissions = (array) session('quyen', []);
        $maTK        = (int) session('MaTK', 0);

        $cacheKey = "dashboard_charts_{$maTK}";
        if ($request->query('refresh') === '1') {
            Cache::forget($cacheKey);
        }

        $data = Cache::remember($cacheKey, 300, function () use ($permissions) {
            $response = $this->client->post('biz/dashboard/charts', ['permissions' => $permissions]);
            return $response['charts'] ?? [];
        });

        return response()->json(['ok' => true, 'charts' => $data])
            ->header('Cache-Control', 'private, max-age=300');
    }

    public function markNotificationsRead(Request $request): JsonResponse
    {
        $type = (string) $request->input('type', 'all');
        $allowed = ['all', 'leave', 'contract', 'candidate'];

        if (!in_array($type, $allowed, true)) {
            return response()->json(['ok' => false, 'message' => 'INVALID_TYPE'], 422);
        }

        $accountId = (int) session('MaTK', 0);
        if ($accountId <= 0) {
            return response()->json(['ok' => false, 'message' => 'UNAUTHORIZED'], 401);
        }

        $this->client->post('biz/dashboard/notifications/mark-read', [
            'ma_tk' => $accountId,
            'type'  => $type,
        ]);

        return response()->json(['ok' => true]);
    }

    private function buildMetricCards(array $metrics, array $permissions): array
    {
        $cards = [
            ['permission' => 'xem_nhanvien', 'label' => 'Nhan vien', 'value' => $metrics['employees'] ?? 0],
            ['permission' => 'xem_phongban', 'label' => 'Phong ban', 'value' => $metrics['departments'] ?? 0],
            ['permission' => 'xem_chamcong', 'label' => 'Cham cong thang nay', 'value' => $metrics['attendanceThisMonth'] ?? 0],
            ['permission' => 'xem_luong', 'label' => 'Bang luong thang nay', 'value' => $metrics['payrollThisMonth'] ?? 0],
            ['permission' => 'xem_dot_tuyen', 'label' => 'Dot tuyen dang mo', 'value' => $metrics['activeRecruitmentCampaigns'] ?? 0],
            ['permission' => 'xem_khoa_dao_tao', 'label' => 'Khoa dao tao dang chay', 'value' => $metrics['activeTrainingCourses'] ?? 0],
            ['permission' => 'xem_baocao', 'label' => 'Bao cao', 'value' => $metrics['reports'] ?? 0],
            ['permission' => 'su_dung_chatbot', 'label' => 'Luot tro chuyen chatbot', 'value' => $metrics['chatbotSessions'] ?? 0],
        ];

        return array_values(array_filter($cards, fn (array $card) => in_array($card['permission'], $permissions, true)));
    }

    private function buildModuleLinks(array $permissions): array
    {
        $links = [
            ['permission' => 'xem_nhanvien', 'label' => 'Mo nhan vien', 'route' => route('nhanvien.index'), 'secondary' => false],
            ['permission' => 'xem_phongban', 'label' => 'Mo phong ban', 'route' => route('phongban.index'), 'secondary' => true],
            ['permission' => 'xem_chamcong', 'label' => 'Mo cham cong', 'route' => route('chamcong.index'), 'secondary' => true],
            ['permission' => 'xem_luong', 'label' => 'Mo luong', 'route' => route('luong.index'), 'secondary' => true],
            ['permission' => 'xem_dot_tuyen', 'label' => 'Mo tuyen dung', 'route' => route('tuyendung.index'), 'secondary' => true],
            ['permission' => 'xem_khoa_dao_tao', 'label' => 'Mo dao tao', 'route' => route('daotao.index'), 'secondary' => true],
            ['permission' => 'xem_baocao', 'label' => 'Mo bao cao', 'route' => route('baocao.index'), 'secondary' => true],
            ['permission' => 'su_dung_chatbot', 'label' => 'Mo nhat ky chatbot', 'route' => route('chatbot.index'), 'secondary' => true],
            ['permission' => 'xem_phanquyen', 'label' => 'Mo phan quyen', 'route' => route('phanquyen.index'), 'secondary' => true],
            ['permission' => 'xem_phanquyen', 'label' => 'Mo bang dich vu', 'route' => route('services.index'), 'secondary' => true],
        ];

        return array_values(array_filter($links, fn (array $link) => in_array($link['permission'], $permissions, true)));
    }

    private function buildQuickSignals(array $metrics, array $permissions): array
    {
        $signals = [];

        if (in_array('su_dung_chatbot', $permissions, true)) {
            $signals[] = ['label' => 'Tin nhan chatbot hom nay', 'value' => $metrics['chatbotMessagesToday'] ?? 0, 'note' => null];
            $signals[] = ['label' => 'Draft chatbot dang cho', 'value' => $metrics['chatbotDraftsPending'] ?? 0, 'note' => 'Kiem tra cac thao tac AI dang cho xac nhan.'];
        }

        if (in_array('xem_dot_tuyen', $permissions, true)) {
            $signals[] = ['label' => 'Dot tuyen dang mo', 'value' => $metrics['activeRecruitmentCampaigns'] ?? 0, 'note' => 'Theo doi cac dot can dong lich va xu ly ho so.'];
        }

        if (in_array('xem_khoa_dao_tao', $permissions, true)) {
            $signals[] = ['label' => 'Khoa dao tao dang chay', 'value' => $metrics['activeTrainingCourses'] ?? 0, 'note' => 'Rao soat tien do cac khoa dang dao tao.'];
        }

        $signals[] = ['label' => 'Quyen hien tai', 'value' => count($permissions), 'note' => null];

        return $signals;
    }

    private function filterRecentActivity(array $recentActivity, array $permissions): array
    {
        return array_values(array_filter($recentActivity, function (array $item) use ($permissions) {
            $permission = (string) ($item['permission'] ?? '');

            return $permission !== '' && in_array($permission, $permissions, true);
        }));
    }
}
