<?php
require_once 'models/PhanCongModel.php';
require_once 'core/AuthMiddleware.php';
require_once 'core/RequestValidator.php';
require_once 'core/WebResponder.php';
require_once 'core/AppLogger.php';

class PhanCongController {
    private $model;
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
        $this->model = new PhanCongModel($conn);
    }

    /* ================== LIST ================== */
    public function index() {
        AuthMiddleware::check($this->conn, 'xem_phancong');
        $quyen = $_SESSION['quyen'] ?? [];
        $keyword = trim((string)($_GET['keyword'] ?? ''));
        $currentMaNV = $this->isEmployeeRole() ? $this->currentEmployeeId() : null;
        $phancongs = $this->model->getAll($keyword, $currentMaNV);

        require 'views/phancong/index.php';
    }

    /* ================== ADD ================== */
    public function add() {
         AuthMiddleware::check($this->conn, 'them_phancong');
         $quyen = $_SESSION['quyen'] ?? [];
        $nhanviens = $this->model->getNhanVien();
        $phongbans = $this->model->getPhongBan();
        $chucvus   = $this->model->getChucVu();
        require 'views/phancong/add.php';
    }

    public function store() {
        AuthMiddleware::check($this->conn, 'them_phancong');
        $quyen = $_SESSION['quyen'] ?? [];
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $validator = new RequestValidator($_POST);

        $payload = [
            'MaNV' => $validator->requiredInt('MaNV', 'Nhân viên', 1),
            'MaPB' => $validator->requiredInt('MaPB', 'Phòng ban', 1),
            'MaCV' => $validator->requiredInt('MaCV', 'Chức vụ', 1),
            'NgayBatDau' => $validator->requiredDate('NgayBatDau', 'Ngày bắt đầu'),
            'LyDoThayDoi' => $validator->optionalString('LyDoThayDoi', 1000),
            'LoaiDieuChuyen' => $validator->requiredString('LoaiDieuChuyen', 'Loại điều chuyển', 2, 100),
        ];

        if (!$validator->isValid()) {
            AppLogger::warning('Validation failed in PhanCongController::store', ['errors' => $validator->allErrors()]);
            WebResponder::backWithMessage($validator->firstError(), 'error', 'index.php?controller=phancong&action=add');
        }

        $maNV  = $payload['MaNV'];
        $ngayBD = $payload['NgayBatDau'];

        // 🔴 CHECK TRÙNG THỜI GIAN
        if ($this->model->hasActiveAssignment($maNV, $ngayBD)) {
            WebResponder::backWithMessage('Nhân viên đang có phân công hiệu lực trong thời gian này.', 'error', 'index.php?controller=phancong&action=add');
        }

        // ✔ Không trùng mới cho thêm
        $this->model->insertWithTransition($payload);

        WebResponder::redirectWithMessage('index.php?controller=phancong&action=index', 'Thêm phân công thành công.', 'success');
    }
}

    /* ================== EDIT ================== */
    public function edit() {
         AuthMiddleware::check($this->conn, 'sua_phancong');
         $quyen = $_SESSION['quyen'] ?? [];
        $maQT = (int)($_GET['id'] ?? 0);
        $phancong = $this->model->getById($maQT);

        $phongbans = $this->model->getPhongBan();
        $chucvus   = $this->model->getChucVu();

        require 'views/phancong/edit.php';
    }

    public function update() {
        AuthMiddleware::check($this->conn, 'sua_phancong');
        $quyen = $_SESSION['quyen'] ?? [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $validator = new RequestValidator($_POST);
            $payload = [
                'MaQT' => $validator->requiredInt('MaQT', 'Mã quá trình', 1),
                'MaPB' => $validator->requiredInt('MaPB', 'Phòng ban', 1),
                'MaCV' => $validator->requiredInt('MaCV', 'Chức vụ', 1),
                'NgayBatDau' => $validator->requiredDate('NgayBatDau', 'Ngày bắt đầu'),
                'NgayKetThuc' => $validator->optionalDate('NgayKetThuc'),
                'LyDoThayDoi' => $validator->optionalString('LyDoThayDoi', 1000),
                'LoaiDieuChuyen' => $validator->requiredString('LoaiDieuChuyen', 'Loại điều chuyển', 2, 100),
            ];

            if (!$validator->isValid()) {
                AppLogger::warning('Validation failed in PhanCongController::update', ['errors' => $validator->allErrors()]);
                WebResponder::backWithMessage($validator->firstError(), 'error', 'index.php?controller=phancong&action=index');
            }

            if (!empty($payload['NgayKetThuc']) && strtotime($payload['NgayKetThuc']) < strtotime($payload['NgayBatDau'])) {
                WebResponder::backWithMessage('Ngày kết thúc phải lớn hơn hoặc bằng ngày bắt đầu.', 'error', 'index.php?controller=phancong&action=index');
            }

            $this->model->update($payload);
            WebResponder::redirectWithMessage('index.php?controller=phancong&action=index', 'Cập nhật phân công thành công.', 'success');
        }
    }

    /* ================== DELETE ================== */
    public function delete() {
        AuthMiddleware::check($this->conn, 'xoa_phancong');
        $quyen = $_SESSION['quyen'] ?? [];
        $maQT = (int)($_GET['id'] ?? 0);
        if ($maQT <= 0) {
            WebResponder::redirectWithMessage('index.php?controller=phancong&action=index', 'Thiếu mã phân công.', 'error');
        }
        $this->model->delete($maQT);
        WebResponder::redirectWithMessage('index.php?controller=phancong&action=index', 'Xóa phân công thành công.', 'success');
    }
    public function history() {
        AuthMiddleware::check($this->conn, 'xoa_phancong');
        $quyen = $_SESSION['quyen'] ?? [];
    $maNV = (int)($_GET['manv'] ?? 0);

    $lichsu = $this->model->getCareerPath($maNV);

    require 'views/phancong/history.php';
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
