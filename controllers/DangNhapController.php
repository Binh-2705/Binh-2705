<?php

require_once 'models/TaiKhoanModel.php';
require_once 'core/AppLogger.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class DangNhapController {
    private $model;

    public function __construct($conn){
        $this->model = new TaiKhoanModel($conn);
    }

   public function login(){

    // ✅ phải có session
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if($_POST){
        $tenDangNhap = trim((string)($_POST['TenDangNhap'] ?? ''));
        $matKhau = (string)($_POST['MatKhau'] ?? '');
        $tk = $this->model->dangNhap(
            $tenDangNhap,
            $matKhau
        );

        if($tk){

    // ✅ PREVENT SESSION FIXATION: Regenerate session ID after successful login
    session_regenerate_id(true);

    $_SESSION['MaTK'] = $tk['MaTK'];
    $_SESSION['taikhoan'] = $tk;
    $_SESSION['must_change_password'] = !empty($tk['BuocDoiMatKhau']);
    if (empty($_SESSION['session_marker'])) {
        $_SESSION['session_marker'] = bin2hex(random_bytes(32));
    }
    $this->model->registerSessionAudit(
        (int)$tk['MaTK'],
        (string)$_SESSION['session_marker'],
        (string)($_SERVER['HTTP_USER_AGENT'] ?? ''),
        (string)($_SERVER['REMOTE_ADDR'] ?? '')
    );

    // ✅ THÊM DÒNG NÀY
    $_SESSION['quyen'] = $this->model->getQuyenByTaiKhoan($tk['MaTK']);

    if (!empty($_SESSION['must_change_password'])) {
        $_SESSION['message'] = 'Bạn đang dùng mật khẩu tạm. Hãy đổi mật khẩu trước khi tiếp tục.';
        header('Location: index.php?controller=dangnhap&action=doiMatKhauBatBuoc');
        exit;
    }

    header("Location: index.php");
    exit;
}else{
            AppLogger::security('Login failed', [
                'username' => $tenDangNhap,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            ]);
            $loi = "Sai tên đăng nhập hoặc mật khẩu";
        }
    }

    require 'views/dangnhap/login.php';
}
    public function quenMatKhau(){
        $formData = [
            'TenDangNhap' => '',
            'MaNhanVien' => '',
            'NgaySinh' => '',
            'SoDienThoai4So' => '',
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            foreach ($formData as $key => $value) {
                $formData[$key] = trim((string)($_POST[$key] ?? ''));
            }

            $matKhauMoi = (string)($_POST['MatKhauMoi'] ?? '');
            $xacNhanMatKhau = (string)($_POST['XacNhanMatKhau'] ?? '');

            if (in_array('', $formData, true)) {
                $loi = 'Vui lòng nhập đầy đủ thông tin xác thực nội bộ.';
                include 'views/dangnhap/quenmatkhau.php';
                return;
            }

            if (strlen($formData['SoDienThoai4So']) < 4) {
                $loi = 'Vui lòng nhập 4 số cuối số điện thoại đã đăng ký.';
                include 'views/dangnhap/quenmatkhau.php';
                return;
            }

            if (strlen($matKhauMoi) < 7) {
                $loi = 'Mật khẩu mới phải có ít nhất 6 ký tự.';
                include 'views/dangnhap/quenmatkhau.php';
                return;
            }

            if ($matKhauMoi !== $xacNhanMatKhau) {
                $loi = 'Xác nhận mật khẩu không khớp.';
                include 'views/dangnhap/quenmatkhau.php';
                return;
            }

            $match = $this->model->findAccountForInternalRecovery(
                $formData['TenDangNhap'],
                $formData['MaNhanVien'],
                $formData['NgaySinh'],
                $formData['SoDienThoai4So']
            );

            if (!$match) {
                AppLogger::security('Internal password recovery failed', [
                    'username' => $formData['TenDangNhap'],
                    'employee_code' => $formData['MaNhanVien'],
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                ]);
                $loi = 'Thông tin xác thực không khớp. Nếu một nhân viên có nhiều tài khoản, hãy nhập đúng tên đăng nhập của tài khoản cần khôi phục.';
                include 'views/dangnhap/quenmatkhau.php';
                return;
            }

            $account = $match['account'];
            if (password_verify($matKhauMoi, (string)($account['MatKhau'] ?? ''))) {
                $loi = 'Mật khẩu mới không được trùng với mật khẩu hiện tại.';
                include 'views/dangnhap/quenmatkhau.php';
                return;
            }

            $hash = password_hash($matKhauMoi, PASSWORD_DEFAULT);
            $ok = $this->model->updatePasswordByMaTK((int)$account['MaTK'], $hash, false);

            if ($ok) {
                AppLogger::security('Internal password recovery success', [
                    'MaTK' => (int)$account['MaTK'],
                    'username' => $account['TenDangNhap'] ?? '',
                ]);
                $_SESSION['success'] = 'Đặt lại mật khẩu thành công. Vui lòng đăng nhập bằng mật khẩu mới.';
                header('Location: index.php?controller=dangnhap&action=login');
                exit;
            }

            $loi = 'Không thể cập nhật mật khẩu vào lúc này. Vui lòng thử lại.';
        }

        include 'views/dangnhap/quenmatkhau.php';
    }

    public function doiMatKhauBatBuoc(){
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['MaTK'])) {
            header('Location: index.php?controller=dangnhap&action=login');
            exit;
        }

        $maTK = (int)($_SESSION['MaTK'] ?? 0);
        $taiKhoan = $this->model->getById($maTK);
        $mustChangePassword = !empty($_SESSION['must_change_password'])
            || !empty($_SESSION['taikhoan']['BuocDoiMatKhau'])
            || $this->model->isPasswordChangeRequired($maTK);

        if (!$taiKhoan) {
            $this->dangxuat();
        }

        if (!$mustChangePassword) {
            header('Location: index.php');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $matKhauMoi = (string)($_POST['MatKhauMoi'] ?? '');
            $xacNhanMatKhau = (string)($_POST['XacNhanMatKhau'] ?? '');

            if (strlen($matKhauMoi) < 8) {
                $loi = 'Mật khẩu mới phải có ít nhất 8 ký tự.';
                include 'views/dangnhap/doimatkhaubatbuoc.php';
                return;
            }

            if ($matKhauMoi !== $xacNhanMatKhau) {
                $loi = 'Xác nhận mật khẩu không khớp.';
                include 'views/dangnhap/doimatkhaubatbuoc.php';
                return;
            }

            if (password_verify($matKhauMoi, (string)($taiKhoan['MatKhau'] ?? ''))) {
                $loi = 'Mật khẩu mới không được trùng mật khẩu tạm hiện tại.';
                include 'views/dangnhap/doimatkhaubatbuoc.php';
                return;
            }

            $hash = password_hash($matKhauMoi, PASSWORD_DEFAULT);
            $ok = $this->model->updatePasswordByMaTK($maTK, $hash, false);

            if ($ok) {
                $taiKhoanMoi = $this->model->getById($maTK);
                $_SESSION['taikhoan'] = $taiKhoanMoi;
                $_SESSION['must_change_password'] = false;
                AppLogger::security('Forced password change completed', ['MaTK' => $maTK]);
                $_SESSION['success'] = 'Đổi mật khẩu thành công. Bạn có thể tiếp tục sử dụng hệ thống.';
                header('Location: index.php');
                exit;
            }

            $loi = 'Không thể cập nhật mật khẩu. Vui lòng thử lại.';
        }

        include 'views/dangnhap/doimatkhaubatbuoc.php';
    }

    public function datLaiMatKhau(){
        $token = (string)($_GET['token'] ?? $_POST['token'] ?? '');
        if ($token === '') {
            $loi = 'Liên kết không hợp lệ hoặc đã hết hạn.';
            include 'views/dangnhap/datlaimatkhau.php';
            return;
        }

        $tokenRow = $this->model->findValidResetToken($token);
        if (!$tokenRow) {
            $loi = 'Liên kết không hợp lệ hoặc đã hết hạn.';
            include 'views/dangnhap/datlaimatkhau.php';
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $matKhauMoi = (string)($_POST['MatKhauMoi'] ?? '');
            $xacNhan = (string)($_POST['XacNhanMatKhau'] ?? '');

            if (strlen($matKhauMoi) < 8) {
                $loi = 'Mật khẩu mới phải có ít nhất 8 ký tự.';
                include 'views/dangnhap/datlaimatkhau.php';
                return;
            }

            if ($matKhauMoi !== $xacNhan) {
                $loi = 'Xác nhận mật khẩu không khớp.';
                include 'views/dangnhap/datlaimatkhau.php';
                return;
            }

            $hash = password_hash($matKhauMoi, PASSWORD_DEFAULT);
            $ok = $this->model->updatePasswordByMaTK((int)$tokenRow['MaTK'], $hash);
            if ($ok) {
                $this->model->markResetTokenUsed((int)$tokenRow['id']);
                AppLogger::security('Password reset success', ['MaTK' => (int)$tokenRow['MaTK']]);
                $_SESSION['success'] = 'Đặt lại mật khẩu thành công. Vui lòng đăng nhập lại.';
                header('Location: index.php?controller=dangnhap&action=login');
                exit;
            }

            $loi = 'Không thể cập nhật mật khẩu. Vui lòng thử lại.';
        }

        include 'views/dangnhap/datlaimatkhau.php';
    }

    private function guiEmailReset(string $toEmail, string $resetLink): bool
    {
        $config = $this->getMailConfig();
        if (empty($config['host'])) {
            AppLogger::warning('Reset email config missing');
            return false;
        }

        if ($config['smtp_auth'] && (empty($config['username']) || empty($config['password']))) {
            AppLogger::warning('Reset email SMTP auth enabled but credentials missing');
            return false;
        }

        $mail = new PHPMailer(true);
        try {
            $mail->CharSet = 'UTF-8';
            $mail->isSMTP();
            $mail->Host = $config['host'];
            $mail->SMTPAuth = (bool)$config['smtp_auth'];
            if ($mail->SMTPAuth) {
                $mail->Username = $config['username'];
                $mail->Password = $config['password'];
            }
            if ($config['encryption'] === 'ssl') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } elseif ($config['encryption'] === 'tls') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            } else {
                $mail->SMTPSecure = false;
                $mail->SMTPAutoTLS = false;
            }
            $mail->Port = (int)$config['port'];

            $mail->setFrom($config['from_email'], $config['from_name']);
            $mail->addAddress($toEmail);
            $mail->isHTML(true);
            $mail->Subject = 'Đặt lại mật khẩu hệ thống HRM';
            $mail->Body = 'Xin chào,<br><br>Bạn vừa yêu cầu đặt lại mật khẩu. Bấm vào liên kết sau (hiệu lực 30 phút):<br><a href="'
                . htmlspecialchars($resetLink, ENT_QUOTES, 'UTF-8') . '">Đặt lại mật khẩu</a><br><br>Nếu không phải bạn yêu cầu, hãy bỏ qua email này.';

            $mail->send();
            return true;
        } catch (Exception $e) {
            AppLogger::error('Reset email send failed', ['error' => $mail->ErrorInfo]);
            return false;
        }
    }

    private function getMailConfig(): array
    {
        $env = $this->loadEnv();

        $read = function (string $key, string $default = '') use ($env): string {
            if (array_key_exists($key, $env) && $env[$key] !== '') {
                return trim((string)$env[$key]);
            }

            $value = getenv($key);
            if ($value !== false && trim((string)$value) !== '') {
                return trim((string)$value);
            }

            return $default;
        };

        $host = $read('MAIL_HOST', '');
        $port = $read('MAIL_PORT', '587');
        $username = $read('MAIL_USERNAME', '');
        $password = $read('MAIL_PASSWORD', '');
        $encryption = strtolower($read('MAIL_ENCRYPTION', 'tls'));
        $smtpAuthRaw = $read('MAIL_SMTP_AUTH', 'true');
        $smtpAuth = filter_var($smtpAuthRaw, FILTER_VALIDATE_BOOLEAN);
        $fromEmail = $read('MAIL_FROM_ADDRESS', '');
        $fromName = $read('MAIL_FROM_NAME', 'HRM System');

        // Ưu tiên from trong .env, fallback username SMTP, và luôn có địa chỉ hợp lệ cuối cùng.
        if ($fromEmail === '' || !filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
            if ($username !== '' && filter_var($username, FILTER_VALIDATE_EMAIL)) {
                $fromEmail = $username;
            } else {
                $fromEmail = 'no-reply@example.com';
            }
        }

        if (!in_array($encryption, ['tls', 'ssl', ''], true)) {
            $encryption = 'tls';
        }

        return [
            'host' => $host,
            'port' => $port,
            'username' => $username,
            'password' => $password,
            'smtp_auth' => $smtpAuth,
            'encryption' => $encryption,
            'from_email' => $fromEmail,
            'from_name' => $fromName,
        ];
    }

    private function isLocalRequestHost(string $host): bool
    {
        $host = strtolower(trim($host));
        if ($host === '') {
            return false;
        }

        // Bỏ port nếu có: localhost:8080 -> localhost
        $hostOnly = preg_replace('/:\\d+$/', '', $host) ?: $host;

        return in_array($hostOnly, ['localhost', '127.0.0.1', '::1'], true)
            || str_ends_with($hostOnly, '.local');
    }

    private function loadEnv(): array
    {
        $paths = [
            __DIR__ . '/../.env',
            __DIR__ . '/../laravel_app/.env',
        ];

        $data = [];
        foreach ($paths as $path) {
            if (!is_readable($path)) {
                continue;
            }

            $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if ($lines === false) {
                continue;
            }

            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '' || strpos($line, '#') === 0 || strpos($line, '=') === false) {
                    continue;
                }
                list($key, $value) = explode('=', $line, 2);
                $data[trim($key)] = trim($value, " \t\n\r\0\x0B\"'");
            }
        }

        return $data;
    }
    public function dangxuat(){
        $maTK = (int)($_SESSION['MaTK'] ?? 0);
        $marker = (string)($_SESSION['session_marker'] ?? '');
        if ($maTK > 0 && $marker !== '') {
            $this->model->revokeCurrentSession($maTK, $marker);
        }

        session_unset();      // xoá biến session
        session_destroy();    // huỷ session
        header("Location: index.php?controller=dangnhap&action=login");
        exit;
    }
}
