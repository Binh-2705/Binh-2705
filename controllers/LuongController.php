<?php
require_once 'models/LuongModel.php';
require_once 'core/AuthMiddleware.php';
require_once 'core/RequestValidator.php';
require_once 'core/WebResponder.php';
require_once 'core/AppLogger.php';
class LuongController {
    private $model;
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
        $this->model = new LuongModel($conn);
    }

    public function index() {
        AuthMiddleware::check($this->conn, 'xem_luong');
        $quyen = $_SESSION['quyen'] ?? [];
        $currentMaNV = $this->isEmployeeRole() ? $this->currentEmployeeId() : null;
        $luong = $this->model->getAll($currentMaNV);
        include './views/luong/index.php';
    }

    /* ================= TÍNH LƯƠNG THÁNG ================= */
   public function tinhLuongThang() {
        AuthMiddleware::check($this->conn, 'tinh_luong_thang');
$quyen = $_SESSION['quyen'] ?? [];
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $validator = new RequestValidator($_POST);

        $thang = $validator->requiredInt('thang', 'Tháng', 1);
        $nam   = $validator->requiredInt('nam', 'Năm', 2000);

        if ($thang < 1 || $thang > 12) {
            WebResponder::backWithMessage('Tháng không hợp lệ.', 'error', 'index.php?controller=luong&action=index');
        }

        if (!$validator->isValid()) {
            AppLogger::warning('Validation failed in LuongController::tinhLuongThang', ['errors' => $validator->allErrors()]);
            WebResponder::backWithMessage($validator->firstError(), 'error', 'index.php?controller=luong&action=index');
        }

        try {
            // CHỈ GỌI 1 LẦN DUY NHẤT
            $this->model->tinhLuongToanBo($thang,$nam);
            WebResponder::redirectWithMessage('index.php?controller=luong&action=index', 'Đã tính lương toàn bộ nhân viên tháng '.$thang.'/'.$nam.'.', 'success');

        } catch (Exception $e) {
            AppLogger::error('tinhLuongThang failed', ['error' => $e->getMessage()]);
            WebResponder::backWithMessage('Không thể tính lương. Vui lòng kiểm tra dữ liệu chấm công.', 'error', 'index.php?controller=luong&action=index');
        }
    }
}
    public function chotLuong() {
        AuthMiddleware::check($this->conn, 'chot_luong');
        $quyen = $_SESSION['quyen'] ?? [];
    $id = (int)($_GET['id'] ?? 0);
    if ($id <= 0) {
        WebResponder::redirectWithMessage('index.php?controller=luong&action=index', 'Thiếu mã lương.', 'error');
    }
    $this->model->updateTrangThai($id, 'Đã chốt');
    WebResponder::redirectWithMessage('index.php?controller=luong&action=index', 'Chốt lương thành công.', 'success');
}

public function moChot() {
    AuthMiddleware::check($this->conn, 'mo_chot_luong');
    $quyen = $_SESSION['quyen'] ?? [];
    $id = (int)($_GET['id'] ?? 0);
    if ($id <= 0) {
        WebResponder::redirectWithMessage('index.php?controller=luong&action=index', 'Thiếu mã lương.', 'error');
    }
    $this->model->updateTrangThai($id, 'Chưa chốt');
    WebResponder::redirectWithMessage('index.php?controller=luong&action=index', 'Mở chốt lương thành công.', 'success');
}

    private function currentEmployeeId(): ?int {
        $account = $_SESSION['taikhoan'] ?? [];
        $maNVRef = (int)($account['MaNVRef'] ?? 0);
        if ($maNVRef > 0) return $maNVRef;
        $maNVRaw = (string)($account['MaNV'] ?? '');
        $digits = preg_replace('/\D+/', '', $maNVRaw);
        if ($digits === '') return null;
        $maNV = (int)$digits;
        return $maNV > 0 ? $maNV : null;
    }

    private function isEmployeeRole(): bool {
        $account = $_SESSION['taikhoan'] ?? [];
        return strtolower(trim((string)($account['VaiTro'] ?? ''))) === 'nhanvien';
    }
}