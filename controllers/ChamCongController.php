
<?php
require_once './models/ChamCongModel.php';
require_once 'core/AuthMiddleware.php';
require_once 'core/RequestValidator.php';
require_once 'core/AppLogger.php';
require_once 'core/WebResponder.php';

class ChamCongController {
    private $model;
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
        $this->model = new ChamCongModel($conn);
    }

    private function currentEmployeeId(): ?int {
        $account = $_SESSION['taikhoan'] ?? [];
        $maNVRef = (int)($account['MaNVRef'] ?? 0);
        if ($maNVRef > 0) {
            return $maNVRef;
        }

        $maNVRaw = (string)($account['MaNV'] ?? '');
        $digits = preg_replace('/\D+/', '', $maNVRaw);
        if ($digits === '') {
            return null;
        }

        $maNV = (int)$digits;
        return $maNV > 0 ? $maNV : null;
    }

    private function isEmployeeRole(): bool {
        $account = $_SESSION['taikhoan'] ?? [];
        return strtolower(trim((string)($account['VaiTro'] ?? ''))) === 'nhanvien';
    }

    /* =====================================================
       TRANG CHÍNH = BÁO CÁO CÔNG THÁNG (KHÔNG PHẢI FORM CHẤM)
       ===================================================== */
   public function index() {
        AuthMiddleware::check($this->conn, 'xem_chamcong');
$quyen = $_SESSION['quyen'] ?? [];
    // ===== Lấy tháng năm (mặc định hiện tại)
    $thang = $_GET['thang'] ?? date('m');
    $nam   = $_GET['nam'] ?? date('Y');
    $mapb  = $_GET['mapb'] ?? '';

    // ===== TÍNH SỐ NGÀY TRONG THÁNG (QUAN TRỌNG)
    $soNgay = cal_days_in_month(CAL_GREGORIAN, $thang, $nam);

    // ===== Lấy danh sách phòng ban
    $phongBanList = $this->model->getAllPhongBan_Array();

    // ===== Lấy dữ liệu chấm công dạng ma trận
    $currentMaNV = $this->isEmployeeRole() ? $this->currentEmployeeId() : null;
    $data = $this->model->bangChamCongThang($thang, $nam, $currentMaNV);

    // ===== Load view
    include './views/chamcong/index.php';
}

    /* =====================================================
       FORM CHẤM CÔNG THEO NGÀY
       ===================================================== */
    public function them() {
        AuthMiddleware::check($this->conn, 'them_chamcong');
        $quyen = $_SESSION['quyen'] ?? [];
         $nhanvien = $this->model->getAllNhanVien();
        
        include './views/chamcong/them.php';
    }

    /* =====================================================
       LƯU CHẤM CÔNG (CHỈ LƯU DỮ LIỆU GỐC)
       ===================================================== */
    public function luu() {
        AuthMiddleware::check($this->conn, 'them_chamcong');
        $quyen = $_SESSION['quyen'] ?? [];

        $validator = new RequestValidator($_POST);
        $maNV = $validator->requiredInt('MaNV', 'Nhân viên', 1);
        $ngay = trim((string)($_POST['Ngay'] ?? $_POST['NgayLamViec'] ?? ''));
        $gioVao = trim((string)($_POST['GioVao'] ?? '')) ?: null;
        $gioRa = trim((string)($_POST['GioRa'] ?? '')) ?: null;
        $trangThaiRaw = trim((string)($_POST['TrangThai'] ?? ''));
        $ghiChu = trim((string)($_POST['GhiChu'] ?? '')) ?: null;

        $statusMap = [
            'Đi làm' => 'Di lam',
            'Di lam' => 'Di lam',
            'Nghỉ phép' => 'Nghi phep',
            'Nghi phep' => 'Nghi phep',
            'Nghỉ không phép' => 'Nghi khong phep',
            'Nghi khong phep' => 'Nghi khong phep',
        ];
        $trangThai = $statusMap[$trangThaiRaw] ?? '';

        if ($ngay === '') {
            WebResponder::backWithMessage('Ngày làm việc không được để trống.', 'error', 'index.php?controller=chamcong&action=them');
        }
        $dateValidator = new RequestValidator(['Ngay' => $ngay]);
        $ngay = $dateValidator->requiredDate('Ngay', 'Ngày làm việc');

        if (!$validator->isValid() || !$dateValidator->isValid() || $trangThai === '') {
            AppLogger::warning('Validation failed in ChamCongController::luu', [
                'errors' => array_merge($validator->allErrors(), $dateValidator->allErrors()),
                'trangThai' => $trangThaiRaw,
            ]);
            WebResponder::backWithMessage('Dữ liệu chấm công không hợp lệ.', 'error', 'index.php?controller=chamcong&action=them');
        }

        if ($this->model->existsChamCong($maNV,$ngay)) {
            WebResponder::backWithMessage('Nhân viên này đã được chấm công ngày đó!', 'error', 'index.php?controller=chamcong&action=them');
        }

        $this->model->insertChamCong($maNV,$ngay,$gioVao,$gioRa,$trangThai,$ghiChu);
        WebResponder::redirectWithMessage('index.php?controller=chamcong', 'Chấm công thành công!', 'success');
    }

    /* =====================================================
       FORM SỬA
       ===================================================== */
    public function sua() {
        AuthMiddleware::check($this->conn, 'sua_chamcong');
        $quyen = $_SESSION['quyen'] ?? [];
        $maCC = $_GET['macc'];
        $row = $this->model->getChamCongById($maCC);
        $nhanvien = $this->model->getAllNhanVien();
        include './views/chamcong/sua.php';
    }

    /* =====================================================
       LƯU SỬA
       ===================================================== */
    public function luuSua() {
        AuthMiddleware::check($this->conn, 'sua_chamcong');
        $quyen = $_SESSION['quyen'] ?? [];

        $validator = new RequestValidator($_POST);
        $maCC = $validator->requiredInt('MaCC', 'Mã chấm công', 1);
        $maNV = $validator->requiredInt('MaNV', 'Nhân viên', 1);
        $ngay = trim((string)($_POST['Ngay'] ?? $_POST['NgayLamViec'] ?? ''));
        $gioVao = trim((string)($_POST['GioVao'] ?? '')) ?: null;
        $gioRa  = trim((string)($_POST['GioRa'] ?? '')) ?: null;
        $trangThaiRaw = trim((string)($_POST['TrangThai'] ?? ''));
        $ghiChu = trim((string)($_POST['GhiChu'] ?? '')) ?: null;

        $statusMap = [
            'Đi làm' => 'Di lam',
            'Di lam' => 'Di lam',
            'Nghỉ phép' => 'Nghi phep',
            'Nghi phep' => 'Nghi phep',
            'Nghỉ không phép' => 'Nghi khong phep',
            'Nghi khong phep' => 'Nghi khong phep',
        ];
        $trangThai = $statusMap[$trangThaiRaw] ?? '';

        $dateValidator = new RequestValidator(['Ngay' => $ngay]);
        $ngay = $dateValidator->requiredDate('Ngay', 'Ngày làm việc');

        if (!$validator->isValid() || !$dateValidator->isValid() || $trangThai === '') {
            AppLogger::warning('Validation failed in ChamCongController::luuSua', [
                'errors' => array_merge($validator->allErrors(), $dateValidator->allErrors()),
                'trangThai' => $trangThaiRaw,
            ]);
            WebResponder::backWithMessage('Dữ liệu cập nhật chấm công không hợp lệ.', 'error', 'index.php?controller=chamcong');
        }

        if ($this->model->existsChamCong($maNV,$ngay,$maCC)) {
            WebResponder::backWithMessage('Trùng dữ liệu ngày!', 'error', 'index.php?controller=chamcong');
        }

        $this->model->updateChamCong(
            $maCC,$maNV,$ngay,$gioVao,$gioRa,$trangThai,$ghiChu
        );

        WebResponder::redirectWithMessage('index.php?controller=chamcong', 'Cập nhật thành công!', 'success');
    }

    /* =====================================================
       XOÁ
       ===================================================== */
    public function xoa() {
        AuthMiddleware::check($this->conn, 'xoa_chamcong');
        $quyen = $_SESSION['quyen'] ?? [];
        $maCC = (int)($_GET['macc'] ?? 0);
        if ($maCC <= 0) {
            WebResponder::redirectWithMessage('index.php?controller=chamcong', 'Thiếu mã chấm công.', 'error');
        }
        $this->model->deleteChamCong($maCC);
        WebResponder::redirectWithMessage('index.php?controller=chamcong', 'Đã xoá!', 'success');
    }
    /* ================== AJAX CHẤM 1 Ô ================== */
