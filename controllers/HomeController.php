<?php

require_once "models/DashboardModel.php";
require_once 'models/TaiKhoanModel.php';
require_once 'core/AuthMiddleware.php';
require_once 'core/AppLogger.php';
require_once 'core/WebResponder.php';

class HomeController{

    private $model;
    private $accountModel;
    private $conn;

    public function __construct($conn){
        $this->conn = $conn;
        $this->model = new DashboardModel($conn);
        $this->accountModel = new TaiKhoanModel($conn);
    }

    public function index(){

        // thống kê
        $tongNhanVien = $this->model->tongNhanVien();
        $tongPhongBan = $this->model->tongPhongBan();
        $donNghiChoDuyet = $this->model->donNghiChoDuyet();
        $tongUngVien = $this->model->tongUngVien();

        // biểu đồ nhân viên phòng ban
        $nhanVienPhongBan = $this->model->nhanVienTheoPhongBan();

        // thông báo (đã xem lưu theo tài khoản)
        $maTK = (int)($_SESSION['MaTK'] ?? 0);
        $notificationSnapshot = $this->model->getDashboardNotificationSnapshot($maTK);
        $tbNghiPhep = (int)$notificationSnapshot['totals']['leave'];
        $tbHopDong = (int)$notificationSnapshot['totals']['contract'];
        $tbUngVien = (int)$notificationSnapshot['totals']['candidate'];
        $unreadNghiPhep = (int)$notificationSnapshot['unread']['leave'];
        $unreadHopDong = (int)$notificationSnapshot['unread']['contract'];
        $unreadUngVien = (int)$notificationSnapshot['unread']['candidate'];
        $lastNghiPhepText = $this->toRelativeDateText($notificationSnapshot['latest']['leave']);
        $lastHopDongText = $this->toRelativeDateText($notificationSnapshot['latest']['contract']);
        $lastUngVienText = $this->toRelativeDateText($notificationSnapshot['latest']['candidate']);
        $luongPhongBan = $this->model->luongTrungBinhPhongBan();

        // nhân viên nam nữ
        $tongNam = $this->model->tongNam();
        $tongNu = $this->model->tongNu();

        

        include "views/home/index.php";
    }

    public function markNotificationsRead(){
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            WebResponder::ajaxText('{"ok":false,"message":"METHOD_NOT_ALLOWED"}', 405);
        }

        $maTK = (int)($_SESSION['MaTK'] ?? 0);
        if ($maTK <= 0) {
            WebResponder::ajaxText('{"ok":false,"message":"UNAUTHORIZED"}', 401);
        }

        $type = (string)($_POST['type'] ?? 'all');
        $allowed = ['all', 'leave', 'contract', 'candidate'];
        if (!in_array($type, $allowed, true)) {
            WebResponder::ajaxText('{"ok":false,"message":"INVALID_TYPE"}', 422);
        }

        $ok = $this->model->markNotificationsRead($maTK, $type);
        if (!$ok) {
            WebResponder::ajaxText('{"ok":false,"message":"SAVE_FAILED"}', 500);
        }

