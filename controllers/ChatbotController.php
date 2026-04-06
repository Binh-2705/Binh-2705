<?php
require_once 'core/AppLogger.php';
require_once 'core/AuthMiddleware.php';
require_once 'models/ChatbotAuditModel.php';
require_once 'models/HopDongModel.php';
require_once 'models/NghiPhepModel.php';

class ChatbotController {
    private $conn;
    private $auditModel;
    private $contractModel;
    private $leaveModel;

    public function __construct($conn) {
        $this->conn = $conn;
        $this->auditModel = new ChatbotAuditModel($conn);
        $this->contractModel = new HopDongModel($conn);
        $this->leaveModel = new NghiPhepModel($conn);
    }

    public function index() {
        $quyen = $_SESSION['quyen'] ?? [];
        include 'views/chatbot/index.php';
    }

    public function audit() {
        AuthMiddleware::check($this->conn, 'xem_taikhoan');
        $quyen = $_SESSION['quyen'] ?? [];

        $q = trim((string)($_GET['q'] ?? ''));
        $source = trim((string)($_GET['source'] ?? ''));
        $status = trim((string)($_GET['status'] ?? ''));

        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $totalItems = $this->auditModel->getAuditRowsCount($q, $source, $status);
        $totalPages = max(1, (int)ceil($totalItems / $perPage));
        if ($page > $totalPages) {
            $page = $totalPages;
        }
        $offset = ($page - 1) * $perPage;
        $logs = $this->auditModel->getAuditRows($q, $source, $status, $perPage, $offset);

        include 'views/chatbot/audit.php';
    }

