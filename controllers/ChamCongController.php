<?php
require_once './ketnoi.php';
require_once './models/ChamCongModel.php';

class ChamCongController {
    private $model;

    public function __construct($conn) {
        $this->model = new ChamCongModel($conn);
    }

    public function index() {
        $data = $this->model->getAllChamCong();
        include './views/chamcong/index.php';
    }

    public function search() {
        if (isset($_GET['keyword'])) {
            $keyword = $_GET['keyword'];
            $data = $this->model->searchChamCong($keyword);
        } else {
            $data = $this->model->getAllChamCong();
        }
        include './views/chamcong/index.php';
    }
    public function sua() {
        $macc = $_GET['macc'] ?? '';
        if (!$macc) { echo "Không tìm thấy bản ghi."; exit; }

        $result = $this->model->getChamCongById($macc);
        if (!$result || $result->num_rows == 0) {
            echo "Không tìm thấy bản ghi."; exit;
        }

        $row = $result->fetch_assoc();
        $nhanvien = $this->model->getAllNhanVien();

        include './views/chamcong/sua.php';
    }

    public function luuSua() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $macc = $_POST['MaCC'];
            $manv = $_POST['MaNV'];
            $thang = $_POST['Thang'];
            $songaylam = $_POST['SoNgayLam'];
            $songaynghi = $_POST['SoNgayNghi'];
            $ghichu = $_POST['GhiChu'] ?? '';

            if ($this->model->updateChamCong($macc, $manv, $thang, $songaylam, $songaynghi, $ghichu)) {
                echo "<script>alert('✅ Cập nhật chấm công thành công!'); 
                      window.location='index.php?controller=chamcong&action=index';</script>";
            } else {
                echo "<script>alert('❌ Lỗi khi cập nhật!'); window.history.back();</script>";
            }
        }
    }
    public function them() {
        $newMaCC = $this->model->getNewMaCC();
        $nhanvien = $this->model->getAllNhanVien();
        include './views/chamcong/them.php';
    }

    public function luu() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $macc = $_POST['MaCC'];
            $manv = $_POST['MaNV'];
            $thang = $_POST['Thang'];
            $songaylam = $_POST['SoNgayLam'];
            $songaynghi = $_POST['SoNgayNghi'];
            $ghichu = $_POST['GhiChu'];

            if ($this->model->insertChamCong($macc, $manv, $thang, $songaylam, $songaynghi, $ghichu)) {
                echo "<script>alert('✅ Thêm chấm công thành công!'); 
                      window.location='index.php?controller=chamcong&action=index';</script>";
            } else {
                echo "<script>alert('❌ Lỗi thêm dữ liệu!'); window.history.back();</script>";
            }
        }
    }
    public function xoa() {
        if (isset($_GET['macc'])) {
            $macc = $_GET['macc'];

            if ($this->model->xoa($macc)) {
                echo "<script>
                        alert('✅ Xóa chấm công thành công!');
                        window.location='index.php?controller=chamcong&action=index';
                      </script>";
            } else {
                echo "<script>
                        alert('❌ Lỗi khi xóa dữ liệu!');
                        window.location='index.php?controller=chamcong&action=index';
                      </script>";
            }
        } else {
            echo "<script>
                    alert('Không có mã chấm công để xóa!');
                    window.location='index.php?controller=chamcong&action=index';
                  </script>";
        }
    }
    public function getSoNgayLam() {
        $manv = $_GET['manv'] ?? '';
        $thang = $_GET['thang'] ?? '';

        $data = ['SoNgayLam' => 0];
        if ($manv && $thang) {
            $data['SoNgayLam'] = $this->model->getSoNgayLam($manv, $thang);
        }

        header('Content-Type: application/json');
        echo json_encode($data);
    }

}
?>