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

    // ACTION XUẤT EXCEL
    public function xuatexcel() {
    $danhSachUngVien = $this->model->getListExcel(); 
    
    $filename = "Danh_sach_ung_vien_" . date('Ymd_His') . ".xls";

    header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
    header("Content-Disposition: attachment; filename=\"$filename\"");
    echo "\xEF\xBB\xBF"; 
    echo "<table border='1'>";
    
    echo "<tr style='background-color:#1e3a8a; color:white; font-weight:bold;'>
            <th>STT</th>
            <th>Họ và Tên</th>
            <th>Vị trí</th>
            <th>SĐT</th>
            <th>Ngày nộp</th>
            <th>Trạng thái</th>
          </tr>";

    if (!empty($danhSachUngVien)) {
        $stt = 1;
        foreach ($danhSachUngVien as $uv) {
            $hoTen = $uv['HoTen'] ?? '';
            $viTri = $uv['ViTriUngTuyen'] ?? $uv['ViTri'] ?? ''; 
            $sdt = $uv['SoDienThoai'] ?? $uv['SDT'] ?? '';    
            $ngayNop = isset($uv['NgayNop']) ? date('d/m/Y', strtotime($uv['NgayNop'])) : '';
            $trangThai = $uv['TrangThai'] ?? '';

            echo "<tr>
                    <td>" . $stt++ . "</td>
                    <td>{$hoTen}</td>
                    <td>{$viTri}</td>
                    <td>{$sdt}</td>
                    <td>{$ngayNop}</td>
                    <td>{$trangThai}</td>
                  </tr>";
            }
        }
        echo "</table>";
        exit();
    }
}    