    public function ask() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['ok' => false, 'message' => 'METHOD_NOT_ALLOWED'], 405);
            return;
        }

        if (empty($_SESSION['taikhoan'])) {
            $this->json(['ok' => false, 'message' => 'SESSION_EXPIRED'], 401);
            return;
        }

        $message = trim((string)($_POST['message'] ?? ''));
        if ($message === '') {
            $this->json(['ok' => false, 'message' => 'EMPTY_MESSAGE'], 422);
            return;
        }

        AppLogger::info('Chatbot question received', [
            'ma_tk' => (int)($_SESSION['MaTK'] ?? 0),
        ]);

        $history = $_SESSION['chatbot_history'] ?? [];
        if (!is_array($history)) {
            $history = [];
        }

        $history[] = [
            'role' => 'user',
            'content' => $message,
        ];

        $history = array_slice($history, -10);

        $account = $_SESSION['taikhoan'] ?? [];
        $sessionId = $this->getChatSessionId($account);
        $this->auditModel->logMessage($sessionId, 'user', $message, 'user_input');

        $payload = [
            'message' => $message,
            'history' => $history,
            'user' => [
                'ma_tk' => (int)($_SESSION['MaTK'] ?? 0),
                'username' => (string)($account['TenDangNhap'] ?? ''),
                'role' => (string)($account['VaiTro'] ?? ''),
                'permissions' => array_values(array_slice((array)($_SESSION['quyen'] ?? []), 0, 80)),
            ],
        ];

        $botUrl = getenv('BOT_SERVICE_URL') ?: 'http://127.0.0.1:8001/chat';
        $result = $this->callBotService($botUrl, $payload);

        if (!$result['ok']) {
            AppLogger::error('Chatbot service call failed', [
                'error' => $result['error'],
                'url' => $botUrl,
            ]);

            $fallback = 'Bot service chưa sẵn sàng. Hãy chạy Python service và thử lại.';
            $history[] = ['role' => 'assistant', 'content' => $fallback];
            $_SESSION['chatbot_history'] = array_slice($history, -10);
            $this->auditModel->logMessage($sessionId, 'assistant', $fallback, 'fallback');

            $this->json([
                'ok' => true,
                'reply' => $fallback,
                'actions' => [],
                'suggestions' => [
                    'Tổng số nhân viên hiện tại là bao nhiêu?',
                    'Thống kê nghỉ phép',
                    'Hợp đồng sắp hết hạn',
                ],
                'source' => 'fallback',
            ], 200);
            return;
        }

        $reply = trim((string)($result['data']['reply'] ?? ''));
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
            $token = $this->auditModel->createActionDraft($sessionId, (int)($_SESSION['MaTK'] ?? 0), $draft);
            $actionDraftResponse = [
                'token' => $token,
                'title' => (string)($draft['title'] ?? 'Xác nhận hành động'),
                'summary' => (string)($draft['summary'] ?? ''),
                'confirm_label' => (string)($draft['confirm_label'] ?? 'Xác nhận thực thi'),
                'action_type' => (string)($draft['action_type'] ?? ''),
            ];
        }

        $history[] = ['role' => 'assistant', 'content' => $reply];
        $_SESSION['chatbot_history'] = array_slice($history, -10);
        $this->auditModel->logMessage(
            $sessionId,
            'assistant',
            $reply,
            (string)($result['data']['source'] ?? 'bot_service'),
            $actions,
            $suggestions,
            $actionDraftResponse['token'] ?? null
        );

        AppLogger::info('Chatbot answer returned', [
            'ma_tk' => (int)($_SESSION['MaTK'] ?? 0),
            'source' => (string)($result['data']['source'] ?? 'bot_service'),
            'suggestion_count' => count($suggestions),
        ]);

        $this->json([
            'ok' => true,
            'reply' => $reply,
            'actions' => array_values($actions),
            'suggestions' => array_values($suggestions),
            'action_draft' => $actionDraftResponse,
            'source' => (string)($result['data']['source'] ?? 'bot_service'),
        ], 200);
    }

    public function confirmDraft() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['ok' => false, 'message' => 'METHOD_NOT_ALLOWED'], 405);
            return;
        }

        if (empty($_SESSION['taikhoan'])) {
            $this->json(['ok' => false, 'message' => 'SESSION_EXPIRED'], 401);
            return;
        }

        $token = trim((string)($_POST['action_token'] ?? ''));
        if ($token === '') {
            $this->json(['ok' => false, 'message' => 'MISSING_ACTION_TOKEN'], 422);
            return;
        }

        $currentMaTK = (int)($_SESSION['MaTK'] ?? 0);
        $draft = $this->auditModel->getPendingActionDraft($token, $currentMaTK);
        if (!$draft) {
            $this->json(['ok' => false, 'message' => 'ACTION_DRAFT_NOT_FOUND_OR_EXPIRED'], 404);
            return;
        }

        $confirmReason = trim((string)($_POST['confirm_reason'] ?? ''));
        $actionType = (string)($draft['action_type'] ?? '');
        $requiresReason = in_array($actionType, ['leave_approve', 'leave_reject'], true);
        if ($requiresReason && $confirmReason === '') {
            $this->json(['ok' => false, 'message' => 'CONFIRM_REASON_REQUIRED'], 422);
            return;
        }

        $actionType = (string)($draft['action_type'] ?? '');
        $requiredPermission = $this->getRequiredPermissionForAction($actionType);
        if ($requiredPermission !== '' && !in_array($requiredPermission, (array)($_SESSION['quyen'] ?? []), true)) {
            $this->json(['ok' => false, 'message' => 'FORBIDDEN'], 403);
            return;
        }

        $payload = json_decode((string)($draft['payload_json'] ?? '{}'), true);
        if (!is_array($payload)) {
            $payload = [];
        }

        $result = $this->executeDraft($actionType, $payload, $confirmReason);
        $status = $result['ok'] ? 'executed' : 'failed';
        $message = (string)($result['message'] ?? 'Không xác định');

        if ($confirmReason !== '') {
            $message .= ' | Lý do xác nhận: ' . $confirmReason;
        }

        $stateDiff = trim((string)($result['state_diff'] ?? ''));
        if ($stateDiff !== '') {
            $message .= ' | ' . $stateDiff;
        }

        $this->auditModel->markActionDraftCompleted((int)$draft['id'], (int)($_SESSION['MaTK'] ?? 0), $status, $message);

        $sessionId = $this->getChatSessionId($_SESSION['taikhoan'] ?? []);
        $this->auditModel->logMessage($sessionId, 'system', $message, 'action_execution');

        $this->json([
            'ok' => (bool)$result['ok'],
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

    public function clearHistory() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['ok' => false, 'message' => 'METHOD_NOT_ALLOWED'], 405);
            return;
        }

        if (empty($_SESSION['taikhoan'])) {
            $this->json(['ok' => false, 'message' => 'SESSION_EXPIRED'], 401);
            return;
        }

        unset($_SESSION['chatbot_history']);
        $_SESSION['chatbot_session_key'] = bin2hex(random_bytes(16));

        $this->json([
            'ok' => true,
            'reply' => 'Đã xóa lịch sử hội thoại hiện tại.',
            'actions' => ['Lịch sử chat trong session đã được làm mới'],
            'suggestions' => ['Tổng số nhân viên hiện tại là bao nhiêu?', 'Thông tin cá nhân của tôi', 'Hợp đồng sắp hết hạn'],
        ]);
    }

    private function executeDraft(string $actionType, array $payload, string $confirmReason = ''): array {
        if ($actionType === 'leave_approve') {
            $maNP = (int)($payload['ma_np'] ?? 0);
            if ($maNP <= 0) {
                return ['ok' => false, 'message' => 'Thiếu mã đơn nghỉ phép để duyệt.'];
            }

            $leaveRow = $this->leaveModel->getNghiPhepById($maNP);
            $leave = $leaveRow ? mysqli_fetch_assoc($leaveRow) : null;
            if (!$leave) {
                return ['ok' => false, 'message' => 'Không tìm thấy đơn nghỉ phép cần duyệt.'];
            }

            $status = trim((string)($leave['TrangThai'] ?? ''));
            if ($status !== 'Chờ duyệt') {
                return ['ok' => false, 'message' => 'Đơn nghỉ phép không còn ở trạng thái Chờ duyệt.'];
            }

            if ($this->leaveModel->duyet($maNP)) {
                $afterRow = $this->leaveModel->getNghiPhepById($maNP);
                $afterLeave = $afterRow ? mysqli_fetch_assoc($afterRow) : null;
                $afterStatus = trim((string)($afterLeave['TrangThai'] ?? 'Đã duyệt'));
                return [
                    'ok' => true,
                    'message' => 'Đã duyệt đơn nghỉ phép từ chatbot.',
                    'state_diff' => 'Trạng thái: ' . $status . ' -> ' . $afterStatus,
                ];
            }

            return ['ok' => false, 'message' => 'Không thể duyệt đơn nghỉ phép từ chatbot.'];
        }

        if ($actionType === 'leave_reject') {
            $maNP = (int)($payload['ma_np'] ?? 0);
            if ($maNP <= 0) {
                return ['ok' => false, 'message' => 'Thiếu mã đơn nghỉ phép để từ chối.'];
            }

            $leaveRow = $this->leaveModel->getNghiPhepById($maNP);
            $leave = $leaveRow ? mysqli_fetch_assoc($leaveRow) : null;
            if (!$leave) {
                return ['ok' => false, 'message' => 'Không tìm thấy đơn nghỉ phép cần từ chối.'];
            }

            $status = trim((string)($leave['TrangThai'] ?? ''));
            if ($status !== 'Chờ duyệt') {
                return ['ok' => false, 'message' => 'Đơn nghỉ phép không còn ở trạng thái Chờ duyệt.'];
            }

            if ($this->leaveModel->tuchoi($maNP)) {
                $afterRow = $this->leaveModel->getNghiPhepById($maNP);
                $afterLeave = $afterRow ? mysqli_fetch_assoc($afterRow) : null;
                $afterStatus = trim((string)($afterLeave['TrangThai'] ?? 'Từ chối'));
                return [
                    'ok' => true,
                    'message' => 'Đã từ chối đơn nghỉ phép từ chatbot.',
                    'state_diff' => 'Trạng thái: ' . $status . ' -> ' . $afterStatus,
                ];
            }

            return ['ok' => false, 'message' => 'Không thể từ chối đơn nghỉ phép từ chatbot.'];
        }

        if ($actionType === 'leave_create') {
            $currentEmployeeId = $this->getCurrentEmployeeId();
            $maNV = (int)($payload['ma_nv'] ?? 0);
            $tuNgay = trim((string)($payload['tu_ngay'] ?? ''));
            $denNgay = trim((string)($payload['den_ngay'] ?? ''));
            $lyDo = trim((string)($payload['ly_do'] ?? ''));
            $loaiNghi = trim((string)($payload['loai_nghi'] ?? 'Nghỉ phép năm'));

            if ($maNV <= 0 || $tuNgay === '' || $denNgay === '') {
                return ['ok' => false, 'message' => 'Thiếu dữ liệu để tạo đơn nghỉ phép.'];
            }

            if ($currentEmployeeId > 0 && $currentEmployeeId !== $maNV) {
                return ['ok' => false, 'message' => 'Bạn chỉ có thể tạo đơn nghỉ phép cho chính mình từ chatbot.'];
            }

            try {
                $start = new DateTime($tuNgay);
                $end = new DateTime($denNgay);
            } catch (Exception $exception) {
                return ['ok' => false, 'message' => 'Ngày nghỉ phép không hợp lệ.'];
            }

            if ($end < $start) {
                return ['ok' => false, 'message' => 'Ngày kết thúc phải lớn hơn hoặc bằng ngày bắt đầu.'];
            }

            $soNgay = (int)$start->diff($end)->days + 1;
            if ($this->leaveModel->insertNghiPhep($maNV, $tuNgay, $denNgay, $soNgay, $lyDo, $loaiNghi)) {
                $newId = (int)mysqli_insert_id($this->conn);
                return [
                    'ok' => true,
                    'message' => 'Đã tạo đơn nghỉ phép từ chatbot.',
                    'state_diff' => 'Mã đơn mới: #' . $newId . ' | Trạng thái: Chờ duyệt',
                ];
            }

            return ['ok' => false, 'message' => 'Không thể tạo đơn nghỉ phép từ chatbot.'];
        }

        if ($actionType === 'contract_extend') {
            $maHopDong = (int)($payload['ma_hop_dong'] ?? 0);
            $newEndDate = trim((string)($payload['new_end_date'] ?? ''));
            if ($maHopDong <= 0 || $newEndDate === '') {
                return ['ok' => false, 'message' => 'Thiếu dữ liệu để gia hạn hợp đồng.'];
            }

            $contract = $this->contractModel->getHopDongById($maHopDong);
            if (!$contract) {
                return ['ok' => false, 'message' => 'Không tìm thấy hợp đồng cần gia hạn.'];
            }

            if (trim((string)($contract['TrangThai'] ?? '')) !== 'Còn hiệu lực') {
                return ['ok' => false, 'message' => 'Chỉ gia hạn được hợp đồng còn hiệu lực.'];
            }

            if (empty($contract['NgayKetThuc'])) {
                return ['ok' => false, 'message' => 'Hợp đồng không xác định thời hạn không cần gia hạn theo cách này.'];
            }

            try {
                $currentEnd = new DateTime((string)$contract['NgayKetThuc']);
                $newEnd = new DateTime($newEndDate);
            } catch (Exception $exception) {
                return ['ok' => false, 'message' => 'Ngày gia hạn hợp đồng không hợp lệ.'];
            }

            if ($newEnd <= $currentEnd) {
                return ['ok' => false, 'message' => 'Ngày kết thúc mới phải lớn hơn ngày kết thúc hiện tại.'];
            }

            $newStart = (clone $currentEnd)->modify('+1 day')->format('Y-m-d');
            $contractNumber = $this->generateRenewalContractNumber((string)($contract['SoHopDong'] ?? ''), $newEnd->format('Ymd'));
            $data = [
                'MaNV' => (int)($contract['MaNV'] ?? 0),
                'MaBac' => (int)($contract['MaBac'] ?? 0),
                'HopDongGoc' => $maHopDong,
                'SoHopDong' => $contractNumber,
                'LoaiHopDong' => (string)($contract['LoaiHopDong'] ?? 'Xác định thời hạn'),
                'NgayBatDau' => $newStart,
                'NgayKetThuc' => $newEnd->format('Y-m-d'),
            ];

            if ($this->contractModel->insertHopDongGiaHan($data)) {
                $newContractId = (int)mysqli_insert_id($this->conn);
                return [
                    'ok' => true,
                    'message' => 'Đã tạo hợp đồng gia hạn từ chatbot.',
                    'state_diff' => 'Hợp đồng mới: #' . $newContractId . ' | Số HĐ: ' . $contractNumber,
                ];
            }

            return ['ok' => false, 'message' => 'Không thể tạo hợp đồng gia hạn từ chatbot.'];
        }

        return ['ok' => false, 'message' => 'Loại hành động này chưa được hỗ trợ thực thi.'];
    }

    private function getRequiredPermissionForAction(string $actionType): string {
        $actionPermissionMap = [
            'leave_approve' => 'duyet_nghiphep',
            'leave_reject' => 'duyet_nghiphep',
            'leave_create' => 'them_nghiphep',
            'contract_extend' => 'giahan_hopdong',
        ];
        return (string)($actionPermissionMap[$actionType] ?? '');
    }

    private function getCurrentEmployeeId(): int {
        $account = $_SESSION['taikhoan'] ?? [];
        if (!empty($account['MaNVRef'])) {
            return (int)$account['MaNVRef'];
        }

        $maNV = trim((string)($account['MaNV'] ?? ''));
        if ($maNV !== '' && preg_match('/(\d+)/', $maNV, $matches)) {
            return (int)$matches[1];
        }

        return 0;
    }

    private function generateRenewalContractNumber(string $baseNumber, string $suffix): string {
        $baseNumber = trim($baseNumber);
        if ($baseNumber === '') {
            $baseNumber = 'HD';
        }

        $candidate = $baseNumber . '-GH-' . $suffix;
        $counter = 1;
        while ($this->contractModel->checkSoHopDongExists($candidate)) {
            $candidate = $baseNumber . '-GH-' . $suffix . '-' . $counter;
            $counter++;
        }

        return $candidate;
    }

    private function getChatSessionId(array $account): int {
        if (empty($_SESSION['chatbot_session_key'])) {
            $_SESSION['chatbot_session_key'] = bin2hex(random_bytes(16));
        }

        return $this->auditModel->getOrCreateSession(
            (string)$_SESSION['chatbot_session_key'],
            (int)($_SESSION['MaTK'] ?? 0),
            (string)($account['TenDangNhap'] ?? 'unknown'),
            (string)($account['VaiTro'] ?? 'unknown')
        );
    }

    public function brief() {
        if (empty($_SESSION['taikhoan'])) {
            $this->json(['ok' => false, 'message' => 'SESSION_EXPIRED'], 401);
            return;
        }

        $account = $_SESSION['taikhoan'] ?? [];
        $payload = [
            'user' => [
                'ma_tk'       => (int)($_SESSION['MaTK'] ?? 0),
                'username'    => (string)($account['TenDangNhap'] ?? ''),
                'role'        => (string)($account['VaiTro'] ?? ''),
                'permissions' => array_values(array_slice((array)($_SESSION['quyen'] ?? []), 0, 80)),
            ],
        ];

        $baseUrl = getenv('BOT_SERVICE_URL') ?: 'http://127.0.0.1:8001/chat';
        $briefUrl = rtrim(preg_replace('#/chat$#', '', $baseUrl), '/') . '/brief';

        $result = $this->callBotService($briefUrl, $payload);
        if (!$result['ok']) {
            $this->json(['ok' => true, 'items' => [], 'source' => 'fallback']);
            return;
        }

        $this->json([
            'ok'     => true,
            'items'  => array_values((array)($result['data']['items'] ?? [])),
            'source' => (string)($result['data']['source'] ?? 'brief'),
        ]);
    }

    private function callBotService(string $url, array $payload): array {
        $ch = curl_init($url);
        if ($ch === false) {
            return ['ok' => false, 'error' => 'CURL_INIT_FAILED'];
        }

        $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            return ['ok' => false, 'error' => 'JSON_ENCODE_FAILED'];
        }

        $headers = [
            'Content-Type: application/json',
            'Accept: application/json',
        ];

        $sharedSecret = trim((string)(getenv('APP_SHARED_SECRET') ?: ''));
        if ($sharedSecret !== '') {
            $headers[] = 'X-App-Secret: ' . $sharedSecret;
        }

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);

        $response = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false || $error !== '') {
            return ['ok' => false, 'error' => $error !== '' ? $error : 'CURL_EXEC_FAILED'];
        }

        $decoded = json_decode($response, true);
        if (!is_array($decoded)) {
            return ['ok' => false, 'error' => 'INVALID_JSON_RESPONSE'];
        }

        if ($httpCode >= 400) {
            return ['ok' => false, 'error' => 'BOT_HTTP_' . $httpCode];
        }

        return ['ok' => true, 'data' => $decoded];
    }

    private function json(array $data, int $statusCode = 200): void {
        http_response_code($statusCode);
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=UTF-8');
        }
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
