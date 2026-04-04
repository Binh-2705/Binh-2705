<?php

require_once "models/BaoCaoModel.php";
require_once 'core/AuthMiddleware.php';
require_once 'core/RequestValidator.php';
require_once 'core/WebResponder.php';
require_once 'core/AppLogger.php';

class BaocaoController{

    private $model;
    private $conn;

    public function __construct($conn){
        $this->conn = $conn;

        $this->model = new BaoCaoModel($conn);

    }

    // danh sách
    public function index(){
        AuthMiddleware::check($this->conn, 'xem_baocao');
$quyen = $_SESSION['quyen'] ?? [];
        $baocaos = $this->model->getAll();

        include "views/baocao/index.php";
    }


    // form thêm
    public function create(){
        AuthMiddleware::check($this->conn, 'them_baocao');
$quyen = $_SESSION['quyen'] ?? [];
        include "views/baocao/create.php";
    }


    // lưu dữ liệu
    public function store(){
        AuthMiddleware::check($this->conn, 'them_baocao');
        $quyen = $_SESSION['quyen'] ?? [];

        $validator = new RequestValidator($_POST);

        $data = [
            'TenBaoCao' => $validator->requiredString('TenBaoCao', 'Tên báo cáo', 2, 200),
            'LoaiBaoCao' => $validator->requiredString('LoaiBaoCao', 'Loại báo cáo', 2, 100),
            'TuNgay' => $validator->requiredDate('TuNgay', 'Từ ngày'),
            'DenNgay' => $validator->requiredDate('DenNgay', 'Đến ngày'),
            'NguoiTao' => $validator->requiredString('NguoiTao', 'Người tạo', 2, 120),
            'GhiChu' => $validator->optionalString('GhiChu', 1000)
        ];

        if (!$validator->isValid()) {
            AppLogger::warning('Validation failed in BaocaoController::store', ['errors' => $validator->allErrors()]);
            WebResponder::backWithMessage($validator->firstError(), 'error', 'index.php?controller=baocao&action=create');
        }

        if (strtotime($data['DenNgay']) < strtotime($data['TuNgay'])) {
            WebResponder::backWithMessage('Đến ngày phải lớn hơn hoặc bằng Từ ngày.', 'error', 'index.php?controller=baocao&action=create');
        }

        $this->model->create($data);

        WebResponder::redirectWithMessage('index.php?controller=baocao&action=index', 'Thêm báo cáo thành công.', 'success');
    }


    // form sửa
    public function edit(){
            AuthMiddleware::check($this->conn, 'sua_baocao');
$quyen = $_SESSION['quyen'] ?? [];
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            WebResponder::redirectWithMessage('index.php?controller=baocao&action=index', 'Thiếu mã báo cáo.', 'error');
        }

        $baocao = $this->model->find($id);
        if (!$baocao) {
            WebResponder::redirectWithMessage('index.php?controller=baocao&action=index', 'Không tìm thấy báo cáo.', 'error');
        }

        include "views/baocao/edit.php";
    }


    // cập nhật
    public function update(){
        AuthMiddleware::check($this->conn, 'sua_baocao');
        $quyen = $_SESSION['quyen'] ?? [];

        $validator = new RequestValidator($_POST);

        $id = $validator->requiredInt('MaBC', 'Mã báo cáo', 1);

        $data = [
            'TenBaoCao' => $validator->requiredString('TenBaoCao', 'Tên báo cáo', 2, 200),
            'LoaiBaoCao' => $validator->requiredString('LoaiBaoCao', 'Loại báo cáo', 2, 100),
            'TuNgay' => $validator->requiredDate('TuNgay', 'Từ ngày'),
            'DenNgay' => $validator->requiredDate('DenNgay', 'Đến ngày'),
            'NguoiTao' => $validator->requiredString('NguoiTao', 'Người tạo', 2, 120),
            'GhiChu' => $validator->optionalString('GhiChu', 1000)
        ];

        if (!$validator->isValid()) {
            AppLogger::warning('Validation failed in BaocaoController::update', ['errors' => $validator->allErrors()]);
            WebResponder::backWithMessage($validator->firstError(), 'error', 'index.php?controller=baocao&action=index');
        }

        if (strtotime($data['DenNgay']) < strtotime($data['TuNgay'])) {
            WebResponder::backWithMessage('Đến ngày phải lớn hơn hoặc bằng Từ ngày.', 'error', 'index.php?controller=baocao&action=index');
        }

        $this->model->update($id,$data);

        WebResponder::redirectWithMessage('index.php?controller=baocao&action=index', 'Cập nhật báo cáo thành công.', 'success');
    }


    // xóa
    public function delete(){
        AuthMiddleware::check($this->conn, 'xoa_baocao');
$quyen = $_SESSION['quyen'] ?? [];
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            WebResponder::redirectWithMessage('index.php?controller=baocao&action=index', 'Thiếu mã báo cáo.', 'error');
        }

        $this->model->delete($id);

        WebResponder::redirectWithMessage('index.php?controller=baocao&action=index', 'Xóa báo cáo thành công.', 'success');
    }
    // dashboard thống kê
public function dashboard(){
    AuthMiddleware::check($this->conn, 'dashboard_baocao');
$quyen = $_SESSION['quyen'] ?? [];
$thongke = $this->model->dashboard();

$phongban = $this->model->thongKePhongBan();

$tuyendung = $this->model->thongKeTuyenDung();

$luong = $this->model->thongKeLuong();

$nghiphep = $this->model->thongKeNghiPhep();

$topnghi = $this->model->topNghiPhep();

$chamcong = $this->model->thongKeChamCong();

$gioitinh = $this->model->thongKeGioiTinh();

$hopdong = $this->model->thongKeHopDong();

include "views/baocao/dashboard.php";

}
public function exportExcel(){
    AuthMiddleware::check($this->conn, 'xuatex_baocao');
$quyen = $_SESSION['quyen'] ?? [];
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=baocao_nhan_su.xls");

$data = $this->model->getAll();

echo "ID\tTên báo cáo\tLoại\tNgười tạo\n";

while($row=mysqli_fetch_assoc($data)){
echo $row['MaBC']."\t".$row['TenBaoCao']."\t".$row['LoaiBaoCao']."\t".$row['NguoiTao']."\n";
}

}

public function exportJson(){
    AuthMiddleware::check($this->conn, 'xuatex_baocao');
    $quyen = $_SESSION['quyen'] ?? [];

    header('Content-Type: application/json; charset=UTF-8');
    header('Content-Disposition: attachment; filename="baocao_nhan_su_' . date('Ymd-His') . '.json"');

    $data = $this->model->getAll();
    $rows = [];

    while ($row = mysqli_fetch_assoc($data)) {
        $rows[] = $row;
    }

    echo json_encode([
        'exportedAt' => date('c'),
        'count' => count($rows),
        'reports' => $rows,
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

}