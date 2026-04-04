<?php
require_once './ketnoi.php';
require_once './models/NghiPhepModel.php';
require_once 'core/AuthMiddleware.php';
require_once 'core/RequestValidator.php';
require_once 'core/AppLogger.php';
require_once 'core/WebResponder.php';

class NghiPhepController {
    private $model;
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
        $this->model = new NghiPhepModel($conn);
    }

    /* ================= DANH SÁCH ================= */
    public function index() {
        AuthMiddleware::check($this->conn, 'xem_nghiphep');
        $quyen = $_SESSION['quyen'] ?? [];
        $keyword = trim((string)($_GET['keyword'] ?? ''));
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 10;

        $totalItems = $this->model->countNghiPhep($keyword);
        $totalPages = max(1, (int)ceil($totalItems / $perPage));
        if ($page > $totalPages) {
            $page = $totalPages;
        }

        $offset = ($page - 1) * $perPage;
        $data = $this->model->getNghiPhepPage($keyword, $perPage, $offset);
        include './views/nghiphep/index.php';
    }

    /* ================= TÌM KIẾM ================= */
    public function search() {
        AuthMiddleware::check($this->conn, 'timkiem_nghiphep');
        $quyen = $_SESSION['quyen'] ?? [];
        $keyword = trim((string)($_POST['keyword'] ?? ''));
        header('Location: index.php?controller=nghiphep&action=index&keyword=' . urlencode($keyword));
        exit;
    }

    /* ================= FORM THÊM ================= */
    public function them() {
        AuthMiddleware::check($this->conn, 'them_nghiphep');
        $quyen = $_SESSION['quyen'] ?? [];
        $nhanvien = $this->model->getAllNhanVien();
        include './views/nghiphep/them.php';
    }

    /* ================= LƯU THÊM ================= */
    public function luu() {
        AuthMiddleware::check($this->conn, 'them_nghiphep');
        $quyen = $_SESSION['quyen'] ?? [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $validator = new RequestValidator($_POST);
            $manv   = $validator->requiredInt('MaNV', 'Nhân viên', 1);
            $tungay = $validator->requiredDate('TuNgay', 'Từ ngày');
            $denngay = $validator->requiredDate('DenNgay', 'Đến ngày');
            $lydo   = $validator->optionalString('LyDo', 1000);
            $loai   = $validator->in('LoaiNghi', 'Loại nghỉ', ['Nghỉ phép', 'Nghỉ không phép', 'Nghỉ ốm']);

            if (!$validator->isValid()) {
                AppLogger::warning('Validation failed in NghiPhepController::luu', ['errors' => $validator->allErrors()]);
                WebResponder::backWithMessage($validator->firstError(), 'error', 'index.php?controller=nghiphep&action=them');
            }

            if (strtotime($denngay) < strtotime($tungay)) {
                WebResponder::backWithMessage('Đến ngày phải lớn hơn hoặc bằng Từ ngày.', 'error', 'index.php?controller=nghiphep&action=them');
            }

            // Tính số ngày nghỉ
            $start = new DateTime($tungay);
            $end   = new DateTime($denngay);
            $songay = $start->diff($end)->days + 1;

            if ($this->model->insertNghiPhep(
                $manv, $tungay, $denngay, $songay, $lydo, $loai
            )) {
                WebResponder::redirectWithMessage('index.php?controller=nghiphep&action=index', 'Gửi đơn nghỉ phép thành công!', 'success');
            } else {
                WebResponder::backWithMessage('Lỗi khi gửi đơn!', 'error', 'index.php?controller=nghiphep&action=them');
            }
        }
    }

    /* ================= FORM SỬA ================= */
    public function sua() {
        AuthMiddleware::check($this->conn, 'sua_nghiphep');
        $quyen = $_SESSION['quyen'] ?? [];
        $id = $_GET['id'] ?? '';
        if (!$id) {
            $_SESSION['error'] = 'Không tìm thấy đơn';
            header('Location: index.php?controller=nghiphep&action=index');
            exit;
        }

        $result = $this->model->getNghiPhepById($id);
        if ($result->num_rows == 0) {
            $_SESSION['error'] = 'Không tìm thấy đơn';
            header('Location: index.php?controller=nghiphep&action=index');
            exit;
        }

        $row = $result->fetch_assoc();
        $nhanvien = $this->model->getAllNhanVien();

        include './views/nghiphep/sua.php';
    }

    /* ================= LƯU SỬA ================= */
    public function luuSua() {
        AuthMiddleware::check($this->conn, 'sua_nghiphep');
        $quyen = $_SESSION['quyen'] ?? [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $validator = new RequestValidator($_POST);
            $id      = $validator->requiredInt('MaNP', 'Mã nghỉ phép', 1);
            $manv    = $validator->requiredInt('MaNV', 'Nhân viên', 1);
            $tungay  = $validator->requiredDate('TuNgay', 'Từ ngày');
            $denngay = $validator->requiredDate('DenNgay', 'Đến ngày');
            $lydo    = $validator->optionalString('LyDo', 1000);
            $loai    = $validator->in('LoaiNghi', 'Loại nghỉ', ['Nghỉ phép', 'Nghỉ không phép', 'Nghỉ ốm']);

            if (!$validator->isValid()) {
                AppLogger::warning('Validation failed in NghiPhepController::luuSua', ['errors' => $validator->allErrors()]);
                WebResponder::backWithMessage($validator->firstError(), 'error', 'index.php?controller=nghiphep&action=index');
            }

            if (strtotime($denngay) < strtotime($tungay)) {
                WebResponder::backWithMessage('Đến ngày phải lớn hơn hoặc bằng Từ ngày.', 'error', 'index.php?controller=nghiphep&action=index');
            }

            $start = new DateTime($tungay);
            $end   = new DateTime($denngay);
            $songay = $start->diff($end)->days + 1;

            if ($this->model->updateNghiPhep(
                $id, $manv, $tungay, $denngay, $songay, $lydo, $loai
            )) {
                WebResponder::redirectWithMessage('index.php?controller=nghiphep&action=index', 'Cập nhật thành công!', 'success');
            } else {
                WebResponder::backWithMessage('Lỗi cập nhật!', 'error', 'index.php?controller=nghiphep&action=index');
            }
        }
    }

    /* ================= DUYỆT ================= */
    public function duyet() {
        AuthMiddleware::check($this->conn, 'duyet_nghiphep');
        $quyen = $_SESSION['quyen'] ?? [];
        $id = (int)($_GET['id'] ?? 0);
        if ($id > 0 && $this->model->duyet($id)) {
            WebResponder::redirectWithMessage('index.php?controller=nghiphep&action=index', 'Đã duyệt đơn!', 'success');
        }
        WebResponder::redirectWithMessage('index.php?controller=nghiphep&action=index', 'Không thể duyệt đơn.', 'error');
    }

    /* ================= TỪ CHỐI ================= */
    public function tuchoi() {
        AuthMiddleware::check($this->conn, 'tuchoi_nghiphep');
        $quyen = $_SESSION['quyen'] ?? [];
        $id = (int)($_GET['id'] ?? 0);
        if ($id > 0 && $this->model->tuchoi($id)) {
            WebResponder::redirectWithMessage('index.php?controller=nghiphep&action=index', 'Đã từ chối đơn!', 'success');
        }
        WebResponder::redirectWithMessage('index.php?controller=nghiphep&action=index', 'Không thể từ chối đơn.', 'error');
    }

    /* ================= XÓA ================= */
    public function xoa() {
        AuthMiddleware::check($this->conn, 'xoa_nghiphep');
        $quyen = $_SESSION['quyen'] ?? [];
        $id = (int)($_GET['id'] ?? 0);
        if ($id > 0 && $this->model->xoa($id)) {
            WebResponder::redirectWithMessage('index.php?controller=nghiphep&action=index', 'Đã xóa đơn!', 'success');
        }
        WebResponder::redirectWithMessage('index.php?controller=nghiphep&action=index', 'Không thể xóa đơn.', 'error');
    }
}
?>
