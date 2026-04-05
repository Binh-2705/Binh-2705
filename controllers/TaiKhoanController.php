<?php
require_once 'models/TaiKhoanModel.php';
require_once 'core/AuthMiddleware.php';
require_once 'core/AppLogger.php';
class TaiKhoanController {
    private $model;
    private $conn;

    public function __construct($conn){
        $this->conn = $conn;
        $this->model = new TaiKhoanModel($conn);
    }

    // DANH SÁCH + TÌM KIẾM
    public function index(){
        AuthMiddleware::check($this->conn, 'xem_taikhoan');
        $quyen = $_SESSION['quyen'] ?? [];
        $key = $_GET['key'] ?? '';
        $tempPasswordNotice = $_SESSION['temp_password_notice'] ?? null;
        unset($_SESSION['temp_password_notice']);
        $data = $this->model->getAll($key);
        require 'views/taikhoan/index.php';
    }

    private function normalizeEmployeeCodeInput(string $value): string {
        return strtoupper(trim($value));
    }

    private function isValidEmployeeCode(string $value): bool {
        if ($value === '') {
            return true;
        }

        return (bool)preg_match('/^[A-Z0-9_-]{1,10}$/', $value);
    }

    // THÊM
    public function them(){
        AuthMiddleware::check($this->conn, 'them_taikhoan');
        $quyen = $_SESSION['quyen'] ?? [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST'){
            $user = trim((string)($_POST['user'] ?? ''));
            if ($user === '' || !preg_match('/^[A-Za-z0-9_.]{4,50}$/', $user)) {
                $_SESSION['error'] = 'Tên đăng nhập chỉ gồm chữ, số, dấu chấm, gạch dưới và từ 4-50 ký tự.';
                header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'index.php?controller=taikhoan&action=them'));
                exit;
            }

            $pass = trim((string)($_POST['pass'] ?? ''));
            if (mb_strlen($pass) < 6) {
                $_SESSION['error'] = 'Mật khẩu phải có ít nhất 6 ký tự.';
                header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'index.php?controller=taikhoan&action=them'));
                exit;
            }

            $vaitro = trim((string)($_POST['vaitro'] ?? ''));
            if (!in_array($vaitro, ['Admin', 'NhanVien', 'HR', 'KeToan', 'QuanLy'], true)) {
                $_SESSION['error'] = 'Vai trò không hợp lệ.';
                header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'index.php?controller=taikhoan&action=them'));
                exit;
            }

            $manv = $this->normalizeEmployeeCodeInput((string)($_POST['manv'] ?? ''));
            if (!$this->isValidEmployeeCode($manv)) {
                $_SESSION['error'] = 'Mã nhân viên chỉ gồm chữ, số, gạch dưới, gạch ngang (tối đa 10 ký tự).';
                header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'index.php?controller=taikhoan&action=them'));
                exit;
            }

            if ($this->model->insert($user, $pass, $vaitro, $manv)) {
                $_SESSION['success'] = 'Thêm tài khoản thành công.';
                header("Location: index.php?controller=taikhoan");
                exit;
            }

            AppLogger::warning('TaiKhoanController::them failed', ['user' => $user]);
            $_SESSION['error'] = 'Không thể thêm tài khoản.';
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'index.php?controller=taikhoan&action=them'));
            exit;
        }
        require 'views/taikhoan/them.php';
    }

    // SỬA
    public function sua(){
            AuthMiddleware::check($this->conn, 'sua_taikhoan');
            $quyen = $_SESSION['quyen'] ?? [];
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            $_SESSION['error'] = 'Thiếu mã tài khoản.';
            header('Location: index.php?controller=taikhoan');
            exit;
        }

        $tk = $this->model->getById($id);
        if (!$tk) {
            $_SESSION['error'] = 'Không tìm thấy tài khoản.';
            header('Location: index.php?controller=taikhoan');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST'){
            $vaitro = trim((string)($_POST['vaitro'] ?? ''));
            if (!in_array($vaitro, ['Admin', 'NhanVien', 'HR', 'KeToan', 'QuanLy'], true)) {
                $_SESSION['error'] = 'Vai trò không hợp lệ.';
                header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'index.php?controller=taikhoan&action=sua&id=' . $id));
                exit;
            }

            $manv = $this->normalizeEmployeeCodeInput((string)($_POST['manv'] ?? ''));
            if (!$this->isValidEmployeeCode($manv)) {
                $_SESSION['error'] = 'Mã nhân viên chỉ gồm chữ, số, gạch dưới, gạch ngang (tối đa 10 ký tự).';
                header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'index.php?controller=taikhoan&action=sua&id=' . $id));
                exit;
            }

            if ($this->model->update($id, $vaitro, $manv)) {
                $_SESSION['success'] = 'Cập nhật tài khoản thành công.';
                header("Location: index.php?controller=taikhoan");
                exit;
            }

            $_SESSION['error'] = 'Không thể cập nhật tài khoản.';
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'index.php?controller=taikhoan&action=sua&id=' . $id));
            exit;
        }
        require 'views/taikhoan/sua.php';
    }

    // XÓA
    public function xoa(){
        AuthMiddleware::check($this->conn, 'xoa_taikhoan');
        $quyen = $_SESSION['quyen'] ?? [];
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            $_SESSION['error'] = 'Thiếu mã tài khoản.';
            header("Location: index.php?controller=taikhoan");
            exit;
        }

        $this->model->delete($id);
        $_SESSION['success'] = 'Đã xóa tài khoản.';
        header("Location: index.php?controller=taikhoan");
        exit;
    }

    public function resetTamThoi(){
        AuthMiddleware::check($this->conn, 'sua_taikhoan');
        $quyen = $_SESSION['quyen'] ?? [];

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = 'Yêu cầu không hợp lệ.';
            header('Location: index.php?controller=taikhoan');
            exit;
        }

        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            $_SESSION['error'] = 'Thiếu mã tài khoản.';
            header('Location: index.php?controller=taikhoan');
            exit;
        }

        $tk = $this->model->getById($id);
        if (!$tk) {
            $_SESSION['error'] = 'Không tìm thấy tài khoản cần cấp lại mật khẩu.';
            header('Location: index.php?controller=taikhoan');
            exit;
        }

        $matKhauTam = $this->taoMatKhauTam();
        $hash = password_hash($matKhauTam, PASSWORD_DEFAULT);

        if ($this->model->updatePasswordByMaTK($id, $hash, true)) {
            AppLogger::security('Admin reset temporary password', [
                'target_MaTK' => $id,
                'target_username' => $tk['TenDangNhap'] ?? '',
                'by_MaTK' => (int)($_SESSION['MaTK'] ?? 0),
            ]);

            if ((int)($_SESSION['MaTK'] ?? 0) === $id) {
                $_SESSION['must_change_password'] = true;
                $_SESSION['taikhoan']['BuocDoiMatKhau'] = 1;
            }

            $_SESSION['temp_password_notice'] = [
                'username' => $tk['TenDangNhap'] ?? ('#' . $id),
                'employee_code' => trim((string)($tk['MaNV'] ?? '')),
                'password' => $matKhauTam,
                'issued_at' => date('d/m/Y H:i'),
            ];
            $_SESSION['message'] = 'Đã cấp mật khẩu tạm. Hãy chuyển thông tin này cho người dùng qua kênh nội bộ an toàn.';
        } else {
            $_SESSION['error'] = 'Không thể cấp lại mật khẩu tạm.';
        }

        header('Location: index.php?controller=taikhoan');
        exit;
    }

    private function taoMatKhauTam(): string {
        return 'HRM' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
    }

}
