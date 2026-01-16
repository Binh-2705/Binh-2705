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
    public function import() {
    include 'views/phongban/import.php';
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
    public function exportExcel() {
    $result = $this->model->getAllPhongBan();

    $filename = "Danh_sach_phong_ban_" . date('Ymd') . ".xls";

    header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
    header("Content-Disposition: attachment; filename=\"$filename\"");
    echo "\xEF\xBB\xBF"; // BOM UTF-8

    echo "<table border='1'>";
    echo "<tr style='background-color:#f2f2f2; font-weight:bold;'>
            <th>Mã PB</th>
            <th>Tên phòng ban</th>
            <th>Mô tả</th>
            
          </tr>";

    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>
                <td>{$row['MaPB']}</td>
                <td>{$row['TenPB']}</td>
                <td>{$row['MoTa']}</td>
              </tr>";
    }

    echo "</table>";
    exit;
}
    public function docFile() {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {

        if (!isset($_FILES['filecsv']) || $_FILES['filecsv']['error'] != 0) {
            echo "<script>alert('❌ Vui lòng chọn file CSV'); window.history.back();</script>";
            exit;
        }

        $fileTmp = $_FILES['filecsv']['tmp_name'];
        $handle = fopen($fileTmp, "r");

        if ($handle === false) {
            echo "<script>alert('❌ Không thể đọc file'); window.history.back();</script>";
            exit;
        }

        $count = 0;
        fgetcsv($handle); // bỏ dòng tiêu đề

        while (($data = fgetcsv($handle, 1000, ",")) !== false) {
            $mapb = trim($data[0]);
            $tenpb = trim($data[1]);
            $mota  = trim($data[2]);

            if ($mapb != "" && !$this->model->checkma($mapb)) {
                $this->model->insertPhongBan($mapb, $tenpb, $mota);
                $count++;
            }
        }

        fclose($handle);

        echo "<script>
                alert('✅ Đã import $count phòng ban!');
                window.location='index.php?controller=phongban&action=index';
              </script>";
    }
}

   
}
