<?php
// controllers/KhenThuongController.php

require_once 'models/KhenThuongModel.php';

class KhenThuongController {
    private $model;

    public function __construct($conn) {
        $this->model = new KhenThuongModel($conn);
    }

    // Hiển thị danh sách (Index)
    public function index() {
        $keyword = $_GET['search'] ?? ''; 
        $result = $this->model->getAllQuyetDinh($keyword); 
        include 'views/khenthuong/index.php'; // Gọi View Index
    }

    // Chuẩn bị form Thêm
    public function them() {
        $nhanviens = $this->model->getAllNhanVienForSelect();
        include 'views/khenthuong/them.php'; // Gọi View Thêm
    }

    // Xử lý Lưu Thêm
    public function luuThem() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $maQD = $_POST['maQD'] ?? null;
            $maNV = $_POST['maNV'];
            $loaiQD = $_POST['loaiQD'];
            $ngayRaQD = $_POST['ngayQD']; 
            $tieuDe = $_POST['tieuDe'];
            $noiDung = $_POST['noiDung'];
            $giaTri = $_POST['giaTri'];
            
            if (empty($maQD) || $this->model->checkMaQDExists($maQD)) {
                $msg = empty($maQD) ? '❌ Mã Quyết định không được để trống!' : "❌ Mã Quyết định $maQD đã tồn tại!";
                echo "<script>alert('{$msg}'); window.history.back();</script>";
                return;
            }

            if ($this->model->insertQuyetDinh($maQD, $maNV, $loaiQD, $ngayRaQD, $tieuDe, $noiDung, $giaTri)) {
                echo "<script>alert('✅ Thêm Quyết định thành công!'); 
                      window.location='index.php?controller=khenthuong&action=index';</script>";
            } else {
                echo "<script>alert('❌ Lỗi khi thêm Quyết định!'); window.history.back();</script>";
            }
        }
    }

    // Chuẩn bị form Sửa
    public function sua() {
        if (!isset($_GET['maQD'])) {
             echo "<script>alert('Thiếu mã quyết định!'); window.location='index.php?controller=khenthuong&action=index';</script>";
             exit;
        }

        $maQD = $_GET['maQD']; 
        $quyetdinh = $this->model->getQuyetDinhById($maQD);
        $nhanviens = $this->model->getAllNhanVienForSelect();

        if (!$quyetdinh) {
            echo "<script>alert('Không tìm thấy Quyết định!'); window.location='index.php?controller=khenthuong&action=index';</script>";
            exit;
        }
        // Gọi View Sửa
        include 'views/khenthuong/sua.php'; 
    }

    // Xử lý Lưu Sửa
    public function luuSua() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $maQD = $_POST['maQD'];
            $maNV = $_POST['maNV'];
            $loaiQD = $_POST['loaiQD'];
            $ngayRaQD = $_POST['ngayQD']; 
            $tieuDe = $_POST['tieuDe'];
            $noiDung = $_POST['noiDung'];
            $giaTri = $_POST['giaTri'];

            if ($this->model->updateQuyetDinh($maQD, $maNV, $loaiQD, $ngayRaQD, $tieuDe, $noiDung, $giaTri)) {
                echo "<script>alert('✅ Cập nhật Quyết định thành công!'); 
                      window.location='index.php?controller=khenthuong&action=index';</script>";
            } else {
                echo "<script>alert('❌ Lỗi khi cập nhật Quyết định!'); window.history.back();</script>";
            }
        }
    }
    
    // Xử lý Xóa
    public function xoa() {
        if (isset($_GET['maQD'])) {
            $maQD = $_GET['maQD'];
            if ($this->model->deleteQuyetDinh($maQD)) {
                echo "<script>alert('✅ Xóa Quyết định thành công!'); window.location='index.php?controller=khenthuong&action=index';</script>";
            } else {
                echo "<script>alert('❌ Lỗi khi xóa Quyết định!'); window.location='index.php?controller=khenthuong&action=index';</script>";
            }
        }
    }
}
?>