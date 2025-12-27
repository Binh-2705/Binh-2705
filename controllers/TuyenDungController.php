<?php
require_once 'models/TuyenDungModel.php';

class TuyenDungController {
    private $model;
    
    public function __construct($conn) {
        $this->model = new TuyenDungModel($conn);
    }
    
    // ACTION INDEX
    public function index() {
        $keyword = $_GET['search'] ?? ''; 
        $danhSachUngVien = $this->model->getList($keyword); 
        
        require_once 'views/tuyendung/index.php';
    }

    // ACTION ADD
    public function add() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $hoTen = $_POST['HoTen'] ?? '';
            $email = $_POST['Email'] ?? '';
            $sdt = $_POST['SoDienThoai'] ?? '';
            $viTri = $_POST['ViTriUngTuyen'] ?? '';
            $ngayNop = $_POST['NgayNop'] ?? date('Y-m-d');
            $ghiChu = $_POST['GhiChu'] ?? '';

            if ($this->model->add($hoTen, $email, $sdt, $viTri, $ngayNop, $ghiChu)) {
                $message = "Thêm ứng viên thành công!";
                header('Location: index.php?controller=tuyendung&action=index&msg=' . urlencode($message));
                exit;
            } else {
                $error = "Lỗi khi thêm: " . $this->model->conn->error;
            }
        }
        require_once 'views/tuyendung/add.php';
    }

    // ACTION DELETE
    public function delete() {
        $id = $_GET['id'] ?? null;
        if ($id && $this->model->delete($id)) {
            $message = "Xóa ứng viên thành công!";
        } else {
            $message = "Lỗi khi xóa ứng viên!";
        }
        header('Location: index.php?controller=tuyendung&action=index&msg=' . urlencode($message));
        exit;
    }

    // ACTION APPROVE
    public function approve() {
    $id = $_GET['id'] ?? null;
    $status = $_GET['status'] ?? null;

    if ($id && $status) {
        if ($this->model->updateStatus($id, $status)) {
            $message = "Đã cập nhật trạng thái ứng viên thành công!";
        } else {
            $message = "Lỗi khi cập nhật trạng thái!";
        }
        header('Location: index.php?controller=tuyendung&action=index&msg=' . urlencode($message));
        exit;
    }
}
}
?>