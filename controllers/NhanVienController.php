<?php
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
        $phongbans = $this->model->getAllPhongBan();
        include 'views/nhanvien/them.php';
    }

    public function luuThem() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $manv = $_POST['manv'];
            $hoten = $_POST['hoten'];
            $gioitinh = $_POST['gioitinh'];
            $ngaysinh = $_POST['ngaysinh'];
            $phongban = $_POST['phongban'];
            $chucvu = $_POST['chucvu'];

            if ($this->model->insertNhanVien($manv, $hoten, $gioitinh, $ngaysinh, $phongban, $chucvu)) {
                echo "<script>alert('Thêm nhân viên thành công!'); 
                      window.location='index.php?controller=nhanvien&action=index';</script>";
            } else {
                echo "<script>alert('Lỗi khi thêm nhân viên!'); window.history.back();</script>";
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

        if (!$nhanvien) {
            echo "<script>alert('Không tìm thấy nhân viên!'); window.location='index.php?controller=nhanvien&action=index';</script>";
            exit;
        }

        include 'views/nhanvien/sua.php';
    }

   
    public function luuSua() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $success = $this->model->updateNhanVien(
                $_POST['manv'],
                $_POST['hoten'],
                $_POST['gioitinh'],
                $_POST['ngaysinh'],
                $_POST['phongban'],
                $_POST['chucvu'],
                $_POST['luong']
            );

            if ($success) {
                echo "<script>alert('✅ Cập nhật thành công!'); window.location='index.php?controller=nhanvien&action=index';</script>";
            } else {
                echo "<script>alert('❌ Lỗi khi cập nhật'); window.history.back();</script>";
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
                        alert('❌ Lỗi khi xóa nhân viên');
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
}
