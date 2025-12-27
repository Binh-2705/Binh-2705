<?php
require_once 'models/LuongModel.php';

class LuongController {
    private $model;

    public function __construct($conn) {
        $this->model = new LuongModel($conn);
    }

    
    public function index() {
        $luong = $this->model->getAll();
        include './views/luong/index.php';
    }

    public function timkiem() {
        $keyword = $_GET['keyword'] ?? '';
        $luong = $this->model->timkiem($keyword);
        include './views/luong/timkiem.php';
    }
    public function them() {
        $dsNV = $this->model->getNhanVien();
        include './views/luong/them.php';
    }

   
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $maluong = $_POST['maluong'];
            if($this->model->checkma($maluong)){
                echo "<script>alert('❌ Mã lương đã tồn tại!'); window.history.back();</script>";
                exit;
            }
            $data = $_POST;
            $success = $this->model->insertLuong($data);
            if ($success) {
                echo "<script>alert('✅ Thêm lương thành công!'); window.location='index.php?controller=luong&action=index';</script>";
            } else {
                echo "<script>alert('❌ Lỗi thêm lương!'); window.history.back();</script>";
            }
        }
    }
    public function sua() {
        if (!isset($_GET['maluong'])) {
            echo "<script>alert('Thiếu ID lương!'); window.location='index.php?controller=luong&action=index';</script>";
            exit;
        }

        $maluong = $_GET['maluong'];
        $luong = $this->model->getLuongById($maluong);
        if (!$luong) {
            echo "<script>alert('Không tìm thấy bản ghi lương!'); window.location='index.php?controller=luong&action=index';</script>";
            exit;
        }

        $dsNV = $this->model->getNhanVien();
        include './views/luong/sua.php';
    }

   
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($this->model->updateLuong($_POST)) {
                echo "<script>alert('✅ Cập nhật lương thành công!'); window.location='index.php?controller=luong&action=index';</script>";
            } else {
                echo "<script>alert('❌ Lỗi khi cập nhật!'); window.history.back();</script>";
            }
        } else {
            echo "<script>alert('Thiếu dữ liệu!'); window.location='index.php?controller=luong&action=index';</script>";
        }
    }
    public function delete() {
    if (!isset($_GET['maluong'])) {
        echo "<script>alert('Thiếu mã lương cần xóa!'); window.location='index.php?controller=luong&action=index';</script>";
        exit;
    }

    $maluong = $_GET['maluong'];
    $exists = $this->model->getLuongById($maluong);
    if (!$exists) {
        echo "<script>alert('Không tìm thấy mã lương cần xóa!'); window.location='index.php?controller=luong&action=index';</script>";
        exit;
    }

    if ($this->model->deleteLuong($maluong)) {
        echo "<script>alert('✅ Xóa bản ghi lương thành công!'); window.location='index.php?controller=luong&action=index';</script>";
    } else {
        echo "<script>alert('❌ Lỗi khi xóa!'); window.location='index.php?controller=luong&action=index';</script>";
    }
}
public function exportExcel() {
    $luong = $this->model->getAll();

    $filename = "Danh_sach_luong_" . date('Ymd') . ".xls";

    header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
    header("Content-Disposition: attachment; filename=\"$filename\"");
    echo "\xEF\xBB\xBF"; // BOM UTF-8

    echo "<table border='1'>";
    echo "<tr style='background-color:#f2f2f2; font-weight:bold;'>
            <th>Mã Lương</th>
            <th>Mã NV</th>
            <th>Họ tên</th>
            <th>Tháng</th>
            <th>Lương cơ bản</th>
            <th>Phụ cấp</th>
            <th>Thưởng</th>
            <th>Khấu trừ</th>
            <th>Tổng lương</th>
          </tr>";

    foreach ($luong as $row) {
        $tong = $row['LuongCB'] + $row['PhuCap'] + $row['Thuong'] - $row['KhauTru'];
        echo "<tr>
                <td>{$row['MaLuong']}</td>
                <td>{$row['MaNV']}</td>
                <td>{$row['HoTen']}</td>
                <td>{$row['Thang']}</td>
                <td>".number_format($row['LuongCB'],0,',','.')."</td>
                <td>".number_format($row['PhuCap'],0,',','.')."</td>
                <td>".number_format($row['Thuong'],0,',','.')."</td>
                <td>".number_format($row['KhauTru'],0,',','.')."</td>
                <td>".number_format($tong,0,',','.')."</td>
              </tr>";
    }

    echo "</table>";
    exit;
}


}
