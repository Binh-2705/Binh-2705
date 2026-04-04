<?php
require_once 'models/DaoTaoModel.php';
require_once 'core/AuthMiddleware.php';
require_once 'core/RequestValidator.php';
require_once 'core/AppLogger.php';
require_once 'core/WebResponder.php';
class DaoTaoController {
    private $model;
    private $conn;

    public function __construct($conn){
        $this->conn = $conn;
        $this->model = new DaoTaoModel($conn);
    }

    public function index(){
        AuthMiddleware::check($this->conn, 'xem_khoa_dao_tao');
         $quyen = $_SESSION['quyen'] ?? [];
        $khoa = $this->model->getAllKhoa();
        include 'views/daotao/index.php';
    }

    public function themKhoa(){
        AuthMiddleware::check($this->conn, 'them_khoa_dao_tao');
        $quyen = $_SESSION['quyen'] ?? [];
        if($_SERVER['REQUEST_METHOD']=='POST'){
            $validator = new RequestValidator($_POST);
            $ten = $validator->requiredString('ten', 'Tên khóa đào tạo', 2, 150);
            $tu = $validator->requiredDate('tu', 'Từ ngày');
            $den = $validator->requiredDate('den', 'Đến ngày');
            $noidung = $validator->optionalString('noidung', 2000);
            $donvi = $validator->requiredString('donvi', 'Đơn vị tổ chức', 2, 150);

            if (!$validator->isValid()) {
                AppLogger::warning('Validation failed in DaoTaoController::themKhoa', ['errors' => $validator->allErrors()]);
                WebResponder::backWithMessage($validator->firstError(), 'error', 'index.php?controller=daotao&action=themKhoa');
            }

            if (strtotime($den) < strtotime($tu)) {
                WebResponder::backWithMessage('Đến ngày phải lớn hơn hoặc bằng Từ ngày.', 'error', 'index.php?controller=daotao&action=themKhoa');
            }

            $this->model->insertKhoa(
                $ten,
                $tu,
                $den,
                $noidung,
                $donvi
            );
            WebResponder::redirectWithMessage('index.php?controller=daotao', 'Thêm khóa đào tạo thành công.', 'success');
        }
        include 'views/daotao/them.php';
    }

    public function xoaKhoa(){
        AuthMiddleware::check($this->conn, 'xoa_khoa_dao_tao');
        $quyen = $_SESSION['quyen'] ?? [];
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            WebResponder::redirectWithMessage('index.php?controller=daotao', 'Thiếu mã khóa đào tạo.', 'error');
        }

        $this->model->deleteKhoa($id);
        WebResponder::redirectWithMessage('index.php?controller=daotao', 'Xóa khóa đào tạo thành công.', 'success');
    }

    public function thamGia(){
        AuthMiddleware::check($this->conn, 'xem_tham_gia_dao_tao');
        $quyen = $_SESSION['quyen'] ?? [];
        $maKDT = (int)($_GET['id'] ?? 0);
        if ($maKDT <= 0) {
            WebResponder::redirectWithMessage('index.php?controller=daotao', 'Thiếu mã khóa đào tạo.', 'error');
        }

        $dsNV = $this->model->getNhanVien();
        $thamgia = $this->model->getThamGia($maKDT);
        $duocCham = $this->model->kiemTraHoanThanh($maKDT);

        include 'views/daotao/thamgia.php';
    }

    public function themNhanVien(){
        AuthMiddleware::check($this->conn, 'them_tham_gia_dao_tao');
        $quyen = $_SESSION['quyen'] ?? [];
        $validator = new RequestValidator($_POST);
        $maNV = $validator->requiredInt('MaNV', 'Nhân viên', 1);
        $maKDT = $validator->requiredInt('MaKDT', 'Khóa đào tạo', 1);

        if (!$validator->isValid()) {
            WebResponder::backWithMessage($validator->firstError(), 'error', 'index.php?controller=daotao');
        }

        $this->model->themNhanVien($maNV, $maKDT);
        WebResponder::redirectWithMessage('index.php?controller=daotao&action=thamGia&id=' . $maKDT, 'Thêm nhân viên vào khóa thành công.', 'success');
    }

    public function capNhatKetQua(){
        AuthMiddleware::check($this->conn, 'capnhat_ketqua_dao_tao');
        $quyen = $_SESSION['quyen'] ?? [];
        $validator = new RequestValidator($_POST);
        $maTGDT = $validator->requiredInt('MaTGDT', 'Mã tham gia đào tạo', 1);
        $ketQua = trim((string)($_POST['KetQua'] ?? ''));
        $diem = $validator->optionalFloat('DiemDanhGia', 0);
        $diem = $diem ?? 0;

        if ($ketQua === '') {
            WebResponder::backWithMessage('Kết quả không được để trống.', 'error', 'index.php?controller=daotao');
        }

        if (!$validator->isValid()) {
            WebResponder::backWithMessage($validator->firstError(), 'error', 'index.php?controller=daotao');
        }

        $this->model->updateKetQua(
            $maTGDT,
            $ketQua,
            $diem
        );
        WebResponder::backWithMessage('Cập nhật kết quả thành công.', 'success', 'index.php?controller=daotao');
    }
}