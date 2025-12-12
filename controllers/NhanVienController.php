<?php
// controllers/NhanVienController.php

require_once 'models/NhanVienModel.php';

class NhanVienController {
    private $model;

    public function __construct($conn) {
        $this->model = new NhanVienModel($conn);
    }

    public function index() {
        $result = $this->model->getAllNhanVien();
        include 'views/nhanvien/index.php'; 
    }
    
    public function them() {
        // Lấy danh sách Phòng ban
        $phongbans = $this->model->getAllPhongBan(); 
        // THÊM: Lấy danh sách Chức vụ
        $chucvus = $this->model->getAllChucVu(); 
        
        include 'views/nhanvien/them.php'; // Đảm bảo view này có thể truy cập $phongbans và $chucvus
    }

    public function luuThem() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $manv = $_POST['manv'];
            $hoten = $_POST['hoten'];
            $gioitinh = $_POST['gioitinh'];
            $ngaysinh = $_POST['ngaysinh'];
            // Đổi tên biến để đồng bộ với tên cột:
            $maPB = $_POST['phongban']; 
            $maCV = $_POST['chucvu']; 

            //kiểm tra trùng
            if($this->model->checkma($manv)){
                echo "<script>alert('❌ Mã nhân viên đã tồn tại!'); window.history.back();</script>";
                exit;
            }

            if ($this->model->insertNhanVien($manv, $hoten, $gioitinh, $ngaysinh, $maPB, $maCV)) {
                echo "<script>alert('Thêm nhân viên thành công!'); 
                            window.location='index.php?controller=nhanvien&action=index';</script>";
            } else {
                echo "<script>alert('Lỗi khi thêm nhân viên: " . mysqli_error($this->model->conn) . "'); window.history.back();</script>";
            }
        }
    }
    
    public function sua() {
        if (!isset($_GET['manv'])) {
            echo "<script>alert('Thiếu mã nhân viên!'); window.location='index.php?controller=nhanvien&action=index';</script>";
            exit;
        }

        $manv = $_GET['manv'];
        $nhanvien = $this->model->getNhanVienById($manv);
        $phongbans = $this->model->getAllPhongBan();
        // THÊM: Lấy danh sách Chức vụ
        $chucvus = $this->model->getAllChucVu(); 

        if (!$nhanvien) {
            echo "<script>alert('Không tìm thấy nhân viên!'); window.location='index.php?controller=nhanvien&action=index';</script>";
            exit;
        }

        include 'views/nhanvien/sua.php'; // Đảm bảo view này có thể truy cập $chucvus
    }

    
    public function luuSua() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Đổi tên biến để đồng bộ với tên cột:
            $maPB = $_POST['phongban']; 
            $maCV = $_POST['chucvu']; 

            // LƯU Ý: Bỏ $_POST['luong'] vì updateNhanVien trong Model đã bỏ tham số này
            $success = $this->model->updateNhanVien(
                $_POST['manv'],
                $_POST['hoten'],
                $_POST['gioitinh'],
                $_POST['ngaysinh'],
                $maPB,
                $maCV
            );

            if ($success) {
                echo "<script>alert('✅ Cập nhật thành công!'); window.location='index.php?controller=nhanvien&action=index';</script>";
            } else {
                echo "<script>alert('❌ Lỗi khi cập nhật: " . mysqli_error($this->model->conn) . "'); window.history.back();</script>";
            }
        } else {
            echo "<script>alert('Thiếu dữ liệu!'); window.location='index.php?controller=nhanvien&action=index';</script>";
        }
    }
    
    public function xoa() {
        if (isset($_GET['manv'])) {
            $manv = $_GET['manv'];
            if ($this->model->deleteNhanVien($manv)) {
                echo "<script>
                        alert('✅ Xóa nhân viên thành công!');
                        window.location='index.php?controller=nhanvien&action=index';
                      </script>";
            } else {
                echo "<script>
                        alert('❌ Lỗi khi xóa nhân viên: " . mysqli_error($this->model->conn) . "');
                        window.location='index.php?controller=nhanvien&action=index';
                      </script>";
            }
        } else {
            echo "<script>
                        alert('⚠️ Không có mã nhân viên để xóa!');
                        window.location='index.php?controller=nhanvien&action=index';
                      </script>";
        }
    }
    
    public function timkiem() {
        $keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';
        $result = $this->model->searchNhanVien($keyword);

        $nhanviens = [];
        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $nhanviens[] = $row;
            }
        }

        require 'views/nhanvien/timkiem.php';
    }

    public function exportExcel() {
        $result = $this->model->getAllNhanVien();

        $filename = "Danh_sach_nhan_vien_" . date('Ymd') . ".xls";

        header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        echo "\xEF\xBB\xBF"; // BOM UTF-8

        echo "<table border='1'>";
        echo "<tr style='background-color:#f2f2f2; font-weight:bold;'>
                <th>Mã NV</th>
                <th>Họ tên</th>
                <th>Giới tính</th>
                <th>Ngày sinh</th>
                <th>Phòng ban</th>
                <th>Chức vụ</th>
                <th>Mức lương</th>
              </tr>";

        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>
                    <td>{$row['MaNV']}</td>
                    <td>{$row['HoTen']}</td>
                    <td>{$row['GioiTinh']}</td>
                    <td>{$row['NgaySinh']}</td>
                    <td>".($row['TenPB'] ?? '')."</td>
                    <td>{$row['TenChucVu']}</td> <td>".(isset($row['LuongCB']) ? number_format($row['LuongCB'],0,',','.') : '')."</td>
                  </tr>";
        }

        echo "</table>";
        exit;
    }
}
?>