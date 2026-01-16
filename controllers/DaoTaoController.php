<?php
require_once './models/DaoTaoModel.php';
require_once './models/HocVienModel.php';
require_once './models/GiangVienModel.php';

class DaoTaoController {
    private $model;
    private $hocVienModel;
    private $giangVienModel;
    
    public function __construct($conn) { 
        $this->model = new DaoTaoModel($conn);
        $this->hocVienModel = new HocVienModel($conn);
        $this->giangVienModel = new GiangVienModel($conn);
    }

    public function index() {
    $daotao = $this->model->getAll();
    
    // Lấy thống kê
    $countKhoaHoc = $this->model->countAll();
    $countGiangVien = $this->giangVienModel->countAll();
    $countHocVien = $this->hocVienModel->countAll();
    
    // Reset pointer cho daotao result để dùng trong view
    if ($daotao && $daotao->num_rows > 0) {
        mysqli_data_seek($daotao, 0);
    }
    
    include './views/daotao/index.php';
}
    public function them() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->model->insert($_POST);
            header("Location: index.php?controller=daotao&action=index");
        } else {
            $giangVienList = $this->giangVienModel->getAll();
            include './views/daotao/them.php';
        }
    }

    public function sua() {
        $id = $_GET['madt'];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->model->update($_POST);
            header("Location: index.php?controller=daotao&action=index");
        } else {
            $daotao = $this->model->getById($id)->fetch_assoc();
            $giangVienList = $this->giangVienModel->getAll();
            include './views/daotao/sua.php';
        }
    }

    public function xoa() {
        $id = $_GET['madt'];
        $this->model->delete($id);
        header("Location: index.php?controller=daotao&action=index");
    }

    public function exportExcel() {
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=daotao_" . date('Ymd') . ".xls");
        $result = $this->model->getAll();
        
        echo "<table border='1'>";
        echo "<tr><th colspan='8'>DANH SÁCH KHÓA ĐÀO TẠO</th></tr>";
        echo "<tr><th>Mã ĐT</th><th>Tên khóa học</th><th>Giảng viên</th><th>Ngày bắt đầu</th><th>Ngày kết thúc</th><th>Địa điểm</th><th>Chi phí</th><th>Ghi chú</th></tr>";
        
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['MaDT'] . "</td>";
            echo "<td>" . $row['TenKhoaHoc'] . "</td>";
            echo "<td>" . $row['GiangVien'] . "</td>";
            echo "<td>" . $row['NgayBatDau'] . "</td>";
            echo "<td>" . $row['NgayKetThuc'] . "</td>";
            echo "<td>" . $row['DiaDiem'] . "</td>";
            echo "<td>" . number_format($row['ChiPhi']) . " VNĐ</td>";
            echo "<td>" . $row['GhiChu'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        exit;
    }
    
    public function timkiem() {
        $keyword = $_GET['keyword'] ?? '';
        $daotao = $this->model->search($keyword);
        include './views/daotao/index.php';
    }
    
    // ========== QUẢN LÝ HỌC VIÊN ==========
    
    public function hocvien() {
        $maDT = $_GET['madt'] ?? '';
        $hocVienList = $this->hocVienModel->getByKhoaHoc($maDT);
        $daotao = $this->model->getById($maDT)->fetch_assoc();
        $dsNhanVien = $this->hocVienModel->getNhanVienChuaThamGia($maDT);
        include './views/daotao/hocvien.php';
    }
    
    public function themhocvien() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->hocVienModel->insert($_POST);
            header("Location: index.php?controller=daotao&action=hocvien&madt=" . $_POST['MaDT']);
        }
    }
    
    public function xoahocvien() {
        $id = $_GET['id'];
        $maDT = $_GET['madt'];
        $this->hocVienModel->delete($id);
        header("Location: index.php?controller=daotao&action=hocvien&madt=" . $maDT);
    }
    
    public function chamdiem() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->hocVienModel->updateDiem($_POST);
            header("Location: index.php?controller=daotao&action=hocvien&madt=" . $_POST['MaDT']);
        }
    }
    
    public function diemdanh() {
        $id = $_GET['id'];
        $trangThai = $_GET['trangthai'];
        $maDT = $_GET['madt'];
        $this->hocVienModel->updateDiemDanh($id, $trangThai);
        header("Location: index.php?controller=daotao&action=hocvien&madt=" . $maDT);
    }
    
    public function xuatdiem() {
        $maDT = $_GET['madt'];
        $hocVienList = $this->hocVienModel->getByKhoaHoc($maDT);
        $daotao = $this->model->getById($maDT)->fetch_assoc();
        
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=diem_khóa_" . $maDT . "_" . date('Ymd') . ".xls");
        
        echo "<table border='1'>";
        echo "<tr><th colspan='8'>BẢNG ĐIỂM KHÓA HỌC: " . $daotao['TenKhoaHoc'] . "</th></tr>";
        echo "<tr><th colspan='8'>Mã khóa: " . $maDT . " | Ngày xuất: " . date('d/m/Y') . "</th></tr>";
        echo "<tr><th>STT</th><th>Mã NV</th><th>Họ tên</th><th>Phòng ban</th><th>Điểm</th><th>Kết quả</th><th>Trạng thái</th><th>Ghi chú</th></tr>";
        
        $stt = 1;
        while ($row = $hocVienList->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $stt++ . "</td>";
            echo "<td>" . $row['MaNV'] . "</td>";
            echo "<td>" . $row['HoTen'] . "</td>";
            echo "<td>" . $row['PhongBan'] . "</td>";
            echo "<td>" . ($row['Diem'] ?: '') . "</td>";
            echo "<td>" . ($row['KetQua'] ?: '') . "</td>";
            echo "<td>" . $row['TrangThai'] . "</td>";
            echo "<td>" . ($row['GhiChu'] ?: '') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        exit;
    }
    
    // ========== QUẢN LÝ GIẢNG VIÊN ==========
    
    public function giangvien() {
    $keyword = $_GET['keyword'] ?? '';
    if (!empty($keyword)) {
        $giangVienList = $this->giangVienModel->search($keyword);
    } else {
        $giangVienList = $this->giangVienModel->getAll();
    }
    
    // Tính thống kê
    $thongKeGV = $this->giangVienModel->getThongKeGiangVien();
    $topGiangVien = $this->giangVienModel->getTopGiangVien(3);
    
    include './views/daotao/giangvien.php';
}
    
    public function themgiangvien() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->giangVienModel->insert($_POST);
            header("Location: index.php?controller=daotao&action=giangvien");
        } else {
            include './views/daotao/themgiangvien.php';
        }
    }
    
    public function suagiangvien() {
        $id = $_GET['magv'];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->giangVienModel->update($_POST);
            header("Location: index.php?controller=daotao&action=giangvien");
        } else {
            $giangVien = $this->giangVienModel->getById($id)->fetch_assoc();
            include './views/daotao/suagiangvien.php';
        }
    }
    
    public function xoagiangvien() {
        $id = $_GET['magv'];
        $this->giangVienModel->delete($id);
        header("Location: index.php?controller=daotao&action=giangvien");
    }
    
    // ========== BÁO CÁO THỐNG KÊ ==========
    
    public function baocao() {
        $thongKe = $this->model->thongKe();
        $topKhoaHoc = $this->model->getTopKhoaHoc();
        $topGiangVien = $this->giangVienModel->getTopGiangVien();
        $thongKeHocVien = $this->hocVienModel->thongKeHocVien();
        
        include './views/daotao/baocao.php';
    }
    
    public function exportbaocao() {
        $thongKe = $this->model->thongKe();
        $topKhoaHoc = $this->model->getTopKhoaHoc();
        $topGiangVien = $this->giangVienModel->getTopGiangVien();
        
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=baocao_daotao_" . date('Ymd_His') . ".xls");
        
        echo "<html><meta charset='UTF-8'>";
        echo "<table border='1'>";
        
        // Tiêu đề
        echo "<tr><th colspan='6' style='background:#3b82f6;color:white;height:40px;font-size:16px;'>BÁO CÁO TỔNG HỢP ĐÀO TẠO</th></tr>";
        echo "<tr><th colspan='6'>Ngày xuất: " . date('d/m/Y H:i:s') . "</th></tr>";
        
        
        
        echo "</table>";
        exit;
    }
}
?>