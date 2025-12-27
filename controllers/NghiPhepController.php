<?php
require_once './ketnoi.php';
require_once './models/NghiPhepModel.php';

class NghiPhepController {
    private $model;

    public function __construct($conn) {
        $this->model = new NghiPhepModel($conn);
    }

    // Hiển thị danh sách nghỉ phép
    public function index() {
        $data = $this->model->getAllNghiPhep();
        include './views/nghiphep/index.php';
    }

    // Tìm kiếm nghỉ phép
    public function search() {
        if (isset($_POST['keyword'])) {
            $keyword = $_POST['keyword'];
            $data = $this->model->searchNghiPhep($keyword);
        } else {
            $data = $this->model->getAllNghiPhep();
        }
        include './views/nghiphep/index.php';
    }


    // Form thêm nghỉ phép
    public function them() {
        $nhanvien = $this->model->getAllNhanVien();
        include './views/nghiphep/them.php';
    }

    // Lưu nghỉ phép mới
    public function luu() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $manp = $_POST['MaNP'];
            $manv = $_POST['MaNV'];
            $tungay = $_POST['TuNgay'];
            $denngay = $_POST['DenNgay'];
            $lydo = $_POST['LyDo'] ?? '';
            $trangthai = $_POST['TrangThai'] ?? 'Chờ duyệt';
            $ngaydangky = $_POST['NgayDangKy'];

            if ($this->model->checkma($manp)) {
                echo "<script>alert('❌ Mã nghỉ phép đã tồn tại!');
                      window.history.back();</script>";
                exit;
            }

            if ($this->model->insertNghiPhep($manp, $manv, $tungay, $denngay, $lydo, $trangthai, $ngaydangky)) {
                echo "<script>alert('✅ Thêm nghỉ phép thành công!');
                      window.location='index.php?controller=nghiphep&action=index';</script>";
            } else {
                echo "<script>alert('❌ Lỗi khi thêm dữ liệu!');
                      window.history.back();</script>";
            }
        }
    }

    // Form sửa nghỉ phép
    public function sua() {
        $manp = $_GET['id'] ?? '';
        if (!$manp) { echo "Không tìm thấy bản ghi."; exit; }

        $result = $this->model->getNghiPhepById($manp);
        if (!$result || $result->num_rows == 0) {
            echo "Không tìm thấy bản ghi."; exit;
        }

        $row = $result->fetch_assoc();
        $nhanvien = $this->model->getAllNhanVien();

        include './views/nghiphep/sua.php';
    }

    // Lưu sửa nghỉ phép
    public function luuSua() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $manp = $_POST['MaNP'];
            $manv = $_POST['MaNV'];
            $tungay = $_POST['TuNgay'];
            $denngay = $_POST['DenNgay'];
            $lydo = $_POST['LyDo'];
            $trangthai = $_POST['TrangThai'];

            if ($this->model->updateNghiPhep($manp, $manv, $tungay, $denngay, $lydo, $trangthai)) {
                echo "<script>alert('✅ Cập nhật nghỉ phép thành công!');
                      window.location='index.php?controller=nghiphep&action=index';</script>";
            } else {
                echo "<script>alert('❌ Lỗi khi cập nhật!');
                      window.history.back();</script>";
            }
        }
    }

    public function duyet() {
    if (isset($_GET['id'])) {
        $id = $_GET['id'];

        if ($this->model->duyet($id)) {
            echo "<script>
                    alert('✅ Đã duyệt đơn nghỉ phép!');
                    window.location='index.php?controller=nghiphep&action=index';
                  </script>";
        } else {
            echo "<script>
                    alert('❌ Lỗi khi duyệt!');
                    window.history.back();
                  </script>";
        }
    }
}

    public function tuchoi() {
        if (isset($_GET['id'])) {
            $id = $_GET['id'];

            if ($this->model->tuchoi($id)) {
                echo "<script>
                        alert('❌ Đã từ chối đơn nghỉ phép!');
                        window.location='index.php?controller=nghiphep&action=index';
                    </script>";
            } else {
                echo "<script>
                        alert('❌ Lỗi khi từ chối!');
                        window.history.back();
                    </script>";
            }
        }
    }

}
?>
