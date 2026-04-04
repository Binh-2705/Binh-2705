<?php
require_once 'models/NhanVienModel.php';
require_once 'core/AuthMiddleware.php';
require_once 'core/RequestValidator.php';
require_once 'core/AppLogger.php';

class NhanVienController {
    private $model;
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
        $this->model = new NhanVienModel($conn);
    }

    /* ================== DANH SÁCH ================== */
    public function index() {
         AuthMiddleware::check($this->conn, 'xem_nhanvien');
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 10;
        $totalItems = $this->model->countAll();
        $totalPages = max(1, (int)ceil($totalItems / $perPage));
        if ($page > $totalPages) {
            $page = $totalPages;
        }

        $nhanviens = $this->model->getPage($page, $perPage);
        $sttStart = ($page - 1) * $perPage + 1;
        $quyen = $_SESSION['quyen'] ?? [];
        require 'views/nhanvien/index.php';
    }

    /* ================== THÊM (ĐÃ SỬA) ================== */
    public function them() {
        // Lấy Ngạch thay vì lấy Bậc như cũ
         AuthMiddleware::check($this->conn, 'them_nhanvien');
        $dsNgach = $this->model->getAllNgachLuong(); 
        $quyen = $_SESSION['quyen'] ?? [];
        require 'views/nhanvien/them.php';
    }

    public function luuThem() {
        AuthMiddleware::check($this->conn, 'them_nhanvien');
        $quyen = $_SESSION['quyen'] ?? [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $validator = new RequestValidator($_POST);
            $data = [
                'HoTen'     => $validator->requiredString('HoTen', 'Họ tên', 2, 120),
                'GioiTinh'  => $validator->in('GioiTinh', 'Giới tính', ['Nam', 'Nữ']),
                'NgaySinh'  => $validator->requiredDate('NgaySinh', 'Ngày sinh'),
                'Email'     => $validator->optionalEmail('Email', 'Email', 150),
                'DienThoai' => $validator->optionalPattern('DienThoai', 'Điện thoại', '/^\d{9,11}$/'),
                'TrangThai' => $validator->in('TrangThai', 'Trạng thái', ['Đang làm', 'Nghỉ']),
                'MaBac'     => $validator->requiredInt('MaBac', 'Bậc lương', 1),
                'MaHS'      => 0,
            ];

            if (!$validator->isValid()) {
                $_SESSION['error'] = $validator->firstError();
                AppLogger::warning('Validation failed in NhanVienController::luuThem', ['errors' => $validator->allErrors()]);
                header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'index.php?controller=nhanvien&action=them'));
                exit;
            }

            if ($this->model->insert($data)) {
                $_SESSION['success'] = "Thêm nhân viên thành công!";
                header("Location: index.php?controller=nhanvien&action=index");
                exit;
            }

            $_SESSION['error'] = "Lỗi: Không thể lưu dữ liệu.";
            header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'index.php?controller=nhanvien&action=them'));
            exit;
        }
    }

    /* ================== SỬA (ĐÃ SỬA) ================== */
    public function sua() {
        AuthMiddleware::check($this->conn, 'sua_nhanvien');
        $quyen = $_SESSION['quyen'] ?? [];
    if (!isset($_GET['manv'])) {
            $_SESSION['error'] = "Thiếu mã nhân viên";
            header("Location: index.php?controller=nhanvien&action=index");
            exit;
    }

    $maNV = (int)$_GET['manv'];

    $nhanvien = $this->model->getById($maNV);

    if (!$nhanvien) {
        $_SESSION['error'] = "Không tìm thấy nhân viên";
        header("Location: index.php?controller=nhanvien&action=index");
        exit;
    }

    // Lấy danh sách ngạch
    $dsNgach = $this->model->getAllNgachLuong();

    // Lấy ngạch hiện tại từ bậc lương của nhân viên
    $ngachHienTai = $this->model->getNgachByBac($nhanvien['MaBac']);

    // ⚠️ PHẢI gán biến này (view đang dùng)
    $maNgachHienTai = $ngachHienTai['MaNgach'] ?? null;

    // Lấy danh sách bậc theo ngạch hiện tại
    $bacluongs = $this->model->getBacByNgach($maNgachHienTai);

    require 'views/nhanvien/sua.php';
}
   public function luuSua() {
    AuthMiddleware::check($this->conn, 'sua_nhanvien');
    $quyen = $_SESSION['quyen'] ?? [];
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $validator = new RequestValidator($_POST);
        $maNV = $validator->requiredInt('MaNV', 'Mã nhân viên', 1);
        $maBacMoi = $validator->requiredInt('MaBac', 'Bậc lương', 1);

        $data = [
            'MaNV'      => $maNV,
            'HoTen'     => $validator->requiredString('HoTen', 'Họ tên', 2, 120),
            'GioiTinh'  => $validator->in('GioiTinh', 'Giới tính', ['Nam', 'Nữ']),
            'NgaySinh'  => $validator->requiredDate('NgaySinh', 'Ngày sinh'),
            'Email'     => $validator->optionalEmail('Email', 'Email', 150),
            'DienThoai' => $validator->optionalPattern('DienThoai', 'Điện thoại', '/^\d{9,11}$/'),
            'TrangThai' => $validator->in('TrangThai', 'Trạng thái', ['Đang làm', 'Nghỉ']),
            'MaBac'     => $maBacMoi,
        ];

        if (!$validator->isValid()) {
            $_SESSION['error'] = $validator->firstError();
            AppLogger::warning('Validation failed in NhanVienController::luuSua', ['errors' => $validator->allErrors()]);
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'index.php?controller=nhanvien&action=index'));
            exit;
        }

        // Lấy dữ liệu cũ
        $nvCu = $this->model->getById($maNV);
        if (!$nvCu) {
            $_SESSION['error'] = "Không tìm thấy nhân viên";
            header("Location: index.php?controller=nhanvien&action=index");
            exit;
        }
        $maBacCu = (int)$nvCu['MaBac'];

        /* =============================
           🚨 KIỂM TRA HỢP ĐỒNG HIỆU LỰC
        ============================== */

        $hdHienTai = $this->model->getHopDongConHieuLuc($maNV);

        // Nếu đang đổi bậc mà KHÔNG có hợp đồng -> chặn
        if ($maBacCu !== $maBacMoi && !$hdHienTai) {
            $_SESSION['error'] =
                "❌ Không thể thay đổi bậc lương vì nhân viên chưa có hợp đồng còn hiệu lực!";
            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit;
        }

        /* =============================
           CẬP NHẬT NHÂN VIÊN
        ============================== */

        $data['MaHS'] = (int)($nvCu['MaHS'] ?? 0);

        if ($this->model->update($data)) {

            /* =============================
               GHI LỊCH SỬ LƯƠNG (NẾU ĐỔI BẬC)
            ============================== */
            if ($maBacCu !== $maBacMoi && $hdHienTai) {

                $maHD = $hdHienTai['MaHopDong'];

                $luongCu  = $this->model->getLuongThucTeByBac($maBacCu);
                $luongMoi = $this->model->getLuongThucTeByBac($maBacMoi);

                $this->model->insertLichSuLuong([
                    'MaHopDong'  => $maHD,
                    'LuongCu'    => $luongCu,
                    'LuongMoi'   => $luongMoi,
                    'NgayApDung' => date('Y-m-d'),
                    'LyDo'       => 'Điều chỉnh bậc lương từ hồ sơ nhân viên'
                ]);

                // Đồng bộ lại hợp đồng
                $this->model->updateMaBacHopDong($maHD, $maBacMoi);
            }

            $_SESSION['success'] = "Cập nhật nhân viên thành công!";
            header("Location: index.php?controller=nhanvien&action=index");
            exit;
        }

        $_SESSION['error'] = "Cập nhật nhân viên thất bại.";
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'index.php?controller=nhanvien&action=index'));
        exit;
    }
}

    /* ================== XÓA ================== */
