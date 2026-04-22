<?php

namespace App\Http\Controllers;

use App\Services\ChatbotMonitorService;
use DateTime;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class ChatbotMonitorController extends Controller
{
    public function __construct(private ChatbotMonitorService $chatbotMonitorService)
    {
    }

    public function index(): View
    {
        return view('chatbot.index', [
            'sessions' => $this->chatbotMonitorService->paginateSessions(),
        ]);
    }

    public function show(int $session): View
    {
        $payload = $this->chatbotMonitorService->findSession($session);

        return view('chatbot.show', $payload);
    }

    public function ask(Request $request): JsonResponse
    {
        if (!session()->has('taikhoan')) {
            return response()->json(['ok' => false, 'message' => 'SESSION_EXPIRED'], 401);
        }

        $message = trim((string) $request->input('message', ''));
        if ($message === '') {
            return response()->json(['ok' => false, 'message' => 'EMPTY_MESSAGE'], 422);
        }

        $history = session('chatbot_history', []);
        if (!is_array($history)) {
            $history = [];
        }

        $history[] = [
            'role' => 'user',
            'content' => $message,
        ];
        $history = array_slice($history, -10);

        $account = (array) session('taikhoan', []);
        $sessionId = $this->getChatSessionId();
        $this->logMessage($sessionId, 'user', $message, 'user_input');

        $result = $this->callBotService($this->chatEndpoint(), [
            'message' => $message,
            'history' => $history,
            'user' => [
                'ma_tk' => (int) session('MaTK', 0),
                'username' => (string) ($account['TenDangNhap'] ?? ''),
                'role' => (string) ($account['VaiTro'] ?? ''),
                'permissions' => array_values(array_slice((array) session('quyen', []), 0, 80)),
            ],
        ]);

        if (!$result['ok']) {
            logger()->error('Chatbot service call failed', ['error' => $result['error']]);

            $fallback = 'Bot service chưa sẵn sàng. Hãy chạy Python service và thử lại.';
            $history[] = ['role' => 'assistant', 'content' => $fallback];
            session(['chatbot_history' => array_slice($history, -10)]);
            $this->logMessage($sessionId, 'assistant', $fallback, 'fallback');

            return response()->json([
                'ok' => true,
                'reply' => $fallback,
                'actions' => [],
                'suggestions' => [
                    'Tổng số nhân viên hiện tại là bao nhiêu?',
                    'Thống kê nghỉ phép',
                    'Hợp đồng sắp hết hạn',
                ],
                'source' => 'fallback',
            ]);
        }

        $reply = trim((string) ($result['data']['reply'] ?? ''));
        if ($reply === '') {
            $reply = 'Xin lỗi, tôi chưa có câu trả lời phù hợp.';
        }

        $actions = $result['data']['actions'] ?? [];
        if (!is_array($actions)) {
            $actions = [];
        }

        $suggestions = $result['data']['suggestions'] ?? [];
        if (!is_array($suggestions)) {
            $suggestions = [];
        }

        $actionDraftResponse = null;
        $draft = $result['data']['action_draft'] ?? null;
        if (is_array($draft) && !empty($draft['action_type'])) {
            $token = $this->createActionDraft($sessionId, (int) session('MaTK', 0), $draft);
            $actionDraftResponse = [
                'token' => $token,
                'title' => (string) ($draft['title'] ?? 'Xác nhận hành động'),
                'summary' => (string) ($draft['summary'] ?? ''),
                'confirm_label' => (string) ($draft['confirm_label'] ?? 'Xác nhận thực thi'),
                'action_type' => (string) ($draft['action_type'] ?? ''),
            ];
        }

        $history[] = ['role' => 'assistant', 'content' => $reply];
        session(['chatbot_history' => array_slice($history, -10)]);

        $this->logMessage(
            $sessionId,
            'assistant',
            $reply,
            (string) ($result['data']['source'] ?? 'bot_service'),
            $actions,
            $suggestions,
            $actionDraftResponse['token'] ?? null
        );

        return response()->json([
            'ok' => true,
            'reply' => $reply,
            'actions' => array_values($actions),
            'suggestions' => array_values($suggestions),
            'action_draft' => $actionDraftResponse,
            'source' => (string) ($result['data']['source'] ?? 'bot_service'),
        ]);
    }

    public function confirmDraft(Request $request): JsonResponse
    {
        if (!session()->has('taikhoan')) {
            return response()->json(['ok' => false, 'message' => 'SESSION_EXPIRED'], 401);
        }

        $token = trim((string) $request->input('action_token', ''));
        if ($token === '') {
            return response()->json(['ok' => false, 'message' => 'MISSING_ACTION_TOKEN'], 422);
        }

        $currentAccountId = (int) session('MaTK', 0);
        $draft = $this->getPendingActionDraft($token, $currentAccountId);
        if ($draft === null) {
            return response()->json(['ok' => false, 'message' => 'ACTION_DRAFT_NOT_FOUND_OR_EXPIRED'], 404);
        }

        $confirmReason = trim((string) $request->input('confirm_reason', ''));
        $actionType = (string) ($draft->action_type ?? '');
        $requiresReason = in_array($actionType, ['leave_approve', 'leave_reject'], true);
        if ($requiresReason && $confirmReason === '') {
            return response()->json(['ok' => false, 'message' => 'CONFIRM_REASON_REQUIRED'], 422);
        }

        $requiredPermission = $this->requiredPermissionForAction($actionType);
        if ($requiredPermission !== '' && !in_array($requiredPermission, (array) session('quyen', []), true)) {
            return response()->json(['ok' => false, 'message' => 'FORBIDDEN'], 403);
        }

        $payload = json_decode((string) ($draft->payload_json ?? '{}'), true);
        if (!is_array($payload)) {
            $payload = [];
        }

        $result = $this->executeDraft($actionType, $payload, $confirmReason);
        $status = $result['ok'] ? 'executed' : 'failed';
        $message = (string) ($result['message'] ?? 'Không xác định');

        if ($confirmReason !== '') {
            $message .= ' | Lý do xác nhận: ' . $confirmReason;
        }

        $stateDiff = trim((string) ($result['state_diff'] ?? ''));
        if ($stateDiff !== '') {
            $message .= ' | ' . $stateDiff;
        }

        DB::connection($this->hrConnection())
            ->table('chatbot_action_drafts')
            ->where('id', (int) $draft->id)
            ->update([
                'status_name' => $status,
                'confirmed_by' => $currentAccountId,
                'confirmed_at' => now(),
                'executed_at' => now(),
                'result_message' => $message,
            ]);

        $this->logMessage($this->getChatSessionId(), 'system', $message, 'action_execution');

        return response()->json([
            'ok' => (bool) $result['ok'],
            'reply' => $message,
            'actions' => [$result['ok'] ? 'Hành động đã được thực thi' : 'Hành động không thành công'],
            'suggestions' => [
                'Thống kê nghỉ phép',
                'Tổng số nhân viên hiện tại là bao nhiêu?',
                'Hợp đồng sắp hết hạn',
            ],
            'source' => 'action_execution',
        ], $result['ok'] ? 200 : 422);
    }

    public function clearHistory(): JsonResponse
    {
        if (!session()->has('taikhoan')) {
            return response()->json(['ok' => false, 'message' => 'SESSION_EXPIRED'], 401);
        }

        session()->forget('chatbot_history');
        session(['chatbot_session_key' => bin2hex(random_bytes(16))]);

        return response()->json([
            'ok' => true,
            'reply' => 'Đã xóa lịch sử hội thoại hiện tại.',
            'actions' => ['Lịch sử chat trong session đã được làm mới'],
            'suggestions' => ['Tổng số nhân viên hiện tại là bao nhiêu?', 'Thông tin cá nhân của tôi', 'Hợp đồng sắp hết hạn'],
        ]);
    }

    public function brief(): JsonResponse
    {
        if (!session()->has('taikhoan')) {
            return response()->json(['ok' => false, 'message' => 'SESSION_EXPIRED'], 401);
        }

        $account = (array) session('taikhoan', []);
        $result = $this->callBotService($this->briefEndpoint(), [
            'user' => [
                'ma_tk' => (int) session('MaTK', 0),
                'username' => (string) ($account['TenDangNhap'] ?? ''),
                'role' => (string) ($account['VaiTro'] ?? ''),
                'permissions' => array_values(array_slice((array) session('quyen', []), 0, 80)),
            ],
        ]);

        if (!$result['ok']) {
            return response()->json(['ok' => true, 'items' => [], 'source' => 'fallback']);
        }

        return response()->json([
            'ok' => true,
            'items' => array_values((array) ($result['data']['items'] ?? [])),
            'source' => (string) ($result['data']['source'] ?? 'brief'),
        ]);
    }

    private function executeDraft(string $actionType, array $payload, string $confirmReason = ''): array
    {
        $connection = DB::connection($this->hrConnection());

        if ($actionType === 'leave_approve') {
            $leaveId = (int) ($payload['ma_np'] ?? 0);
            if ($leaveId <= 0) {
                return ['ok' => false, 'message' => 'Thiếu mã đơn nghỉ phép để duyệt.'];
            }

            try {
                $result = $connection->transaction(function () use ($connection, $leaveId) {
                    $leave = $connection->table('nghiphep')->where('MaNP', $leaveId)->lockForUpdate()->first();
                    if ($leave === null) {
                        return ['ok' => false, 'message' => 'Không tìm thấy đơn nghỉ phép cần duyệt.'];
                    }

                    $status = trim((string) ($leave->TrangThai ?? ''));
                    if ($status !== 'Chờ duyệt') {
                        return ['ok' => false, 'message' => 'Đơn nghỉ phép không còn ở trạng thái Chờ duyệt.'];
                    }

                    $connection->table('nghiphep')->where('MaNP', $leaveId)->update([
                        'TrangThai' => 'Đã duyệt',
                        'NgayDuyet' => now()->toDateString(),
                    ]);

                    $cursor = strtotime((string) $leave->TuNgay);
                    $end = strtotime((string) $leave->DenNgay);
                    while ($cursor !== false && $end !== false && $cursor <= $end) {
                        $date = date('Y-m-d', $cursor);
                        $connection->table('chamcong')->updateOrInsert(
                            ['MaNV' => (int) $leave->MaNV, 'Ngay' => $date],
                            ['TrangThai' => 'Nghi phep', 'GioVao' => null, 'GioRa' => null]
                        );
                        $cursor = strtotime('+1 day', $cursor);
                    }

                    return [
                        'ok' => true,
                        'message' => 'Đã duyệt đơn nghỉ phép từ chatbot.',
                        'state_diff' => 'Trạng thái: ' . $status . ' -> Đã duyệt',
                    ];
                });

                return is_array($result) ? $result : ['ok' => false, 'message' => 'Không thể duyệt đơn nghỉ phép từ chatbot.'];
            } catch (\Throwable) {
                return ['ok' => false, 'message' => 'Không thể duyệt đơn nghỉ phép từ chatbot.'];
            }
        }

        if ($actionType === 'leave_reject') {
            $leaveId = (int) ($payload['ma_np'] ?? 0);
            if ($leaveId <= 0) {
                return ['ok' => false, 'message' => 'Thiếu mã đơn nghỉ phép để từ chối.'];
            }

            $leave = $connection->table('nghiphep')->where('MaNP', $leaveId)->first();
            if ($leave === null) {
                return ['ok' => false, 'message' => 'Không tìm thấy đơn nghỉ phép cần từ chối.'];
            }

            $status = trim((string) ($leave->TrangThai ?? ''));
            if ($status !== 'Chờ duyệt') {
                return ['ok' => false, 'message' => 'Đơn nghỉ phép không còn ở trạng thái Chờ duyệt.'];
            }

            $connection->table('nghiphep')->where('MaNP', $leaveId)->update([
                'TrangThai' => 'Từ chối',
                'NgayDuyet' => now()->toDateString(),
            ]);

            return [
                'ok' => true,
                'message' => 'Đã từ chối đơn nghỉ phép từ chatbot.',
                'state_diff' => 'Trạng thái: ' . $status . ' -> Từ chối',
            ];
        }

        if ($actionType === 'leave_create') {
            $currentEmployeeId = $this->currentEmployeeId();
            $employeeId = (int) ($payload['ma_nv'] ?? 0);
            $startDate = trim((string) ($payload['tu_ngay'] ?? ''));
            $endDate = trim((string) ($payload['den_ngay'] ?? ''));
            $reason = trim((string) ($payload['ly_do'] ?? ''));
            $leaveType = trim((string) ($payload['loai_nghi'] ?? 'Nghỉ phép năm'));

            if ($employeeId <= 0 || $startDate === '' || $endDate === '') {
                return ['ok' => false, 'message' => 'Thiếu dữ liệu để tạo đơn nghỉ phép.'];
            }

            if ($currentEmployeeId > 0 && $currentEmployeeId !== $employeeId) {
                return ['ok' => false, 'message' => 'Bạn chỉ có thể tạo đơn nghỉ phép cho chính mình từ chatbot.'];
            }

            try {
                $start = new DateTime($startDate);
                $end = new DateTime($endDate);
            } catch (\Throwable) {
                return ['ok' => false, 'message' => 'Ngày nghỉ phép không hợp lệ.'];
            }

            if ($end < $start) {
                return ['ok' => false, 'message' => 'Ngày kết thúc phải lớn hơn hoặc bằng ngày bắt đầu.'];
            }

            $days = (int) $start->diff($end)->days + 1;
            $newId = $connection->table('nghiphep')->insertGetId([
                'MaNV' => $employeeId,
                'TuNgay' => $startDate,
                'DenNgay' => $endDate,
                'SoNgayNghi' => $days,
                'LyDo' => $reason,
                'LoaiNghi' => $leaveType,
            ]);

            return [
                'ok' => true,
                'message' => 'Đã tạo đơn nghỉ phép từ chatbot.',
                'state_diff' => 'Mã đơn mới: #' . $newId . ' | Trạng thái: Chờ duyệt',
            ];
        }

        if ($actionType === 'contract_extend') {
            $contractId = (int) ($payload['ma_hop_dong'] ?? 0);
            $newEndDate = trim((string) ($payload['new_end_date'] ?? ''));

            if ($contractId <= 0 || $newEndDate === '') {
                return ['ok' => false, 'message' => 'Thiếu dữ liệu để gia hạn hợp đồng.'];
            }

            $contract = $connection->table('hopdong')->where('MaHopDong', $contractId)->first();
            if ($contract === null) {
                return ['ok' => false, 'message' => 'Không tìm thấy hợp đồng cần gia hạn.'];
            }

            if (trim((string) ($contract->TrangThai ?? '')) !== 'Còn hiệu lực') {
                return ['ok' => false, 'message' => 'Chỉ gia hạn được hợp đồng còn hiệu lực.'];
            }

            if (empty($contract->NgayKetThuc)) {
                return ['ok' => false, 'message' => 'Hợp đồng không xác định thời hạn không cần gia hạn theo cách này.'];
            }

            try {
                $currentEnd = new DateTime((string) $contract->NgayKetThuc);
                $newEnd = new DateTime($newEndDate);
            } catch (\Throwable) {
                return ['ok' => false, 'message' => 'Ngày gia hạn hợp đồng không hợp lệ.'];
            }

            if ($newEnd <= $currentEnd) {
                return ['ok' => false, 'message' => 'Ngày kết thúc mới phải lớn hơn ngày kết thúc hiện tại.'];
            }

            $newStartDate = (clone $currentEnd)->modify('+1 day')->format('Y-m-d');
            $contractNumber = $this->generateRenewalContractNumber((string) ($contract->SoHopDong ?? ''), $newEnd->format('Ymd'));
            $newContractId = $connection->table('hopdong')->insertGetId([
                'MaNV' => (int) ($contract->MaNV ?? 0),
                'MaBac' => (int) ($contract->MaBac ?? 0),
                'SoHopDong' => $contractNumber,
                'LoaiHopDong' => (string) ($contract->LoaiHopDong ?? 'Xác định thời hạn'),
                'NgayKy' => now()->toDateString(),
                'NgayBatDau' => $newStartDate,
                'NgayKetThuc' => $newEnd->format('Y-m-d'),
                'TrangThai' => 'Còn hiệu lực',
                'HopDongGoc' => $contractId,
            ]);

            return [
                'ok' => true,
                'message' => 'Đã tạo hợp đồng gia hạn từ chatbot.',
                'state_diff' => 'Hợp đồng mới: #' . $newContractId . ' | Số HĐ: ' . $contractNumber,
            ];
        }

        return ['ok' => false, 'message' => 'Loại hành động này chưa được hỗ trợ thực thi.'];
    }

    private function requiredPermissionForAction(string $actionType): string
    {
        return [
            'leave_approve' => 'duyet_nghiphep',
            'leave_reject' => 'duyet_nghiphep',
            'leave_create' => 'them_nghiphep',
            'contract_extend' => 'giahan_hopdong',
        ][$actionType] ?? '';
    }

    private function currentEmployeeId(): int
    {
        $account = (array) session('taikhoan', []);
        if (!empty($account['MaNVRef'])) {
            return (int) $account['MaNVRef'];
        }

        $employeeCode = trim((string) ($account['MaNV'] ?? ''));
        if ($employeeCode !== '' && preg_match('/(\d+)/', $employeeCode, $matches) === 1) {
            return (int) $matches[1];
        }

        return 0;
    }

    private function generateRenewalContractNumber(string $baseNumber, string $suffix): string
    {
        $baseNumber = trim($baseNumber);
        if ($baseNumber === '') {
            $baseNumber = 'HD';
        }

        $connection = DB::connection($this->hrConnection());
        $candidate = $baseNumber . '-GH-' . $suffix;
        $counter = 1;

        while ($connection->table('hopdong')->where('SoHopDong', $candidate)->exists()) {
            $candidate = $baseNumber . '-GH-' . $suffix . '-' . $counter;
            $counter++;
        }

        return $candidate;
    }

    private function getChatSessionId(): int
    {
        $sessionKey = (string) session('chatbot_session_key', '');
        if ($sessionKey === '') {
            $sessionKey = bin2hex(random_bytes(16));
            session(['chatbot_session_key' => $sessionKey]);
        }

        $account = (array) session('taikhoan', []);
        $connection = DB::connection($this->hrConnection());
        $existing = $connection->table('chatbot_sessions')->where('session_key', $sessionKey)->first();

        if ($existing !== null) {
            $connection->table('chatbot_sessions')->where('id', $existing->id)->update([
                'username' => (string) ($account['TenDangNhap'] ?? 'unknown'),
                'role_name' => (string) ($account['VaiTro'] ?? 'unknown'),
                'last_interaction_at' => now(),
            ]);

            return (int) $existing->id;
        }

        return (int) $connection->table('chatbot_sessions')->insertGetId([
            'session_key' => $sessionKey,
            'ma_tk' => (int) session('MaTK', 0),
            'username' => (string) ($account['TenDangNhap'] ?? 'unknown'),
            'role_name' => (string) ($account['VaiTro'] ?? 'unknown'),
            'created_at' => now(),
            'last_interaction_at' => now(),
        ]);
    }

    private function logMessage(int $sessionId, string $role, string $content, string $source = '', array $actions = [], array $suggestions = [], ?string $actionDraftToken = null): void
    {
        DB::connection($this->hrConnection())->table('chatbot_messages')->insert([
            'session_id' => $sessionId,
            'role_name' => $role,
            'content' => $content,
            'source_name' => $source !== '' ? $source : null,
            'actions_json' => !empty($actions) ? json_encode(array_values($actions), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
            'suggestions_json' => !empty($suggestions) ? json_encode(array_values($suggestions), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
            'action_draft_token' => $actionDraftToken,
            'created_at' => now(),
        ]);
    }

    private function createActionDraft(int $sessionId, int $createdBy, array $draft): string
    {
        $token = bin2hex(random_bytes(16));

        DB::connection($this->hrConnection())->table('chatbot_action_drafts')->insert([
            'session_id' => $sessionId,
            'token' => $token,
            'action_type' => (string) ($draft['action_type'] ?? 'unknown'),
            'title' => (string) ($draft['title'] ?? 'Hành động Chatbot'),
            'summary' => (string) ($draft['summary'] ?? ''),
            'permission_required' => (string) ($draft['required_permission'] ?? ''),
            'payload_json' => json_encode($draft['payload'] ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'status_name' => 'pending',
            'created_by' => $createdBy,
            'created_at' => now(),
        ]);

        return $token;
    }

    private function getPendingActionDraft(string $token, int $currentAccountId): ?object
    {
        return DB::connection($this->hrConnection())
            ->table('chatbot_action_drafts')
            ->where('token', $token)
            ->where('status_name', 'pending')
            ->where('created_by', $currentAccountId)
            ->where('created_at', '>=', now()->subMinutes(10))
            ->first();
    }

    private function callBotService(string $url, array $payload): array
    {
        try {
            $request = Http::acceptJson()->connectTimeout(3)->timeout(20);
            $sharedSecret = trim((string) env('APP_SHARED_SECRET', ''));
            if ($sharedSecret !== '') {
                $request = $request->withHeaders(['X-App-Secret' => $sharedSecret]);
            }

            $response = $request->post($url, $payload);
            if (!$response->successful()) {
                return ['ok' => false, 'error' => 'BOT_HTTP_' . $response->status()];
            }

            $data = $response->json();
            if (!is_array($data)) {
                return ['ok' => false, 'error' => 'INVALID_JSON_RESPONSE'];
            }

            return ['ok' => true, 'data' => $data];
        } catch (\Throwable $exception) {
            return ['ok' => false, 'error' => $exception->getMessage() !== '' ? $exception->getMessage() : 'BOT_CALL_FAILED'];
        }
    }

    private function chatEndpoint(): string
    {
        return (string) (env('BOT_SERVICE_URL') ?: 'http://127.0.0.1:8001/chat');
    }

    private function briefEndpoint(): string
    {
        return rtrim((string) preg_replace('#/chat$#', '', $this->chatEndpoint()), '/') . '/brief';
    }

    private function hrConnection(): string
    {
        return (string) config('service_registry.services.hr.connection', config('database.default'));
    }
}