<?php
// controllers/ChucVuController.php

require_once 'models/ChucVuModel.php';
require_once 'core/AuthMiddleware.php';
require_once 'core/RequestValidator.php';
require_once 'core/AppLogger.php';
require_once 'core/WebResponder.php';

class ChucVuController {
    private $model;
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
        $this->model = new ChucVuModel($conn);
    }

    /* =========================
       1. DANH SÁCH + TÌM KIẾM
    ========================== */
    public function index() {
        AuthMiddleware::check($this->conn, 'xem_chucvu');
        $quyen = $_SESSION['quyen'] ?? [];
        $keyword = trim($_GET['search'] ?? '');
        $danhSachChucVu = $this->model->getList($keyword);

        require 'views/chucvu/index.php';
    }

    /* =========================
       2. THÊM CHỨC VỤ
    ========================== */
    public function add() {
        AuthMiddleware::check($this->conn, 'them_chucvu');
        $quyen = $_SESSION['quyen'] ?? [];
        $message = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $validator = new RequestValidator($_POST);
            $tenChucVu = $validator->requiredString('TenChucVu', 'Tên chức vụ', 2, 120);
            $heSo      = $validator->optionalFloat('HeSoChucVu', 0);
            $phuCap    = $validator->optionalFloat('PhuCap', 0);

            $heSo = $heSo ?? 0;
            $phuCap = $phuCap ?? 0;

            if (!$validator->isValid() || $heSo <= 0) {
                if ($heSo <= 0) {
                    $message = 'Hệ số phải lớn hơn 0.';
                } else {
                    $message = $validator->firstError();
                }
                AppLogger::warning('Validation failed in ChucVuController::add', ['errors' => $validator->allErrors()]);
                WebResponder::backWithMessage($message, 'error', 'index.php?controller=chucvu&action=add');
            }

            if ($this->model->add($tenChucVu, $heSo, $phuCap)) {
                WebResponder::redirectWithMessage('index.php?controller=chucvu&action=index', 'Thêm chức vụ thành công', 'success');
            }

            WebResponder::backWithMessage('Lỗi khi thêm chức vụ!', 'error', 'index.php?controller=chucvu&action=add');
        }

        require 'views/chucvu/add.php';
    }

    /* =========================
       3. SỬA CHỨC VỤ
    ========================== */
    public function edit() {
        AuthMiddleware::check($this->conn, 'sua_chucvu');
        $quyen = $_SESSION['quyen'] ?? [];
        $id = intval($_GET['id'] ?? 0);
        if ($id <= 0) {
            WebResponder::redirectWithMessage('index.php?controller=chucvu&action=index', 'Thiếu mã chức vụ', 'error');
        }

        $chucVu = $this->model->getById($id);
        if (!$chucVu) {
            WebResponder::redirectWithMessage('index.php?controller=chucvu&action=index', 'Không tìm thấy chức vụ', 'error');
        }

        $message = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $validator = new RequestValidator($_POST);
            $tenChucVu = $validator->requiredString('TenChucVu', 'Tên chức vụ', 2, 120);
            $heSo      = $validator->optionalFloat('HeSoChucVu', 0);
            $phuCap    = $validator->optionalFloat('PhuCap', 0);

            $heSo = $heSo ?? 0;
            $phuCap = $phuCap ?? 0;

            if (!$validator->isValid() || $heSo <= 0) {
                if ($heSo <= 0) {
                    $message = 'Hệ số phải lớn hơn 0.';
                } else {
                    $message = $validator->firstError();
                }
                AppLogger::warning('Validation failed in ChucVuController::edit', ['errors' => $validator->allErrors()]);
                WebResponder::backWithMessage($message, 'error', 'index.php?controller=chucvu&action=edit&id=' . $id);
            }

            if ($this->model->update($id, $tenChucVu, $heSo, $phuCap)) {
                WebResponder::redirectWithMessage('index.php?controller=chucvu&action=index', 'Cập nhật thành công', 'success');
            }

            WebResponder::backWithMessage('Lỗi khi cập nhật!', 'error', 'index.php?controller=chucvu&action=edit&id=' . $id);
        }

        require 'views/chucvu/edit.php';
    }

    /* =========================
       4. XÓA CHỨC VỤ
    ========================== */
    public function delete() {
        AuthMiddleware::check($this->conn, 'xoa_chucvu');
        $quyen = $_SESSION['quyen'] ?? [];
        $id = intval($_GET['id'] ?? 0);

        if ($id <= 0) {
            WebResponder::redirectWithMessage('index.php?controller=chucvu&action=index', 'Thiếu mã chức vụ', 'error');
        }

        if ($this->model->delete($id)) {
            WebResponder::redirectWithMessage('index.php?controller=chucvu&action=index', 'Xóa thành công', 'success');
        } else {
            WebResponder::redirectWithMessage('index.php?controller=chucvu&action=index', 'Không thể xóa (có nhân viên đang sử dụng)', 'error');
        }
    }

    /* =========================
       5. XUẤT EXCEL
    ========================== */
    public function exportExcel() {
        AuthMiddleware::check($this->conn, 'xuat_excel_chucvu');
        $quyen = $_SESSION['quyen'] ?? [];
        $danhSachChucVu = $this->model->getList('');

        $filename = "Danh_sach_chuc_vu_" . date('Ymd_His') . ".xls";

        header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Pragma: no-cache");
        header("Expires: 0");

        echo "\xEF\xBB\xBF"; // UTF-8 BOM

        echo "<table border='1'>";
        echo "<tr style='font-weight:bold; background:#f2f2f2'>
                <th>Mã</th>
                <th>Tên chức vụ</th>
                <th>Hệ số</th>
                <th>Phụ cấp</th>
                <th>Số nhân viên</th>
              </tr>";

        foreach ($danhSachChucVu as $cv) {
            echo "<tr>
                    <td>{$cv['MaCV']}</td>
                    <td>{$cv['TenChucVu']}</td>
                    <td>{$cv['HeSoChucVu']}</td>
                    <td>" . number_format($cv['PhuCap'], 0, ',', '.') . "</td>
                    <td>{$cv['SoLuongNV']}</td>
                  </tr>";
        }

        echo "</table>";
        exit;
    }
}