public function xoa() {
    AuthMiddleware::check($this->conn, 'xoa_nhanvien');
    $quyen = $_SESSION['quyen'] ?? [];
    if (!isset($_GET['manv'])) {
        $_SESSION['error'] = "Thiếu mã nhân viên";
        header("Location: index.php?controller=nhanvien&action=index");
        exit;
    }

    $maNV = (int)$_GET['manv'];

    if ($this->model->delete($maNV)) {
        header("Location: index.php?controller=nhanvien&action=index");
        exit;
    } else {
        $_SESSION['error'] = "Xóa thất bại!";
        header("Location: index.php?controller=nhanvien&action=index");
        exit;
    }
}

    /* ================== AJAX (ĐÃ SỬA) ================== */
    public function getBacLuongByNgach() {
        $maNgach = $_GET['ma_ngach'] ?? '';
        if ($maNgach) {
            $dsBac = $this->model->getBacByNgach($maNgach);
            
            echo '<option value="">-- Chọn bậc lương --</option>';
            if ($dsBac && mysqli_num_rows($dsBac) > 0) {
                while ($row = mysqli_fetch_assoc($dsBac)) {
                    echo '<option value="' . $row['MaBac'] . '">' 
                         . $row['TenBac'] . ' (HS: ' . $row['HeSoLuong'] . ')' 
                         . '</option>';
                }
            } else {
                echo '<option value="">Chưa có bậc lương cho ngạch này</option>';
            }
        }
        exit; 
    }
   public function timkiem() {
    AuthMiddleware::check($this->conn, 'timkiem_nhanvien');
    $quyen = $_SESSION['quyen'] ?? [];
    $keyword = $_GET['keyword'] ?? '';

    if ($keyword != '') {
        $nhanviens = $this->model->search($keyword);
    } else {
        $nhanviens = $this->model->getAll();
    }

    require 'views/nhanvien/timkiem.php'; // phải gọi file này
}
}