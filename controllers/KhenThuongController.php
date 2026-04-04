<?php
require_once 'models/KhenThuongModel.php';
require_once 'core/AuthMiddleware.php';
require_once 'core/RequestValidator.php';
require_once 'core/WebResponder.php';
require_once 'core/AppLogger.php';
class KhenThuongController {
    private $model;
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
        $this->model = new KhenThuongModel($conn);
    }

    /* ================== DANH SÁCH ================== */
   public function index() {
            AuthMiddleware::check($this->conn, 'xem_khenthuong');
            $quyen = $_SESSION['quyen'] ?? [];

    $keyword = $_GET['search'] ?? '';
    $loai    = $_GET['loai'] ?? '';
    $thang   = $_GET['thang'] ?? '';
$tong   = $this->model->getTongTien($keyword, $loai, $thang);
    $result = $this->model->getAll($keyword, $loai, $thang);

    include 'views/khenthuong/index.php';
}

    /* ================== FORM THÊM ================== */
    public function them() {
        AuthMiddleware::check($this->conn, 'them_khenthuong');
        $quyen = $_SESSION['quyen'] ?? [];
        $nhanviens = $this->model->getNhanVien();
        $loais = $this->model->getLoai();

        include 'views/khenthuong/them.php';
    }

    /* ================== LƯU THÊM ================== */
    public function luuThem() {
        AuthMiddleware::check($this->conn, 'them_khenthuong');
        $quyen = $_SESSION['quyen'] ?? [];

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $validator = new RequestValidator($_POST);

            $data = [
                'MaNV'           => $validator->requiredInt('MaNV', 'Nhân viên', 1),
                'MaLoai'         => $validator->requiredInt('MaLoai', 'Loại', 1),
                'NgayQuyetDinh'  => $validator->requiredDate('NgayQuyetDinh', 'Ngày quyết định'),
                'HinhThuc'       => $validator->requiredString('HinhThuc', 'Hình thức', 2, 120),
                'SoTien'         => $validator->optionalFloat('SoTien', 0) ?? 0,
                'LyDo'           => $validator->requiredString('LyDo', 'Lý do', 2, 1000),
                'GhiChu'         => $validator->optionalString('GhiChu', 1000)
            ];

            if (!$validator->isValid()) {
                AppLogger::warning('Validation failed in KhenThuongController::luuThem', ['errors' => $validator->allErrors()]);
                WebResponder::backWithMessage($validator->firstError(), 'error', 'index.php?controller=khenthuong&action=them');
            }

            if ($this->model->insert($data)) {
                WebResponder::redirectWithMessage('index.php?controller=khenthuong&action=index', 'Thêm thành công!', 'success');
            } else {
                WebResponder::backWithMessage('Lỗi khi thêm!', 'error', 'index.php?controller=khenthuong&action=them');
            }
        }
    }

    /* ================== FORM SỬA ================== */
    public function sua() {
        AuthMiddleware::check($this->conn, 'sua_khenthuong');
$quyen = $_SESSION['quyen'] ?? [];
        if (!isset($_GET['id'])) {
            WebResponder::redirectWithMessage('index.php?controller=khenthuong&action=index', 'Thiếu ID!', 'error');
        }

        $id = (int)$_GET['id'];
        $quyetdinh = $this->model->getById($id);

        if (!$quyetdinh) {
            WebResponder::redirectWithMessage('index.php?controller=khenthuong&action=index', 'Không tìm thấy dữ liệu!', 'error');
        }

        $nhanviens = $this->model->getNhanVien();
        $loais = $this->model->getLoai();

        include 'views/khenthuong/sua.php';
    }

    /* ================== LƯU SỬA ================== */
    public function luuSua() {
        AuthMiddleware::check($this->conn, 'sua_khenthuong');
        $quyen = $_SESSION['quyen'] ?? [];

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $validator = new RequestValidator($_POST);

            $data = [
                'MaKTKL'         => $validator->requiredInt('MaKTKL', 'Mã quyết định', 1),
                'MaNV'           => $validator->requiredInt('MaNV', 'Nhân viên', 1),
                'MaLoai'         => $validator->requiredInt('MaLoai', 'Loại', 1),
                'NgayQuyetDinh'  => $validator->requiredDate('NgayQuyetDinh', 'Ngày quyết định'),
                'HinhThuc'       => $validator->requiredString('HinhThuc', 'Hình thức', 2, 120),
                'SoTien'         => $validator->optionalFloat('SoTien', 0) ?? 0,
                'LyDo'           => $validator->requiredString('LyDo', 'Lý do', 2, 1000),
                'GhiChu'         => $validator->optionalString('GhiChu', 1000)
            ];

            if (!$validator->isValid()) {
                AppLogger::warning('Validation failed in KhenThuongController::luuSua', ['errors' => $validator->allErrors()]);
                WebResponder::backWithMessage($validator->firstError(), 'error', 'index.php?controller=khenthuong&action=index');
            }

            if ($this->model->update($data)) {
                WebResponder::redirectWithMessage('index.php?controller=khenthuong&action=index', 'Cập nhật thành công!', 'success');
            } else {
                WebResponder::backWithMessage('Lỗi khi cập nhật!', 'error', 'index.php?controller=khenthuong&action=index');
            }
        }
    }

    /* ================== XÓA ================== */
    public function xoa() {
        AuthMiddleware::check($this->conn, 'xoa_khenthuong');
        $quyen = $_SESSION['quyen'] ?? [];

        if (!isset($_GET['id'])) {
            WebResponder::redirectWithMessage('index.php?controller=khenthuong&action=index', 'Thiếu ID!', 'error');
        }

        $id = (int)$_GET['id'];

        if ($this->model->delete($id)) {
            WebResponder::redirectWithMessage('index.php?controller=khenthuong&action=index', 'Xóa thành công!', 'success');
        } else {
            WebResponder::redirectWithMessage('index.php?controller=khenthuong&action=index', 'Lỗi khi xóa!', 'error');
        }
    }
}
?>