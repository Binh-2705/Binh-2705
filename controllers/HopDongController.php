<?php
// controllers/HopDongController.php

require_once 'models/HopDongModel.php';
require_once 'core/AuthMiddleware.php';
require_once 'core/RequestValidator.php';
require_once 'core/AppLogger.php';
require_once 'core/WebResponder.php';

class HopDongController {
    private $model;
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
        $this->model = new HopDongModel($conn);
    }

    /* ================== DANH SÁCH ================== */
    public function index() {
            AuthMiddleware::check($this->conn, 'xem_hopdong');
            $quyen = $_SESSION['quyen'] ?? [];

        $filters = [
            'keyword'   => $_GET['keyword']   ?? '',
            'loaiHD'    => $_GET['loaiHD']    ?? '',
            'trangThai' => $_GET['trangThai'] ?? '',
            'tuNgay'    => $_GET['tuNgay']    ?? '',
            'denNgay'   => $_GET['denNgay']   ?? '',
        ];

        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 10;
        $totalItems = $this->model->countHopDong($filters);
        $totalPages = max(1, (int)ceil($totalItems / $perPage));
        if ($page > $totalPages) {
            $page = $totalPages;
        }

        $offset = ($page - 1) * $perPage;
        $result = $this->model->getHopDongPage($filters, $perPage, $offset);
        include 'views/hopdong/index.php';
    }
public function lichsu_luong()
{
    AuthMiddleware::check($this->conn, 'xem_lich_su_luong');
    $quyen = $_SESSION['quyen'] ?? [];
    if (!isset($_GET['MaHopDong'])) {
        $_SESSION['error'] = "Thiếu mã hợp đồng";
        header("Location: index.php?controller=hopdong&action=index");
        exit;
    }

    $maHD = (int)$_GET['MaHopDong'];

    $hopDong = $this->model->getHopDongById($maHD);
    $lichSu  = $this->model->getLichSuLuongByHopDong($maHD);

    require 'views/hopdong/lichsu_luong.php';
}



    /* ================== THÊM ================== */
    public function them() {
AuthMiddleware::check($this->conn, 'them_hopdong');
$quyen = $_SESSION['quyen'] ?? [];
        $nhanviens = $this->model->getAllNhanVienForSelect();
        $bacluongs = $this->model->getAllBacLuongForSelect();
        include 'views/hopdong/them.php';
    }

    public function luuThem() {
        AuthMiddleware::check($this->conn, 'them_hopdong');
        $quyen = $_SESSION['quyen'] ?? [];
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $validator = new RequestValidator($_POST);
        $payload = [
            'SoHopDong'   => $validator->requiredString('SoHopDong', 'Số hợp đồng', 3, 50),
            'MaNV'        => $validator->requiredInt('MaNV', 'Nhân viên', 1),
            'LoaiHopDong' => $validator->in('LoaiHopDong', 'Loại hợp đồng', ['Thử việc', 'Xác định thời hạn', 'Không xác định thời hạn']),
            'NgayKy'      => $validator->requiredDate('NgayKy', 'Ngày ký'),
            'NgayBatDau'  => $validator->requiredDate('NgayBatDau', 'Ngày bắt đầu'),
            'NgayKetThuc' => $validator->optionalDate('NgayKetThuc'),
            'MaBac'       => $validator->requiredInt('MaBac', 'Bậc lương', 1),
            'GhiChu'      => $validator->optionalString('GhiChu', 1000),
            'TrangThai'   => 'con',
        ];

        if (!$validator->isValid()) {
            $_SESSION['error'] = $validator->firstError();
            AppLogger::warning('Validation failed in HopDongController::luuThem', ['errors' => $validator->allErrors()]);
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'index.php?controller=hopdong&action=them'));
            exit;
        }

        if ($this->model->checkSoHopDongExists($payload['SoHopDong'])) {
            $_SESSION['error'] = 'Số hợp đồng đã tồn tại';
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'index.php?controller=hopdong&action=them'));
            exit;
        }

        if (!empty($payload['NgayKetThuc']) && strtotime($payload['NgayKetThuc']) < strtotime($payload['NgayBatDau'])) {
            $_SESSION['error'] = 'Ngày kết thúc không được trước ngày bắt đầu';
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'index.php?controller=hopdong&action=them'));
            exit;
        }

        if ($this->model->insertHopDong($payload)) {
            $_SESSION['success'] = 'Tạo hợp đồng thành công!';
            header("Location: index.php?controller=hopdong&action=index");
            exit;
        }

        $_SESSION['error'] = 'Không thể lưu hợp đồng';
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'index.php?controller=hopdong&action=them'));
        exit;
    }

    /* ================== SỬA ================== */
    public function sua() {
        AuthMiddleware::check($this->conn, 'sua_hopdong');
        $quyen = $_SESSION['quyen'] ?? [];
        $maHD = (int)($_GET['MaHopDong'] ?? 0);
        $hopdong = $this->model->getHopDongById($maHD);

        if (!$hopdong) {
            WebResponder::redirectWithMessage('index.php?controller=hopdong&action=index', 'Hợp đồng không tồn tại.', 'error');
        }

        $nhanviens = $this->model->getAllNhanVienForSelect();
        $bacluongs = $this->model->getAllBacLuongForSelect();
        include 'views/hopdong/sua.php';
    }

    public function luuSua() {
        AuthMiddleware::check($this->conn, 'sua_hopdong');
        $quyen = $_SESSION['quyen'] ?? [];
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $validator = new RequestValidator($_POST);
        $payload = [
            'MaHopDong'   => $validator->requiredInt('MaHopDong', 'Mã hợp đồng', 1),
            'MaNV'        => $validator->requiredInt('MaNV', 'Nhân viên', 1),
            'MaBac'       => $validator->requiredInt('MaBac', 'Bậc lương', 1),
            'LoaiHopDong' => $validator->in('LoaiHopDong', 'Loại hợp đồng', ['Thử việc', 'Xác định thời hạn', 'Không xác định thời hạn']),
            'NgayKy'      => $validator->requiredDate('NgayKy', 'Ngày ký'),
            'NgayBatDau'  => $validator->requiredDate('NgayBatDau', 'Ngày bắt đầu'),
            'NgayKetThuc' => $validator->optionalDate('NgayKetThuc'),
            'GhiChu'      => $validator->optionalString('GhiChu', 1000),
        ];

        if (!$validator->isValid()) {
            $_SESSION['error'] = $validator->firstError();
            AppLogger::warning('Validation failed in HopDongController::luuSua', ['errors' => $validator->allErrors()]);
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'index.php?controller=hopdong&action=index'));
            exit;
        }

        if (!empty($payload['NgayKetThuc']) && strtotime($payload['NgayKetThuc']) < strtotime($payload['NgayBatDau'])) {
            $_SESSION['error'] = 'Ngày kết thúc phải sau ngày bắt đầu';
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'index.php?controller=hopdong&action=index'));
            exit;
        }

        $luongCu = $this->model->getLuongByHopDong($payload['MaHopDong']);
        $luongMoi = $this->model->getLuongByBac($payload['MaBac']);