/* =====================================================
   CHẤM NHANH TRỰC TIẾP TRÊN BẢNG (AJAX)
   ===================================================== */
public function chamNhanh() {
    AuthMiddleware::check($this->conn, 'cham_cong_nhanh');
    $quyen = $_SESSION['quyen'] ?? [];
    // 1. Xóa sạch mọi output trước đó để tránh lỗi JSON/Text
    ob_clean(); 

    $maNV = $_POST['MaNV'] ?? null;
    $ngay = $_POST['Ngay'] ?? null;
    $kyHieu = $_POST['TrangThai'] ?? null;

    if (!$maNV || !$ngay || !$kyHieu) {
        WebResponder::ajaxText('Loi: Thieu du lieu', 400);
    }

    // 2. Map ký hiệu
   $trangThai = ($kyHieu == 'P') ? 'Nghi phep' : 'Di lam';

    try {
        $this->model->saveChamCongNhanh($maNV, $ngay, $trangThai, $kyHieu);
        WebResponder::ajaxText('ok');
    } catch (Exception $e) {
        AppLogger::error('ChamCongController::chamNhanh failed', ['error' => $e->getMessage()]);
        WebResponder::ajaxText('Loi: ' . $e->getMessage(), 500);
    }
}
public function exportExcel()
{
    AuthMiddleware::check($this->conn, 'xuat_bang_cham_cong');
    $quyen = $_SESSION['quyen'] ?? [];
    $thang = $_GET['thang'];
    $nam   = $_GET['nam'];

    $data = $this->model->bangChamCongThang($thang,$nam);
    $soNgay = cal_days_in_month(CAL_GREGORIAN, $thang, $nam);

    header("Content-Type: application/vnd.ms-excel; charset=utf-8");
    header("Content-Disposition: attachment; filename=ChamCong_{$thang}_{$nam}.xls");

    echo "<table border='1'>";
    echo "<tr>
            <th>Ma NV</th>
            <th>Ho Ten</th>";

    for($d=1;$d<=$soNgay;$d++){
        echo "<th>$d</th>";
    }
    echo "</tr>";

    foreach($data as $pb => $ds){
        foreach($ds as $nv){
            echo "<tr>";
            echo "<td>{$nv['MaNV']}</td>";
            echo "<td>{$nv['HoTen']}</td>";

            for($d=1;$d<=$soNgay;$d++){
                $val = $nv['Ngay'][$d] ?? '';
                echo "<td>$val</td>";
            }

            echo "</tr>";
        }
    }

    echo "</table>";
    exit;
}
}
?>
