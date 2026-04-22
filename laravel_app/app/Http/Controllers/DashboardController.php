<?php

namespace App\Http\Controllers;

use App\Services\DashboardOverviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private DashboardOverviewService $dashboardOverviewService)
    {
    }

    public function index(): View
    {
        $permissions = (array) session('quyen', []);
        $metrics = (array) $this->dashboardOverviewService->metrics();
        $recentActivity = (array) $this->dashboardOverviewService->recentActivity();

        return view('trangchu.index', [
            'taiKhoan' => session('taikhoan'),
            'quyen' => $permissions,
            'metricCards' => $this->buildMetricCards($metrics, $permissions),
            'moduleLinks' => $this->buildModuleLinks($permissions),
            'quickSignals' => $this->buildQuickSignals($metrics, $permissions),
            'recentActivity' => $this->filterRecentActivity($recentActivity, $permissions),
        ]);
    }

    public function chartData(Request $request): JsonResponse
    {
        if (!session()->has('taikhoan')) {
            return response()->json(['ok' => false, 'message' => 'UNAUTHORIZED'], 401);
        }

        $permissions = (array) session('quyen', []);
        $maTK        = (int) session('MaTK', 0);
        $hrConn      = (string) config('service_registry.services.hr.connection', config('database.default'));

        // Cache 5 phút theo tài khoản. Thêm ?refresh=1 để buộc tải lại.
        $cacheKey = "dashboard_charts_{$maTK}";
        if ($request->query('refresh') === '1') {
            Cache::forget($cacheKey);
        }

        $data = Cache::remember($cacheKey, 300, function () use ($permissions, $hrConn) {
            $result = [];

            // Nhân viên theo phòng ban
            if (in_array('xem_phongban', $permissions, true) || in_array('xem_nhanvien', $permissions, true)) {
                $rows = DB::connection($hrConn)
                    ->table('phancong as pc')
                    ->join('phongban as pb', 'pc.MaPB', '=', 'pb.MaPB')
                    ->selectRaw('pb.TenPB, COUNT(DISTINCT pc.MaNV) as total')
                    ->whereRaw('(pc.NgayKetThuc IS NULL OR pc.NgayKetThuc >= CURDATE())')
                    ->groupBy('pb.MaPB', 'pb.TenPB')
                    ->orderByDesc('total')
                    ->limit(8)
                    ->get();
                $result['department'] = [
                    'labels' => $rows->pluck('TenPB')->toArray(),
                    'values' => $rows->pluck('total')->map(fn($v) => (int)$v)->toArray(),
                ];
            }

            // Trạng thái nghỉ phép
            if (in_array('xem_nghiphep', $permissions, true) || in_array('duyet_nghiphep', $permissions, true)) {
                $rows = DB::connection($hrConn)
                    ->table('nghiphep')
                    ->selectRaw('TrangThai, COUNT(*) as total')
                    ->groupBy('TrangThai')
                    ->orderByDesc('total')
                    ->get();
                $result['leave'] = [
                    'labels' => $rows->pluck('TrangThai')->toArray(),
                    'values' => $rows->pluck('total')->map(fn($v) => (int)$v)->toArray(),
                ];
            }

            // Chấm công 7 ngày gần nhất
            if (in_array('xem_chamcong', $permissions, true)) {
                $rows = DB::connection($hrConn)
                    ->table('chamcong')
                    ->selectRaw('DATE(Ngay) as ngay, COUNT(*) as total')
                    ->whereRaw('Ngay >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)')
                    ->groupBy('ngay')
                    ->orderBy('ngay')
                    ->get();
                $result['attendance'] = [
                    'labels' => $rows->pluck('ngay')->toArray(),
                    'values' => $rows->pluck('total')->map(fn($v) => (int)$v)->toArray(),
                ];
            }

            // Trạng thái lương tháng này
            if (in_array('xem_luong', $permissions, true)) {
                $rows = DB::connection($hrConn)
                    ->table('bangluong')
                    ->selectRaw('TrangThai, COUNT(*) as total')
                    ->whereRaw('Thang = MONTH(CURDATE()) AND Nam = YEAR(CURDATE())')
                    ->groupBy('TrangThai')
                    ->orderByDesc('total')
                    ->get();
                $result['payroll'] = [
                    'labels' => $rows->pluck('TrangThai')->toArray(),
                    'values' => $rows->pluck('total')->map(fn($v) => (int)$v)->toArray(),
                ];
            }

            // Tuyển dụng theo trạng thái
            if (in_array('xem_ho_so', $permissions, true) || in_array('xem_dot_tuyen', $permissions, true)) {
                $recruitConn = (string) config('service_registry.services.recruitment.connection', $hrConn);
                $rows = DB::connection($recruitConn)
                    ->table('hosoungtuyen')
                    ->selectRaw('TrangThai, COUNT(*) as total')
                    ->groupBy('TrangThai')
                    ->orderByDesc('total')
                    ->get();
                $result['recruitment'] = [
                    'labels' => $rows->pluck('TrangThai')->toArray(),
                    'values' => $rows->pluck('total')->map(fn($v) => (int)$v)->toArray(),
                ];
            }

            return $result;
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

        $hrConnection = (string) config('service_registry.services.hr.connection', config('database.default'));
        $recruitmentConnection = (string) config('service_registry.services.recruitment.connection', $hrConnection);

        $leaveCount = DB::connection($hrConnection)->table('nghiphep')->where('TrangThai', 'Chờ duyệt')->count();
        $contractCount = DB::connection($hrConnection)
            ->table('hopdong')
            ->whereNotNull('NgayKetThuc')
            ->where('NgayKetThuc', '<=', now()->addDays(30)->toDateString())
            ->count();
        $candidateCount = DB::connection($recruitmentConnection)
            ->table('hosoungtuyen')
            ->where('NgayNop', '>=', now()->subDays(7)->toDateString())
            ->count();

        $existing = DB::connection($hrConnection)->table('thongbao_daxem')->where('MaTK', $accountId)->first();
        $seenLeave = (int) ($existing->DaXemNghiPhep ?? 0);
        $seenContract = (int) ($existing->DaXemHopDong ?? 0);
        $seenCandidate = (int) ($existing->DaXemUngVien ?? 0);

        if ($type === 'leave' || $type === 'all') {
            $seenLeave = $leaveCount;
        }
        if ($type === 'contract' || $type === 'all') {
            $seenContract = $contractCount;
        }
        if ($type === 'candidate' || $type === 'all') {
            $seenCandidate = $candidateCount;
        }

        DB::connection($hrConnection)->table('thongbao_daxem')->updateOrInsert(
            ['MaTK' => $accountId],
            [
                'DaXemNghiPhep' => $seenLeave,
                'DaXemHopDong' => $seenContract,
                'DaXemUngVien' => $seenCandidate,
                'UpdatedAt' => now(),
            ]
        );

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