        WebResponder::ajaxText('{"ok":true}', 200);
    }

    public function settings(){
        $maTK = (int)($_SESSION['MaTK'] ?? 0);
        $taiKhoan = $maTK > 0 ? $this->accountModel->getById($maTK) : null;
        $isAdmin = AuthMiddleware::isAdmin($this->conn);

        if ($maTK > 0 && empty($_SESSION['session_marker'])) {
            $_SESSION['session_marker'] = bin2hex(random_bytes(32));
        }

        if ($maTK > 0 && !empty($_SESSION['session_marker'])) {
            $this->accountModel->registerSessionAudit(
                $maTK,
                (string)$_SESSION['session_marker'],
                (string)($_SERVER['HTTP_USER_AGENT'] ?? ''),
                (string)($_SERVER['REMOTE_ADDR'] ?? '')
            );
            $this->accountModel->touchSessionAudit($maTK, (string)$_SESSION['session_marker']);
        }

        $recentSessionsRaw = $maTK > 0 ? $this->accountModel->getRecentSessions($maTK, 8) : [];
        $currentMarker = (string)($_SESSION['session_marker'] ?? '');
        $recentSessions = [];
        foreach ($recentSessionsRaw as $row) {
            $row['is_current'] = !empty($currentMarker) && (string)($row['session_marker'] ?? '') === $currentMarker;
            $recentSessions[] = $row;
        }

        $sessionInfo = [
            'session_id' => session_id(),
            'session_marker' => (string)($_SESSION['session_marker'] ?? ''),
            'must_change_password' => !empty($_SESSION['must_change_password']) || !empty($_SESSION['taikhoan']['BuocDoiMatKhau']),
            'username' => (string)($taiKhoan['TenDangNhap'] ?? ($_SESSION['taikhoan']['TenDangNhap'] ?? '')),
            'employee_code' => (string)($taiKhoan['MaNV'] ?? ($_SESSION['taikhoan']['MaNV'] ?? '')),
            'role' => (string)($taiKhoan['VaiTro'] ?? ($_SESSION['taikhoan']['VaiTro'] ?? '')), 
        ];

        include "views/home/settings.php";
    }

    public function capNhatTenDangNhap(){
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = 'Yêu cầu không hợp lệ.';
            header('Location: index.php?controller=home&action=settings');
            exit;
        }

        $maTK = (int)($_SESSION['MaTK'] ?? 0);
        if ($maTK <= 0) {
            $_SESSION['error'] = 'Phiên đăng nhập không hợp lệ.';
            header('Location: index.php?controller=dangnhap&action=login');
            exit;
        }

        $account = $this->accountModel->getById($maTK);
        if (!$account) {
            $_SESSION['error'] = 'Không tìm thấy tài khoản hiện tại.';
            header('Location: index.php?controller=dangnhap&action=login');
            exit;
        }

        $newUsername = trim((string)($_POST['TenDangNhapMoi'] ?? ''));
        $passwordConfirm = (string)($_POST['MatKhauXacNhan'] ?? '');

        if ($newUsername === '' || !preg_match('/^[A-Za-z0-9_.]{4,50}$/', $newUsername)) {
            $_SESSION['error'] = 'Tên đăng nhập mới chỉ gồm chữ, số, dấu chấm, gạch dưới và từ 4-50 ký tự.';
            header('Location: index.php?controller=home&action=settings');
            exit;
        }

        if (!password_verify($passwordConfirm, (string)($account['MatKhau'] ?? ''))) {
            $_SESSION['error'] = 'Mật khẩu xác nhận không đúng.';
            header('Location: index.php?controller=home&action=settings');
            exit;
        }

        if (strcasecmp($newUsername, (string)($account['TenDangNhap'] ?? '')) === 0) {
            $_SESSION['error'] = 'Tên đăng nhập mới trùng tên đăng nhập hiện tại.';
            header('Location: index.php?controller=home&action=settings');
            exit;
        }

        if (!$this->accountModel->isUsernameAvailable($newUsername, $maTK)) {
            $_SESSION['error'] = 'Tên đăng nhập mới đã tồn tại.';
            header('Location: index.php?controller=home&action=settings');
            exit;
        }

        $ok = $this->accountModel->updateUsernameByMaTK($maTK, $newUsername);
        if (!$ok) {
            $_SESSION['error'] = 'Không thể cập nhật tên đăng nhập. Vui lòng thử lại.';
            header('Location: index.php?controller=home&action=settings');
            exit;
        }

        $freshAccount = $this->accountModel->getById($maTK);
        if ($freshAccount) {
            $_SESSION['taikhoan'] = $freshAccount;
        }

        AppLogger::security('User changed own username from settings', [
            'MaTK' => $maTK,
            'new_username' => $newUsername,
        ]);

        $_SESSION['success'] = 'Cập nhật tên đăng nhập thành công.';
        header('Location: index.php?controller=home&action=settings');
        exit;
    }

    public function capNhatTaiKhoan(){
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = 'Yêu cầu không hợp lệ.';
            header('Location: index.php?controller=home&action=settings');
            exit;
        }

        $maTK = (int)($_SESSION['MaTK'] ?? 0);
        if ($maTK <= 0) {
            $_SESSION['error'] = 'Phiên đăng nhập không hợp lệ.';
            header('Location: index.php?controller=dangnhap&action=login');
            exit;
        }

        $account = $this->accountModel->getById($maTK);
        if (!$account) {
            $_SESSION['error'] = 'Không tìm thấy tài khoản hiện tại.';
            header('Location: index.php?controller=dangnhap&action=login');
            exit;
        }

        $currentPassword = (string)($_POST['MatKhauHienTai'] ?? '');
        $newPassword = (string)($_POST['MatKhauMoi'] ?? '');
        $confirmPassword = (string)($_POST['XacNhanMatKhauMoi'] ?? '');

        if (!password_verify($currentPassword, (string)($account['MatKhau'] ?? ''))) {
            $_SESSION['error'] = 'Mật khẩu hiện tại không đúng.';
            header('Location: index.php?controller=home&action=settings');
            exit;
        }

        if (strlen($newPassword) < 8) {
            $_SESSION['error'] = 'Mật khẩu mới phải có ít nhất 8 ký tự.';
            header('Location: index.php?controller=home&action=settings');
            exit;
        }

        if ($newPassword !== $confirmPassword) {
            $_SESSION['error'] = 'Xác nhận mật khẩu mới không khớp.';
            header('Location: index.php?controller=home&action=settings');
            exit;
        }

        if (password_verify($newPassword, (string)($account['MatKhau'] ?? ''))) {
            $_SESSION['error'] = 'Mật khẩu mới không được trùng mật khẩu hiện tại.';
            header('Location: index.php?controller=home&action=settings');
            exit;
        }

        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $ok = $this->accountModel->updatePasswordByMaTK($maTK, $newHash, false);

        if (!$ok) {
            $_SESSION['error'] = 'Không thể cập nhật mật khẩu. Vui lòng thử lại.';
            header('Location: index.php?controller=home&action=settings');
            exit;
        }

        $freshAccount = $this->accountModel->getById($maTK);
        if ($freshAccount) {
            $_SESSION['taikhoan'] = $freshAccount;
        }

        $_SESSION['must_change_password'] = false;
        session_regenerate_id(true);

        AppLogger::security('User changed own password from settings', ['MaTK' => $maTK]);
        $_SESSION['success'] = 'Đổi mật khẩu thành công.';
        header('Location: index.php?controller=home&action=settings');
        exit;
    }

    public function lamMoiPhien(){
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = 'Yêu cầu không hợp lệ.';
            header('Location: index.php?controller=home&action=settings');
            exit;
        }

        session_regenerate_id(true);

        $maTK = (int)($_SESSION['MaTK'] ?? 0);
        $marker = (string)($_SESSION['session_marker'] ?? '');
        if ($maTK > 0 && $marker !== '') {
            $this->accountModel->registerSessionAudit(
                $maTK,
                $marker,
                (string)($_SERVER['HTTP_USER_AGENT'] ?? ''),
                (string)($_SERVER['REMOTE_ADDR'] ?? '')
            );
        }

        $_SESSION['success'] = 'Đã làm mới phiên đăng nhập hiện tại.';
        header('Location: index.php?controller=home&action=settings');
        exit;
    }

    public function dangXuatPhienKhac(){
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = 'Yêu cầu không hợp lệ.';
            header('Location: index.php?controller=home&action=settings');
            exit;
        }

        $maTK = (int)($_SESSION['MaTK'] ?? 0);
        $marker = (string)($_SESSION['session_marker'] ?? '');
        if ($maTK <= 0 || $marker === '') {
            $_SESSION['error'] = 'Không thể xác định phiên hiện tại.';
            header('Location: index.php?controller=home&action=settings');
            exit;
        }

        $ok = $this->accountModel->revokeOtherSessions($maTK, $marker);
        if (!$ok) {
            $_SESSION['error'] = 'Không thể đăng xuất các phiên khác. Vui lòng thử lại.';
            header('Location: index.php?controller=home&action=settings');
            exit;
        }

        AppLogger::security('User revoked other sessions from settings', ['MaTK' => $maTK]);
        $_SESSION['success'] = 'Đã đăng xuất tất cả phiên khác của tài khoản này.';
        header('Location: index.php?controller=home&action=settings');
        exit;
    }

    private function toRelativeDateText($dateString){
        if (empty($dateString)) {
            return 'vừa xong';
        }

        try {
            $eventDate = new DateTime($dateString);
            $now = new DateTime();
            $eventDay = (clone $eventDate)->setTime(0, 0, 0);
            $today = (clone $now)->setTime(0, 0, 0);
            $dayDiff = (int)$today->diff($eventDay)->format('%r%a');

            $raw = (string)$dateString;
            $hasExplicitTime = (strpos($raw, ':') !== false) && ($eventDate->format('H:i') !== '00:00');
            $timePart = $hasExplicitTime ? (' ' . $eventDate->format('H:i')) : '';

            if ($dayDiff === 0) {
                return 'hôm nay' . $timePart;
            }

            if ($dayDiff > 0) {
                return 'còn ' . $dayDiff . ' ngày' . $timePart;
            }

            return abs($dayDiff) . ' ngày trước' . $timePart;
        } catch (Exception $e) {
            return 'vừa xong';
        }
    }

}