<?php
// controllers/HopDongController.php - ĐÃ SỬA THÊM LuongCoBan

require_once 'models/HopDongModel.php';

class HopDongController {
    private $model;

    public function __construct($conn) {
        $this->model = new HopDongModel($conn);
    }

    public function index() {
        $keyword = $_GET['search'] ?? ''; 
        $result = $this->model->getAllHopDong($keyword); 
        include 'views/hopdong/index.php'; 
    }


    public function them() {
        $nhanviens = $this->model->getAllNhanVienForSelect();
        include 'views/hopdong/them.php'; 
    }

    public function luuThem() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $maHD = $_POST['maHD'] ?? null;
            $maNV = $_POST['maNV'];
            $loaiHopDong = $_POST['loaiHopDong']; 
            $ngayBatDau = $_POST['ngayBatDau']; 
            $ngayKetThuc = $_POST['ngayKetThuc'] ?? null;
            $trangThai = $_POST['trangThai'];
            // 🔥 THÊM: Lấy LuongCoBan
            $luongCoBan = $_POST['luongCoBan'] ?? 0;
            
            // --- BẮT LỖI NGHIỆP VỤ ---

            if (empty($maHD)) {
                echo "<script>alert('❌ Mã Hợp đồng không được để trống!'); window.history.back();</script>";
                return;
            }

            if ($this->model->checkMaHDExists($maHD)) {
                echo "<script>alert('❌ Mã Hợp đồng $maHD đã tồn tại! Vui lòng chọn mã khác.'); window.history.back();</script>";
                return;
            }

            if (!empty($ngayKetThuc) && (strtotime($ngayKetThuc) < strtotime($ngayBatDau))) {
                echo "<script>alert('❌ Ngày Kết thúc không thể trước Ngày Bắt đầu!'); window.history.back();</script>";
                return;
            }

            // --- LƯU DỮ LIỆU (THÊM $luongCoBan) ---

            if ($this->model->insertHopDong($maHD, $maNV, $loaiHopDong, $ngayBatDau, $ngayKetThuc, $trangThai, $luongCoBan)) {
                echo "<script>alert('✅ Thêm Hợp đồng thành công!'); 
                      window.location='index.php?controller=hopdong&action=index';</script>";
            } else {
                echo "<script>alert('❌ Lỗi khi thêm Hợp đồng! Vui lòng kiểm tra lại dữ liệu và kết nối DB.'); window.history.back();</script>";
            }
        }
    }
    
    // Hàm Sửa
    public function sua() {
        if (!isset($_GET['maHD'])) {
             echo "<script>alert('Thiếu mã hợp đồng!'); window.location='index.php?controller=hopdong&action=index';</script>";
             exit;
        }

        $maHD = $_GET['maHD']; 
        $hopdong = $this->model->getHopDongById($maHD);
        $nhanviens = $this->model->getAllNhanVienForSelect();

        if (!$hopdong) {
            echo "<script>alert('Không tìm thấy Hợp đồng!'); window.location='index.php?controller=hopdong&action=index';</script>";
            exit;
        }

        include 'views/hopdong/sua.php'; 
    }

    // Hàm Lưu Sửa
    public function luuSua() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $maHD = $_POST['maHD']; 
            $maNV = $_POST['maNV'];
            $loaiHopDong = $_POST['loaiHopDong']; 
            $ngayBatDau = $_POST['ngayBatDau']; 
            $ngayKetThuc = $_POST['ngayKetThuc'] ?? null; 
            $trangThai = $_POST['trangThai'];
            // 🔥 THÊM: Lấy LuongCoBan
            $luongCoBan = $_POST['luongCoBan'] ?? 0;


            // --- BẮT LỖI NGHIỆP VỤ ---
            
            if (!empty($ngayKetThuc) && (strtotime($ngayKetThuc) < strtotime($ngayBatDau))) {
                echo "<script>alert('❌ Ngày Kết thúc không thể trước Ngày Bắt đầu!'); window.history.back();</script>";
                return;
            }

            // --- LƯU DỮ LIỆU (THÊM $luongCoBan) ---

            if ($this->model->updateHopDong($maHD, $maNV, $loaiHopDong, $ngayBatDau, $ngayKetThuc, $trangThai, $luongCoBan)) {
                echo "<script>alert('✅ Cập nhật Hợp đồng thành công!'); 
                      window.location='index.php?controller=hopdong&action=index';</script>";
            } else {
                echo "<script>alert('❌ Lỗi khi cập nhật Hợp đồng! Vui lòng kiểm tra lại dữ liệu.'); window.history.back();</script>";
            }
        }
    }
    
    // Hàm Xóa
    public function xoa() {
        if (isset($_GET['maHD'])) {
            $maHD = $_GET['maHD'];
            if ($this->model->deleteHopDong($maHD)) {
                echo "<script>alert('✅ Xóa Hợp đồng thành công!'); window.location='index.php?controller=hopdong&action=index';</script>";
            } else {
                echo "<script>alert('❌ Lỗi khi xóa Hợp đồng! Hợp đồng có thể đang liên quan đến dữ liệu khác.'); window.location='index.php?controller=hopdong&action=index';</script>";
            }
        }
    }
}
?>