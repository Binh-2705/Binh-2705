<?php
require_once 'models/HoSoCaNhanModel.php';
require_once 'core/AuthMiddleware.php';
class HoSoCaNhanController {
    private $model;
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
         
        $this->model = new HoSoCaNhanModel($conn);
    }

    public function index() {
        AuthMiddleware::check($this->conn, 'xem_hoso');
        $quyen = $_SESSION['quyen'] ?? [];

        // Nhân viên chỉ được xem hồ sơ của chính mình
        $account = $_SESSION['taikhoan'] ?? [];
        if (strtolower(trim((string)($account['VaiTro'] ?? ''))) === 'nhanvien') {
            $maNV = $this->getCurrentEmployeeId();
            $hoso = $maNV > 0 ? $this->model->getByMaNV($maNV) : null;
            if ($hoso && !empty($hoso['MaHoSo'])) {
                header('Location: index.php?controller=hosocanhan&action=xem&id=' . (int)$hoso['MaHoSo']);
            } else {
                header('Location: index.php?controller=hosocanhan&action=nhapnhanh');
            }
            exit;
        }

        $page = max(1, (int)($_GET['page'] ?? 1));
        $keyword = trim((string)($_GET['keyword'] ?? ''));
        $perPage = 10;
        $totalItems = $keyword !== ''
            ? $this->model->countSearch($keyword)
            : $this->model->countAll();
        $totalPages = max(1, (int)ceil($totalItems / $perPage));
        if ($page > $totalPages) {
            $page = $totalPages;
        }
        $hosos = $keyword !== ''
            ? $this->model->searchPage($keyword, $page, $perPage)
            : $this->model->getPage($page, $perPage);
        include 'views/hosocanhan/index.php';
    }

    public function nhapnhanh() {
        if (empty($_SESSION['taikhoan'])) {
            header('Location: index.php?controller=dangnhap&action=login');
            exit;
        }

        $maNV = $this->getCurrentEmployeeId();
        if ($maNV <= 0) {
            $_SESSION['error'] = 'Tài khoản của bạn chưa được gán mã nhân viên.';
            header('Location: index.php');
            exit;
        }

        $hoso = $this->model->getByMaNV($maNV);
        $nhanvienInfo = $this->model->getThongTinNhanVien($maNV);
        $nvInfo = $nhanvienInfo ? mysqli_fetch_assoc($nhanvienInfo) : null;
        include 'views/hosocanhan/nhapnhanh.php';
    }

    public function luunhapnhanh() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controller=hosocanhan&action=nhapnhanh');
            exit;
        }

        if (empty($_SESSION['taikhoan'])) {
            header('Location: index.php?controller=dangnhap&action=login');
            exit;
        }

        $maNV = $this->getCurrentEmployeeId();
        if ($maNV <= 0) {
            $_SESSION['error'] = 'Tài khoản chưa được gán mã nhân viên, không thể lưu hồ sơ.';
            header('Location: index.php?controller=hosocanhan&action=nhapnhanh');
            exit;
        }

        $data = [
            'CCCD' => trim((string)($_POST['CCCD'] ?? '')),
            'NoiCap' => trim((string)($_POST['NoiCap'] ?? '')),
            'NgayCap' => trim((string)($_POST['NgayCap'] ?? '')),
            'DiaChi' => trim((string)($_POST['DiaChi'] ?? '')),
            'DanToc' => trim((string)($_POST['DanToc'] ?? '')),
            'TonGiao' => trim((string)($_POST['TonGiao'] ?? '')),
            'TrinhDo' => trim((string)($_POST['TrinhDo'] ?? '')),
            'ChuyenMon' => trim((string)($_POST['ChuyenMon'] ?? '')),
            'TrangThaiHonNhan' => trim((string)($_POST['TrangThaiHonNhan'] ?? 'Độc thân')),
        ];

        if ($data['CCCD'] === '' || !preg_match('/^\d{12}$/', $data['CCCD'])) {
            $_SESSION['error'] = 'CCCD phải gồm đúng 12 chữ số.';
            header('Location: index.php?controller=hosocanhan&action=nhapnhanh');
            exit;
        }

        $validHonNhan = ['Độc thân', 'Đã kết hôn'];
        if (!in_array($data['TrangThaiHonNhan'], $validHonNhan, true)) {
            $_SESSION['error'] = 'Trạng thái hôn nhân không hợp lệ.';
            header('Location: index.php?controller=hosocanhan&action=nhapnhanh');
            exit;
        }

        $existing = $this->model->getByMaNV($maNV);
        $role = strtolower(trim((string)($_SESSION['taikhoan']['VaiTro'] ?? '')));
        $isAdminOrManager = in_array($role, ['admin', 'quanly'], true);

        if (!$existing) {
            $nhanvienInfo = $this->model->getThongTinNhanVien($maNV);
            $nvInfo = $nhanvienInfo ? mysqli_fetch_assoc($nhanvienInfo) : [];
            $insertPayload = $data;
            $insertPayload['NgayVaoLam'] = date('Y-m-d');
            $insertPayload['MaPB'] = (int)($nvInfo['MaPB'] ?? 0);
            $insertPayload['MaCV'] = (int)($nvInfo['MaCV'] ?? 0);
            $insertPayload['Anh'] = '';

            if ($this->model->themHoSoChoNhanVien($maNV, $insertPayload)) {
                $_SESSION['success'] = 'Đã lưu hồ sơ nhanh thành công.';
            } else {
                $_SESSION['error'] = 'Không thể lưu hồ sơ. Vui lòng thử lại.';
            }

            header('Location: index.php?controller=hosocanhan&action=nhapnhanh');
            exit;
        }

        if ($isAdminOrManager) {
            if ($this->model->capNhatHoSoByMaNV($maNV, $data)) {
                $_SESSION['success'] = 'Đã cập nhật trực tiếp hồ sơ của bạn.';
            } else {
                $_SESSION['error'] = 'Không thể cập nhật hồ sơ lúc này.';
            }

            header('Location: index.php?controller=hosocanhan&action=nhapnhanh');
            exit;
        }

        $requestedBy = (int)($_SESSION['MaTK'] ?? 0);
        $requestedRole = (string)($_SESSION['taikhoan']['VaiTro'] ?? 'NhanVien');
        $note = trim((string)($_POST['YeuCauGhiChu'] ?? ''));
        $saved = $this->model->savePendingUpdateRequest($maNV, $requestedBy, $requestedRole, $data, $note);

        if ($saved) {
            $_SESSION['success'] = 'Bạn đã gửi yêu cầu chỉnh sửa. Chờ Admin/Quản lý duyệt.';
        } else {
            $_SESSION['error'] = 'Không thể gửi yêu cầu chỉnh sửa lúc này.';
        }

        header('Location: index.php?controller=hosocanhan&action=nhapnhanh');
        exit;
    }

    public function duyetyeucau() {
        $this->requireAdminOrManager();
        $requests = $this->model->getUpdateRequests('pending');
        include 'views/hosocanhan/duyetyc.php';
    }

    public function xulyyeucau() {
        $this->requireAdminOrManager();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controller=hosocanhan&action=duyetyeucau');
            exit;
        }

        $id = (int)($_POST['request_id'] ?? 0);
        $decision = trim((string)($_POST['decision'] ?? ''));
        $reviewNote = trim((string)($_POST['review_note'] ?? ''));
        if ($id <= 0 || !in_array($decision, ['approve', 'reject'], true)) {
            $_SESSION['error'] = 'Yêu cầu xử lý không hợp lệ.';
            header('Location: index.php?controller=hosocanhan&action=duyetyeucau');
            exit;
        }

        $request = $this->model->getUpdateRequestById($id);
        if (!$request || (string)$request['status_name'] !== 'pending') {
            $_SESSION['error'] = 'Yêu cầu không tồn tại hoặc đã xử lý.';
            header('Location: index.php?controller=hosocanhan&action=duyetyeucau');
            exit;
        }

        $reviewedBy = (int)($_SESSION['MaTK'] ?? 0);
        if ($decision === 'approve') {
            $payload = json_decode((string)($request['payload_json'] ?? '{}'), true);
            if (!is_array($payload)) {
                $payload = [];
            }

            $updated = $this->model->capNhatHoSoByMaNV((int)$request['MaNV'], $payload);
            if (!$updated) {
                $_SESSION['error'] = 'Không thể áp dụng cập nhật hồ sơ.';
                header('Location: index.php?controller=hosocanhan&action=duyetyeucau');
                exit;
            }

            $this->model->resolveUpdateRequest($id, 'approved', $reviewedBy, $reviewNote);
            $_SESSION['success'] = 'Đã duyệt và cập nhật hồ sơ thành công.';
            header('Location: index.php?controller=hosocanhan&action=duyetyeucau');
            exit;
        }

        $this->model->resolveUpdateRequest($id, 'rejected', $reviewedBy, $reviewNote);
        $_SESSION['success'] = 'Đã từ chối yêu cầu chỉnh sửa.';
        header('Location: index.php?controller=hosocanhan&action=duyetyeucau');
        exit;
    }

     public function them(){
AuthMiddleware::check($this->conn, 'them_hoso');
$quyen = $_SESSION['quyen'] ?? [];
    $nhanvien = $this->model->getNhanVien();
    $phongban = $this->model->getPhongBan();
    $chucvu = $this->model->getChucVu();

    if($_SERVER['REQUEST_METHOD'] == 'POST'){

        // VALIDATE CCCD
        if(strlen($_POST['CCCD']) != 12){
            $_SESSION['error'] = "CCCD phải đúng 12 số!";
            header("Location: index.php?controller=hosocanhan&action=them");
            exit;
        }

        // UPLOAD ẢNH
        $anh = '';
        if(isset($_FILES['Anh']) && $_FILES['Anh']['error'] == 0){
            $targetDir = "uploads/";
            if(!is_dir($targetDir)) mkdir($targetDir);

            $anh = time() . "_" . $_FILES['Anh']['name'];
            move_uploaded_file($_FILES['Anh']['tmp_name'], $targetDir . $anh);
        }

        $data = [
            'MaNV' => $_POST['MaNV'],
            'CCCD' => $_POST['CCCD'],
            'NoiCap' => $_POST['NoiCap'],
            'NgayCap' => $_POST['NgayCap'],
            'DiaChi' => $_POST['DiaChi'],
            'DanToc' => $_POST['DanToc'],
            'TonGiao' => $_POST['TonGiao'],
            'TrinhDo' => $_POST['TrinhDo'],
            'ChuyenMon' => $_POST['ChuyenMon'],
            'NgayVaoLam' => $_POST['NgayVaoLam'],
            'MaPB' => $_POST['MaPB'],
            'MaCV' => $_POST['MaCV'],
            'TrangThaiHonNhan' => $_POST['TrangThaiHonNhan'],
            'Anh' => $anh
        ];

        $this->model->themHoSo($data);

        header("Location: index.php?controller=hosocanhan&action=index");
        exit;
    }

    require "views/hosocanhan/them.php";
}

    /* ================= SỬA ================= */

    public function sua(){
AuthMiddleware::check($this->conn, 'sua_hoso');
$quyen = $_SESSION['quyen'] ?? [];

        $id = $_GET['id'];

        $hoso = $this->model->getById($id);

        $nhanvien = $this->model->getNhanVien();
        $phongban = $this->model->getPhongBan();
        $chucvu = $this->model->getChucVu();

        if($_SERVER['REQUEST_METHOD'] == 'POST'){

            $data = [
                'CCCD' => $_POST['CCCD'],
                'NoiCap' => $_POST['NoiCap'],
                'NgayCap' => $_POST['NgayCap'],
                'DiaChi' => $_POST['DiaChi'],
                'DanToc' => $_POST['DanToc'],
                'TonGiao' => $_POST['TonGiao'],
                'TrinhDo' => $_POST['TrinhDo'],
                'ChuyenMon' => $_POST['ChuyenMon'],
                'NgayVaoLam' => $_POST['NgayVaoLam'],
                'MaPB' => $_POST['MaPB'],
                'MaCV' => $_POST['MaCV'],
                'TrangThaiHonNhan' => $_POST['TrangThaiHonNhan']
            ];

            $this->model->capNhatHoSo($id,$data);

            header("Location: index.php?controller=hosocanhan&action=index");
            exit;
        }

        require "views/hosocanhan/sua.php";
    }

    /* ================= XÓA ================= */

    public function xoa(){
AuthMiddleware::check($this->conn, 'xoa_hoso');
$quyen = $_SESSION['quyen'] ?? [];

        $id = $_GET['id'];

        $this->model->xoaHoSo($id);

        header("Location: index.php?controller=hosocanhan&action=index");
        exit;
    }
 public function xem(){
    $id = $_GET['id'];

    $result = $this->model->getById($id);

   $row = $this->model->getById($id);

    require "views/hosocanhan/xem.php";
}
public function getNhanVienInfo(){

    $maNV = $_GET['MaNV'];

    $result = $this->model->getThongTinNhanVien($maNV);

    echo json_encode(mysqli_fetch_assoc($result));
}

private function getCurrentEmployeeId(): int {
    $account = $_SESSION['taikhoan'] ?? [];
    if (!empty($account['MaNVRef'])) {
        return (int)$account['MaNVRef'];
    }

    $maNV = trim((string)($account['MaNV'] ?? ''));
    if ($maNV !== '' && preg_match('/(\d+)/', $maNV, $matches)) {
        return (int)$matches[1];
    }

    return 0;
}

private function requireAdminOrManager(): void {
    if (empty($_SESSION['taikhoan'])) {
        header('Location: index.php?controller=dangnhap&action=login');
        exit;
    }

    $role = strtolower(trim((string)($_SESSION['taikhoan']['VaiTro'] ?? '')));
    if (!in_array($role, ['admin', 'quanly'], true)) {
        $_SESSION['error'] = 'Bạn không có quyền duyệt yêu cầu chỉnh sửa hồ sơ.';
        header('Location: index.php');
        exit;
    }
}

}
