<?php
require_once 'models/PhongBanModel.php';

class PhongBanController {
    private $model;

    public function __construct($conn) {
        $this->model = new PhongBanModel($conn);
    }

    public function index() {
        $phongbans = $this->model->getAllPhongBan();
        include 'views/phongban/index.php';
        
    }
    public function them() {
        include 'views/phongban/them.php';
    }

    public function timkiem() {
        $keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';
        $phongbans = $this->model->searchPhongBan($keyword);
        include 'views/phongban/index.php';
    }
     public function luuThem() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $mapb = trim($_POST['mapb']);
            $tenpb = trim($_POST['tenpb']);
            $mota = trim($_POST['mota']);

             if($this->model->checkma($mapb)){
                echo "<script>alert('❌ Mã phòng ban đã tồn tại!'); window.history.back();</script>";
                exit;
            }

            if ($mapb == "" || $tenpb == "") {
                echo "<script>alert('Vui lòng nhập đầy đủ thông tin!'); window.history.back();</script>";
                exit;
            }

            if ($this->model->insertPhongBan($mapb, $tenpb, $mota)) {
                echo "<script>alert('Thêm phòng ban thành công!'); window.location='index.php?controller=phongban&action=index';</script>";
            } else {
                echo "<script>alert('Mã phòng ban đã tồn tại hoặc lỗi!'); window.history.back();</script>";
            }
        }
    }
     public function sua() {
        if (!isset($_GET['mapb'])) {
            echo "<script>alert('Không có mã phòng ban!'); window.location='index.php?controller=phongban&action=index';</script>";
            exit;
        }

        $mapb = $_GET['mapb'];
        $phongban = $this->model->getPhongBanById($mapb);

        if (!$phongban) {
            echo "<script>alert('Không tìm thấy phòng ban!'); window.location='index.php?controller=phongban&action=index';</script>";
            exit;
        }

        include 'views/phongban/sua.php';
    }

    public function luuSua() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $mapb = $_POST['mapb'];
            $tenpb = $_POST['tenpb'];
            $mota = $_POST['mota'];

            if ($this->model->updatePhongBan($mapb, $tenpb, $mota)) {
                echo "<script>alert('Cập nhật phòng ban thành công!'); window.location='index.php?controller=phongban&action=index';</script>";
            } else {
                echo "<script>alert('Lỗi khi cập nhật!'); window.history.back();</script>";
            }
        }
    }
    public function xoa() {
        if (isset($_GET['mapb'])) {
            $manv = $_GET['mapb'];
            if ($this->model->deletePhongBan($manv)) {
                echo "<script>
                        alert('✅ Xóa thành công!');
                        window.location='index.php?controller=phongban&action=index';
                      </script>";
            } else {
                echo "<script>
                        alert('❌ Lỗi khi xóa');
                        window.location='index.php?controller=phongban&action=index';
                      </script>";
            }
        } else {
            echo "<script>
                    alert('⚠️ Không có mã để xóa!');
                    window.location='index.php?controller=phongban&action=index';
                  </script>";
        }
    }
   
}