// nếu có thay đổi lương thì ghi lịch sử
if ($luongCu != $luongMoi) {

    $this->model->themLichSuLuong([
        'MaHopDong'  => $payload['MaHopDong'],
        'LuongCu'    => $luongCu,
        'LuongMoi'   => $luongMoi,
        'NgayApDung' => date('Y-m-d'),
        'LyDo'       => 'Điều chỉnh bậc lương'
    ]);
}


        if ($this->model->updateHopDong($payload)) {
            $_SESSION['success'] = 'Cập nhật hợp đồng thành công!';
            header("Location: index.php?controller=hopdong&action=index");
            exit;
        }

        $_SESSION['error'] = 'Không thể cập nhật hợp đồng';
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'index.php?controller=hopdong&action=index'));
        exit;
    }

    /* ================== XÓA ================== */
    public function xoa() {
        AuthMiddleware::check($this->conn, 'xoa_hopdong');
        $quyen = $_SESSION['quyen'] ?? [];
        $maHD = (int)($_GET['MaHopDong'] ?? 0);

        if ($this->model->deleteHopDong($maHD)) {
            WebResponder::redirectWithMessage('index.php?controller=hopdong&action=index', 'Đã xóa hợp đồng.', 'success');
        }

        WebResponder::backWithMessage('Không thể xóa hợp đồng.', 'error', 'index.php?controller=hopdong&action=index');
    }

    /* ================== GIA HẠN ================== */
    public function giaHan() {
        AuthMiddleware::check($this->conn, 'giahan_hopdong');
        $quyen = $_SESSION['quyen'] ?? [];
        $maHD = (int)($_GET['MaHopDong'] ?? 0);
        $hopdong = $this->model->getHopDongById($maHD);

        if (!$hopdong) {
            WebResponder::backWithMessage('Hợp đồng không tồn tại.', 'error', 'index.php?controller=hopdong&action=index');
        }

        $nhanviens = $this->model->getAllNhanVienForSelect();
        $bacluongs = $this->model->getAllBacLuongForSelect();
        include 'views/hopdong/giahan.php';
    }

    public function luuGiaHan() {
        AuthMiddleware::check($this->conn, 'giahan_hopdong');
        $quyen = $_SESSION['quyen'] ?? [];
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $validator = new RequestValidator($_POST);
        $payload = [
            'HopDongGoc'  => $validator->requiredInt('HopDongGoc', 'Hợp đồng gốc', 1),
            'SoHopDong'   => $validator->requiredString('SoHopDong', 'Số hợp đồng', 3, 50),
            'MaNV'        => $validator->requiredInt('MaNV', 'Nhân viên', 1),
            'MaBac'       => $validator->requiredInt('MaBac', 'Bậc lương', 1),
            'LoaiHopDong' => $validator->in('LoaiHopDong', 'Loại hợp đồng', ['Thử việc', 'Xác định thời hạn', 'Không xác định thời hạn']),
            'NgayBatDau'  => $validator->requiredDate('NgayBatDau', 'Ngày bắt đầu'),
            'NgayKetThuc' => $validator->optionalDate('NgayKetThuc'),
            'GhiChu'      => $validator->optionalString('GhiChu', 1000),
        ];

        if (!$validator->isValid()) {
            $_SESSION['error'] = $validator->firstError();
            AppLogger::warning('Validation failed in HopDongController::luuGiaHan', ['errors' => $validator->allErrors()]);
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'index.php?controller=hopdong&action=index'));
            exit;
        }

        if ($this->model->checkSoHopDongExists($payload['SoHopDong'])) {
            $_SESSION['error'] = 'Số hợp đồng đã tồn tại';
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'index.php?controller=hopdong&action=index'));
            exit;
        }

        if (!empty($payload['NgayKetThuc']) && strtotime($payload['NgayKetThuc']) < strtotime($payload['NgayBatDau'])) {
            $_SESSION['error'] = 'Ngày kết thúc không hợp lệ';
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'index.php?controller=hopdong&action=index'));
            exit;
        }

        // 1️⃣ Chấm dứt hợp đồng gốc
        $this->model->updateTrangThaiChamdut($payload['HopDongGoc']);

        // 2️⃣ Tạo hợp đồng mới
        if ($this->model->insertHopDongGiaHan($payload)) {
            $_SESSION['success'] = 'Gia hạn hợp đồng thành công';
            header("Location: index.php?controller=hopdong&action=index");
            exit;
        }

        $_SESSION['error'] = 'Gia hạn hợp đồng thất bại';
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'index.php?controller=hopdong&action=index'));
        exit;
    }

    /* ================== CHẤM DỨT ================== */
  public function chamdut() {
    AuthMiddleware::check($this->conn, 'chamdut_hopdong');
    $quyen = $_SESSION['quyen'] ?? [];
    $maHD = (int)($_GET['MaHopDong'] ?? 0);
    if ($maHD <= 0) {
        WebResponder::redirectWithMessage('index.php?controller=hopdong&action=index', 'Thiếu mã hợp đồng.', 'error');
    }

    $hopdong = $this->model->getHopDongById($maHD);

    // ❌ Không tồn tại
    if (!$hopdong) {
        WebResponder::redirectWithMessage('index.php?controller=hopdong&action=index', 'Hợp đồng không tồn tại.', 'error');
    }

    // 🔒 ĐÃ HẾT HIỆU LỰC → KHÔNG CHO CHẤM DỨT LẠI
    if ($hopdong['TrangThai'] === 'Hết hiệu lực') {
        WebResponder::redirectWithMessage('index.php?controller=hopdong&action=index', 'Hợp đồng đã chấm dứt trước đó.', 'warning');
    }

    // ✅ CHẤM DỨT
    if ($this->model->updateTrangThaiChamdut($maHD)) {
        WebResponder::redirectWithMessage('index.php?controller=hopdong&action=index', 'Đã chấm dứt hợp đồng.', 'success');
    }

    WebResponder::backWithMessage('Không thể chấm dứt hợp đồng.', 'error', 'index.php?controller=hopdong&action=index');
}



}
