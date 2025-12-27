<?php
// controllers/ChucVuController.php

require_once 'models/ChucVuModel.php';

class ChucVuController {
    private $model;
    
    public function __construct($conn) {
        $this->model = new ChucVuModel($conn);
    }
    
    // 1. ACTION INDEX (Liệt kê và Tìm kiếm)
    public function index() {
        $keyword = $_GET['search'] ?? ''; 
        $danhSachChucVu = $this->model->getList($keyword); 
        
        require_once 'views/chucvu/index.php';
    }

    // 2. ACTION ADD (Hiển thị form và xử lý POST)
    public function add() {
        $message = '';
        $maCV = $_POST['MaCV'] ?? '';
        $tenChucVu = $_POST['TenChucVu'] ?? '';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            
            if ($this->model->checkMaCV($maCV)) {
                $message = "Lỗi: Mã chức vụ $maCV đã tồn tại.";
            } else {
                if ($this->model->add($maCV, $tenChucVu)) {
                    $message = "Thêm chức vụ $tenChucVu thành công!";
                    header('Location: index.php?controller=chucvu&action=index&msg=' . urlencode($message));
                    exit;
                } else {
                    $message = "Lỗi khi thêm chức vụ: " . $this->model->conn->error;
                }
            }
        }

        require_once 'views/chucvu/add.php';
    }

    // 3. ACTION EDIT (Hiển thị form Sửa và xử lý POST)
    public function edit() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: index.php?controller=chucvu&action=index');
            exit;
        }
        $chucVuChiTiet = $this->model->getById($id);
        
        if (!$chucVuChiTiet) {
            $message = "Không tìm thấy Chức vụ cần sửa!";
            header('Location: index.php?controller=chucvu&action=index&msg=' . urlencode($message));
            exit;
        }

        $maCV = $chucVuChiTiet['MaCV']; // MaCV không thay đổi
        $tenChucVu = $chucVuChiTiet['TenChucVu']; 
        $message = '';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $tenChucVuMoi = $_POST['TenChucVu'];

            if ($this->model->update($id, $tenChucVuMoi)) {
                $message = "Cập nhật Chức vụ {$id} thành công!";
                header('Location: index.php?controller=chucvu&action=index&msg=' . urlencode($message));
                exit;
            } else {
                $message = "Lỗi khi cập nhật Chức vụ: " . $this->model->conn->error;
                // Nếu lỗi, giữ lại tên chức vụ người dùng vừa nhập
                $tenChucVu = $tenChucVuMoi; 
            }
        }

        require_once 'views/chucvu/edit.php';
    }
    
    // 4. ACTION DELETE (Xóa)
    public function delete() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $message = "Không có Mã Chức vụ để xóa!";
        } 
        // Kiểm tra xem có nhân viên nào đang sử dụng chức vụ này không (TÙY CHỌN NÂNG CAO)
        // Hiện tại, chúng ta tạm thời cho phép xóa. Nếu có lỗi khóa ngoại sẽ báo lỗi.
        
        elseif ($this->model->delete($id)) {
            $message = "Xóa Chức vụ Mã {$id} thành công!";
        } else {
            // Lỗi có thể là do khóa ngoại (có nhân viên đang dùng chức vụ này)
            $message = "Lỗi khi xóa Chức vụ Mã {$id}: " . $this->model->conn->error;
        }

        header('Location: index.php?controller=chucvu&action=index&msg=' . urlencode($message));
        exit;
    }
    public function exportExcel() {
        // Lấy toàn bộ danh sách chức vụ, không cần tìm kiếm (truyền chuỗi rỗng)
        $danhSachChucVu = $this->model->getList(''); 

        $filename = "Danh_sach_chuc_vu_" . date('Ymd_His') . ".xls";

        // Thiết lập header cho file Excel
        header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        
        // BOM UTF-8 để hiển thị tiếng Việt có dấu
        echo "\xEF\xBB\xBF"; 

        echo "<table border='1'>";
        
        // Header Bảng Excel
        echo "<tr style='background-color:#f2f2f2; font-weight:bold;'>
                <th>Mã Chức vụ</th>
                <th>Tên Chức vụ</th>
                <th>Số lượng Nhân viên</th>
              </tr>";

        // Vòng lặp in dữ liệu
        if (!empty($danhSachChucVu)) {
            foreach ($danhSachChucVu as $cv) {
                echo "<tr>
                        <td>{$cv['MaCV']}</td>
                        <td>{$cv['TenChucVu']}</td>
                        <td>{$cv['SoLuongNV']}</td>
                      </tr>";
            }
        }

        echo "</table>";
        exit;
    }
}
?>