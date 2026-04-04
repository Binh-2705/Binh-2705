<?php
require_once 'models/PhongBanModel.php';
require_once 'core/AuthMiddleware.php';
require_once 'core/RequestValidator.php';
require_once 'core/AppLogger.php';
require_once 'core/WebResponder.php';

class PhongBanController {
    private $model;
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
        $this->model = new PhongBanModel($conn);
    }

    /* ======================
       DANH SÁCH PHÒNG BAN
    ====================== */
    public function index() {
            AuthMiddleware::check($this->conn, 'xem_phongban');
            $quyen = $_SESSION['quyen'] ?? [];
        $phongbans = $this->model->getAllPhongBan();
        include 'views/phongban/index.php';
    }

    /* ======================
       FORM THÊM
    ====================== */
    public function them() {
        AuthMiddleware::check($this->conn, 'them_phongban');
        $quyen = $_SESSION['quyen'] ?? [];
        include 'views/phongban/them.php';
    }

    /* ======================
       LƯU THÊM
    ====================== */
    public function luuThem() {
        AuthMiddleware::check($this->conn, 'them_phongban');
        $quyen = $_SESSION['quyen'] ?? [];
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $validator = new RequestValidator($_POST);
            $tenpb = $validator->requiredString('tenpb', 'Tên phòng ban', 2, 120);
            $mota  = $validator->optionalString('mota', 1000);

            if (!$validator->isValid()) {
                AppLogger::warning('Validation failed in PhongBanController::luuThem', ['errors' => $validator->allErrors()]);
                WebResponder::backWithMessage($validator->firstError(), 'error', 'index.php?controller=phongban&action=them');
            }

            if ($this->model->insertPhongBan($tenpb, $mota)) {
                WebResponder::redirectWithMessage('index.php?controller=phongban&action=index', 'Thêm phòng ban thành công!', 'success');
            } else {
                WebResponder::backWithMessage('Lỗi khi thêm phòng ban!', 'error', 'index.php?controller=phongban&action=them');
            }
        }
    }

    /* ======================
       TÌM KIẾM
    ====================== */
    public function timkiem() {
        AuthMiddleware::check($this->conn, 'timkiem_phongban');
        $quyen = $_SESSION['quyen'] ?? [];
        $keyword = $_GET['keyword'] ?? '';
        $phongbans = $this->model->searchPhongBan($keyword);
        include 'views/phongban/index.php';
    }

    /* ======================
       FORM SỬA
    ====================== */
    public function sua() {
        AuthMiddleware::check($this->conn, 'sua_phongban');
        $quyen = $_SESSION['quyen'] ?? [];
        $mapb = (int)($_GET['mapb'] ?? 0);
        if ($mapb <= 0) {
            WebResponder::redirectWithMessage('index.php?controller=phongban&action=index', 'Thiếu mã phòng ban!', 'error');
        }

        $phongban = $this->model->getPhongBanById($mapb);

        if (!$phongban) {
            WebResponder::redirectWithMessage('index.php?controller=phongban&action=index', 'Không tìm thấy phòng ban!', 'error');
        }

        include 'views/phongban/sua.php';
    }

    /* ======================
       LƯU SỬA
    ====================== */
    public function luuSua() {
        AuthMiddleware::check($this->conn, 'sua_phongban');
        $quyen = $_SESSION['quyen'] ?? [];
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $validator = new RequestValidator($_POST);
            $mapb  = $validator->requiredInt('mapb', 'Mã phòng ban', 1);
            $tenpb = $validator->requiredString('tenpb', 'Tên phòng ban', 2, 120);
            $mota  = $validator->optionalString('mota', 1000);

            if (!$validator->isValid()) {
                AppLogger::warning('Validation failed in PhongBanController::luuSua', ['errors' => $validator->allErrors()]);
                WebResponder::backWithMessage($validator->firstError(), 'error', 'index.php?controller=phongban&action=index');
            }

            if ($this->model->updatePhongBan($mapb, $tenpb, $mota)) {
                WebResponder::redirectWithMessage('index.php?controller=phongban&action=index', 'Cập nhật thành công!', 'success');
            } else {
                WebResponder::backWithMessage('Lỗi cập nhật!', 'error', 'index.php?controller=phongban&action=index');
            }
        }
    }

    /* ======================
       XÓA
    ====================== */
    public function xoa() {
        AuthMiddleware::check($this->conn, 'xoa_phongban');
        $quyen = $_SESSION['quyen'] ?? [];
        $mapb = (int)($_GET['mapb'] ?? 0);
        if ($mapb <= 0) {
            WebResponder::redirectWithMessage('index.php?controller=phongban&action=index', 'Thiếu mã phòng ban!', 'error');
        }

        if ($this->model->deletePhongBan($mapb)) {
            WebResponder::redirectWithMessage('index.php?controller=phongban&action=index', 'Xóa phòng ban thành công!', 'success');
        } else {
            WebResponder::redirectWithMessage('index.php?controller=phongban&action=index', 'Không thể xóa!', 'error');
        }
    }

    /* ======================
       XUẤT EXCEL
    ====================== */
    public function exportExcel() {
            AuthMiddleware::check($this->conn, 'xuat_excel_phongban');
            $quyen = $_SESSION['quyen'] ?? [];
        $result = $this->model->getAllPhongBan();
        $filename = "Danh_sach_phong_ban_" . date('Ymd') . ".xls";

        header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        echo "\xEF\xBB\xBF";

        echo "<table border='1'>
                <tr>
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

    /* ======================
       IMPORT CSV (KHÔNG MaPB)
    ====================== */
    public function docFile() {
        AuthMiddleware::check($this->conn, 'import_csv_phongban');
        $quyen = $_SESSION['quyen'] ?? [];
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            if (!isset($_FILES['filecsv']) || $_FILES['filecsv']['error'] != 0) {
                WebResponder::backWithMessage('Chưa chọn file CSV', 'error', 'index.php?controller=phongban&action=index');
            }

            $ext = strtolower(pathinfo($_FILES['filecsv']['name'], PATHINFO_EXTENSION));
            if ($ext !== 'csv') {
                WebResponder::backWithMessage('Chỉ hỗ trợ file .csv', 'error', 'index.php?controller=phongban&action=index');
            }

            $handle = fopen($_FILES['filecsv']['tmp_name'], 'r');
            fgetcsv($handle); // bỏ header

            $count = 0;
            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                $tenpb = trim($data[0]);
                $mota  = trim($data[1]);

                if ($tenpb != '') {
                    $this->model->insertPhongBan($tenpb, $mota);
                    $count++;
                }
            }

            fclose($handle);
                        WebResponder::redirectWithMessage('index.php?controller=phongban&action=index', "Đã import $count phòng ban!", 'success');
        }
    }
}
