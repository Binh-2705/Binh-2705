<?php
require_once 'models/BaoHiemModel.php';
require_once 'core/AuthMiddleware.php';
require_once 'core/RequestValidator.php';
require_once 'core/AppLogger.php';
class BaoHiemController {
    private $model;
    private $conn;

    public function __construct($conn){
        $this->conn = $conn;
        $this->model = new BaoHiemModel($conn);
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
        $role = strtolower(trim((string)($account['VaiTro'] ?? '')));
        return $role === 'nhanvien';
    }

    /* ================= DANH SÁCH ================= */
    public function index(){
        AuthMiddleware::check($this->conn, 'xem_baohiem');
        $quyen = $_SESSION['quyen'] ?? [];
        if ($this->isEmployeeRole()) {
            $maNV = $this->currentEmployeeId();
            $ds = ($maNV !== null) ? $this->model->getAllByMaNV($maNV) : false;
        } else {
            $ds = $this->model->getAll();
        }
        include 'views/baohiem/index.php';
    }

    /* ================= THÊM ================= */
    /* ================= THÊM (Nâng cấp) ================= */
public function them(){
    $nhanviens = $this->model->getNhanVien();
        AuthMiddleware::check($this->conn, 'them_baohiem');
$quyen = $_SESSION['quyen'] ?? [];
    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        $validator = new RequestValidator($_POST);
        $mucDong = $validator->optionalFloat('MucDong', 0);

        $data = [
            'MaNV'         => $validator->requiredInt('MaNV', 'Nhân viên', 1),
            'SoBHXH'       => $validator->optionalPattern('SoBHXH', 'Số BHXH', '/^[0-9A-Za-z-]{6,30}$/'),
            'LoaiBaoHiem'  => $validator->in('LoaiBaoHiem', 'Loại bảo hiểm', ['BHXH', 'BHYT', 'BHTN']),
            'NgayThamGia'  => $validator->optionalDate('NgayThamGia') ?? '',
            'MucDong'      => $mucDong ?? 0,
            'TrangThai'    => $validator->in('TrangThai', 'Trạng thái', ['Đang đóng', 'Đã dừng']),
        ];

        if (trim((string)$data['SoBHXH']) === '') {
            $validatorErrors = $validator->allErrors();
            $validatorErrors[] = 'Số BHXH không được để trống.';
            $_SESSION['error'] = $validatorErrors[0];
            AppLogger::warning('Validation failed in BaoHiemController::them', ['errors' => $validatorErrors]);
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'index.php?controller=baohiem&action=them'));
            exit;
        }

        if(!$validator->isValid()){
            $_SESSION['error'] = $validator->firstError();
            AppLogger::warning('Validation failed in BaoHiemController::them', ['errors' => $validator->allErrors()]);
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'index.php?controller=baohiem&action=them'));
            exit;
        }

        $rateCompany = ['BHXH' => 0.175, 'BHYT' => 0.03, 'BHTN' => 0.01];
        $rateEmployee = ['BHXH' => 0.08, 'BHYT' => 0.015, 'BHTN' => 0.01];
        $data['CongTyDong'] = $data['MucDong'] * $rateCompany[$data['LoaiBaoHiem']];
        $data['NhanVienDong'] = $data['MucDong'] * $rateEmployee[$data['LoaiBaoHiem']];
        
        // 2. Kiểm tra trùng số BHXH
        if($this->model->checkSoBHXH($data['SoBHXH'])){
            $_SESSION['error'] = "Số BHXH này đã tồn tại trong hệ thống!";
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'index.php?controller=baohiem&action=them'));
            exit;
        } else {
            if($this->model->them($data)){
                $_SESSION['success'] = 'Thêm bảo hiểm thành công.';
                header("Location: index.php?controller=baohiem&msg=added");
                exit;
            }

            $_SESSION['error'] = 'Không thể thêm bảo hiểm.';
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'index.php?controller=baohiem&action=them'));
            exit;
        }
    }
    include 'views/baohiem/them.php';
}

    /* ================= SỬA ================= */
    public function sua(){
        AuthMiddleware::check($this->conn, 'sua_baohiem');
$quyen = $_SESSION['quyen'] ?? [];
        $id = $_GET['id'] ?? 0;

        $baohiem = $this->model->getById($id);
        $nhanviens = $this->model->getNhanVien();

        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            $validator = new RequestValidator($_POST);
            $mucDong = $validator->optionalFloat('MucDong', 0);

            $data = [
                'MaNV'         => $validator->requiredInt('MaNV', 'Nhân viên', 1),
                'SoBHXH'       => $validator->optionalPattern('SoBHXH', 'Số BHXH', '/^[0-9A-Za-z-]{6,30}$/'),
                'LoaiBaoHiem'  => $validator->in('LoaiBaoHiem', 'Loại bảo hiểm', ['BHXH', 'BHYT', 'BHTN']),
                'NgayThamGia'  => $validator->optionalDate('NgayThamGia') ?? '',
                'MucDong'      => $mucDong ?? 0,
                'TrangThai'    => $validator->in('TrangThai', 'Trạng thái', ['Đang đóng', 'Đã dừng']),
            ];

            if (trim((string)$data['SoBHXH']) === '') {
                $_SESSION['error'] = 'Số BHXH không được để trống.';
                header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'index.php?controller=baohiem&action=sua&id=' . $id));
                exit;
            }

            if(!$validator->isValid()){
                $_SESSION['error'] = $validator->firstError();
                AppLogger::warning('Validation failed in BaoHiemController::sua', ['errors' => $validator->allErrors()]);
                header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'index.php?controller=baohiem&action=sua&id=' . $id));
                exit;
            }

            if ($this->model->checkSoBHXH($data['SoBHXH'], $id)) {
                $_SESSION['error'] = 'Số BHXH này đã tồn tại trong hệ thống!';
                header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'index.php?controller=baohiem&action=sua&id=' . $id));
                exit;
            }

            $rateCompany = ['BHXH' => 0.175, 'BHYT' => 0.03, 'BHTN' => 0.01];
            $rateEmployee = ['BHXH' => 0.08, 'BHYT' => 0.015, 'BHTN' => 0.01];
            $data['CongTyDong'] = $data['MucDong'] * $rateCompany[$data['LoaiBaoHiem']];
            $data['NhanVienDong'] = $data['MucDong'] * $rateEmployee[$data['LoaiBaoHiem']];

            $this->model->sua($id, $data);
            $_SESSION['success'] = 'Cập nhật bảo hiểm thành công.';
            header("Location: index.php?controller=baohiem");
            exit;
        }

        include 'views/baohiem/sua.php';
    }

    /* ================= XÓA ================= */
    public function xoa(){
        AuthMiddleware::check($this->conn, 'xoa_baohiem');
        $id = $_GET['id'] ?? 0;
$quyen = $_SESSION['quyen'] ?? [];
        if($id){
            $this->model->xoa($id);
        }

        header("Location: index.php?controller=baohiem");
        exit;
    }

    /* ================= NGỪNG BẢO HIỂM (NÊN DÙNG) ================= */
    public function dung(){
        AuthMiddleware::check($this->conn, 'dung_baohiem');
        $id = $_GET['id'] ?? 0;
$quyen = $_SESSION['quyen'] ?? [];
        if($id){
            $this->model->dungBaoHiem($id);
        }

        header("Location: index.php?controller=baohiem");
        exit;
    }

    /* ================= TÌM KIẾM ================= */
    public function timkiem(){
            AuthMiddleware::check($this->conn, 'timkiem_baohiem');
$quyen = $_SESSION['quyen'] ?? [];
        $keyword = $_GET['keyword'] ?? '';

        if ($this->isEmployeeRole()) {
            $maNV = $this->currentEmployeeId();
            if ($maNV === null) {
                $ds = false;
            } else {
                $ds = $this->model->searchByKeywordAndMaNV((string)$keyword, $maNV);
            }
        } else {
            $sql = "SELECT bh.*, nv.HoTen
                    FROM baohiem bh
                    JOIN nhanvien nv ON bh.MaNV = nv.MaNV
                    WHERE nv.HoTen LIKE ?
                    ORDER BY bh.MaBH DESC";

            $stmt = $this->conn->prepare($sql);
            $like = "%$keyword%";
            $stmt->bind_param("s", $like);
            $stmt->execute();

            $ds = $stmt->get_result();
        }

        include 'views/baohiem/index.php';
    }
    public function xuatExcel() {
        AuthMiddleware::check($this->conn, 'xuat_excel_baohiem');
        $quyen = $_SESSION['quyen'] ?? [];
    $data = $this->model->getExportData();
    
    $filename = "Danh_Sach_Bao_Hiem_" . date('Ymd') . ".xls";
    
    header("Content-Type: application/vnd.ms-excel; charset=utf-8");
    header("Content-Disposition: attachment; filename=$filename");
    
    echo "<table border='1'>";
    echo "<tr>
            <th>ID</th>
            <th>Nhân viên</th>
            <th>Số BHXH</th>
            <th>Mức đóng</th>
            <th>Công ty đóng (21.5%)</th>
            <th>Cá nhân đóng (10.5%)</th>
            <th>Trạng thái</th>
          </tr>";

    while($row = $data->fetch_assoc()) {
        echo "<tr>
                <td>{$row['MaBH']}</td>
                <td>{$row['HoTen']}</td>
                <td>'{$row['SoBHXH']}</td> 
                <td>" . number_format($row['MucDong']) . "</td>
                <td>" . number_format($row['CongTyDong']) . "</td>
                <td>" . number_format($row['NhanVienDong']) . "</td>
                <td>{$row['TrangThai']}</td>
              </tr>";
    }
    echo "</table>";
    exit;
}
}