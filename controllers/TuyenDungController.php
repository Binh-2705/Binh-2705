<?php
require_once "models/TuyenDungModel.php";
require 'vendor/autoload.php';
require_once 'core/AuthMiddleware.php';
require_once 'core/RequestValidator.php';
require_once 'core/WebResponder.php';
require_once 'core/AppLogger.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class TuyenDungController{

    private $model;
    private $conn;

    public function __construct($conn){
        $this->conn = $conn;
        $this->model = new TuyenDungModel($conn);
    }

    /* ================= DANH SÁCH ĐỢT TUYỂN ================= */

    public function index(){
        AuthMiddleware::check($this->conn, 'xem_dot_tuyen');
        $quyen = $_SESSION['quyen'] ?? [];

        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 10;
        $totalItems = $this->model->countDot();
        $totalPages = max(1, (int)ceil($totalItems / $perPage));
        if ($page > $totalPages) {
            $page = $totalPages;
        }
        $offset = ($page - 1) * $perPage;

        $dot = $this->model->getDotPage($perPage, $offset);


        include "views/tuyendung/index.php";
    }

    /* ================= THÊM ĐỢT TUYỂN ================= */

    public function themDot(){
        AuthMiddleware::check($this->conn, 'them_dot_tuyen');
        $quyen = $_SESSION['quyen'] ?? [];

        if($_SERVER['REQUEST_METHOD']=="POST"){
            $validator = new RequestValidator($_POST);

            $data = [
                'ten'=>$validator->requiredString('ten', 'Tên đợt tuyển', 2, 150),
                'vitri'=>$validator->requiredString('vitri', 'Vị trí tuyển', 2, 120),
                'soluong'=>$validator->requiredInt('soluong', 'Số lượng', 1),
                'tu'=>$validator->requiredDate('tu', 'Từ ngày'),
                'den'=>$validator->requiredDate('den', 'Đến ngày'),
                'mota'=>$validator->optionalString('mota', 2000)
            ];

            if (!$validator->isValid()) {
                AppLogger::warning('Validation failed in TuyenDungController::themDot', ['errors' => $validator->allErrors()]);
                WebResponder::backWithMessage($validator->firstError(), 'error', 'index.php?controller=tuyendung&action=themdot');
            }

            if (strtotime($data['den']) < strtotime($data['tu'])) {
                WebResponder::backWithMessage('Đến ngày phải lớn hơn hoặc bằng Từ ngày.', 'error', 'index.php?controller=tuyendung&action=themdot');
            }

            $this->model->themDot($data);

            WebResponder::redirectWithMessage('index.php?controller=tuyendung', 'Thêm đợt tuyển thành công.', 'success');
        }

        include "views/tuyendung/themdot.php";
    }

    /* ================= XÓA ĐỢT ================= */

    public function xoaDot(){
        AuthMiddleware::check($this->conn, 'xoa_dot_tuyen');
        $quyen = $_SESSION['quyen'] ?? [];

        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            WebResponder::redirectWithMessage('index.php?controller=tuyendung', 'Thiếu mã đợt tuyển.', 'error');
        }

        $this->model->xoaDot($id);

        WebResponder::redirectWithMessage('index.php?controller=tuyendung', 'Xóa đợt tuyển thành công.', 'success');
    }

    /* ================= DANH SÁCH ỨNG VIÊN ================= */

    public function ungvien(){
        AuthMiddleware::check($this->conn, 'xem_ung_vien');
        $quyen = $_SESSION['quyen'] ?? [];

        $ungvien = $this->model->getAllUngVien();

        include "views/tuyendung/ungvien.php";
    }

    /* ================= THÊM ỨNG VIÊN ================= */

    public function themUngVien(){
        AuthMiddleware::check($this->conn, 'them_ung_vien');
        $quyen = $_SESSION['quyen'] ?? [];

        if($_SERVER['REQUEST_METHOD']=="POST"){
            $validator = new RequestValidator($_POST);

            $cvName = null;

            if(isset($_FILES['cv']) && $_FILES['cv']['error']==0){
                $extension = strtolower(pathinfo($_FILES['cv']['name'], PATHINFO_EXTENSION));
                if ($extension !== 'pdf') {
                    WebResponder::backWithMessage('CV chỉ hỗ trợ định dạng PDF.', 'error', 'index.php?controller=tuyendung&action=themungvien');
                }

                $uploadDir = "uploads/cv/";

                if(!is_dir($uploadDir)){
                    mkdir($uploadDir,0777,true);
                }

                $safeFileName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $_FILES['cv']['name']);
                $cvName = time()."_".$safeFileName;

                if (!move_uploaded_file($_FILES['cv']['tmp_name'], $uploadDir.$cvName)) {
                    WebResponder::backWithMessage('Không thể tải lên tệp CV.', 'error', 'index.php?controller=tuyendung&action=themungvien');
                }
            } elseif (isset($_FILES['cv']) && $_FILES['cv']['error'] !== UPLOAD_ERR_NO_FILE) {
                WebResponder::backWithMessage('Lỗi tải lên tệp CV.', 'error', 'index.php?controller=tuyendung&action=themungvien');
            }

            $email = trim((string)($_POST['email'] ?? ''));
            if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $validator->optionalEmail('email', 'Email');
            }

            $data = [
                'hoten'=>$validator->requiredString('hoten', 'Họ tên', 2, 120),
                'ngaysinh'=>$validator->requiredDate('ngaysinh', 'Ngày sinh'),
                'gioitinh'=>$validator->in('gioitinh', 'Giới tính', ['Nam', 'Nữ']),
                'email'=>$validator->optionalEmail('email', 'Email', 150),
                'dienthoai'=>$validator->optionalPattern('dienthoai', 'Điện thoại', '/^[0-9+\s\-]{8,20}$/'),
                'trinhdo'=>$validator->optionalString('trinhdo', 120),
                'kinhnghiem'=>$validator->optionalString('kinhnghiem', 2000),
                'cv'=>$cvName
            ];

            if (!$validator->isValid()) {
                AppLogger::warning('Validation failed in TuyenDungController::themUngVien', ['errors' => $validator->allErrors()]);
                WebResponder::backWithMessage($validator->firstError(), 'error', 'index.php?controller=tuyendung&action=themungvien');
            }

            $this->model->themUngVien($data);
            
            WebResponder::redirectWithMessage('index.php?controller=tuyendung&action=ungvien', 'Thêm ứng viên thành công.', 'success');
        }

        include "views/tuyendung/themungvien.php";
    }

    /* ================= HỒ SƠ THEO ĐỢT ================= */

    public function hosodot(){
        AuthMiddleware::check($this->conn, 'xem_ho_so');
        $quyen = $_SESSION['quyen'] ?? [];

        $maDTD = (int)($_GET['id'] ?? 0);
        if ($maDTD <= 0) {
            WebResponder::redirectWithMessage('index.php?controller=tuyendung', 'Thiếu mã đợt tuyển.', 'error');
        }

        $hoso = $this->model->getHoSoTheoDot($maDTD);

        include "views/tuyendung/hosodot.php";
    }

    /* ================= THÊM HỒ SƠ ================= */

    public function themHoSo(){
        AuthMiddleware::check($this->conn, 'them_ho_so');
        $quyen = $_SESSION['quyen'] ?? [];

        if($_SERVER['REQUEST_METHOD']!=="POST"){
            WebResponder::redirectWithMessage('index.php?controller=tuyendung', 'Phương thức không hợp lệ.', 'error');
        }

        $validator = new RequestValidator($_POST);

        $maUV = $validator->requiredInt('MaUV', 'Mã ứng viên', 1);
        $maDTD = $validator->requiredInt('MaDTD', 'Mã đợt tuyển', 1);

        if (!$validator->isValid()) {
            AppLogger::warning('Validation failed in TuyenDungController::themHoSo', ['errors' => $validator->allErrors()]);
            WebResponder::backWithMessage($validator->firstError(), 'error', 'index.php?controller=tuyendung');
        }

        $this->model->themHoSo($maUV,$maDTD);

        WebResponder::redirectWithMessage('index.php?controller=tuyendung&action=hosodot&id='.$maDTD, 'Thêm hồ sơ thành công.', 'success');
    }

    /* ================= CẬP NHẬT TRẠNG THÁI ================= */

   public function capNhatTrangThai(){
    AuthMiddleware::check($this->conn, 'capnhat_trangthai');
    $quyen = $_SESSION['quyen'] ?? [];

    if($_SERVER['REQUEST_METHOD']!=="POST"){
        WebResponder::redirectWithMessage('index.php?controller=tuyendung', 'Phương thức không hợp lệ.', 'error');
    }

    $validator = new RequestValidator($_POST);

    $maHS = $validator->requiredInt('MaHS', 'Mã hồ sơ', 1);
    $trangthai = $validator->in('TrangThai', 'Trạng thái', ['Nộp hồ sơ', 'Sàng lọc', 'Phỏng vấn', 'Offer', 'Nhận việc', 'Rớt']);

    if (!$validator->isValid()) {
        AppLogger::warning('Validation failed in TuyenDungController::capNhatTrangThai', ['errors' => $validator->allErrors()]);
        WebResponder::backWithMessage($validator->firstError(), 'error', 'index.php?controller=tuyendung');
    }

    $this->model->capNhatTrangThai($maHS,$trangthai);

    // nếu nhận việc → tạo nhân viên
    if($trangthai == "Nhận việc"){
        $this->model->chuyenThanhNhanVien($maHS);
    }

    WebResponder::backWithMessage('Cập nhật trạng thái thành công.', 'success', 'index.php?controller=tuyendung');
}
    /* ================= LỊCH PHỎNG VẤN ================= */

public function lichphongvan(){
    AuthMiddleware::check($this->conn, 'xem_lich_phong_van');
    $quyen = $_SESSION['quyen'] ?? [];

    $maHS = (int)($_GET['id'] ?? 0);
    if ($maHS <= 0) {
        WebResponder::redirectWithMessage('index.php?controller=tuyendung', 'Thiếu mã hồ sơ.', 'error');
    }

    $lich = $this->model->getLichPhongVan($maHS);

    $danhgia = $this->model->getDanhGia($maHS);

    include "views/tuyendung/lichphongvan.php";
}
public function themLichPhongVan(){
    AuthMiddleware::check($this->conn, 'them_lich_phong_van');
    $quyen = $_SESSION['quyen'] ?? [];

if($_SERVER['REQUEST_METHOD']=="POST"){
    $validator = new RequestValidator($_POST);

    $data = [
    'mahs'=>$validator->requiredInt('MaHS', 'Mã hồ sơ', 1),
    'ngay'=>$validator->requiredDate('ngay', 'Ngày phỏng vấn'),
    'gio'=>$validator->requiredString('gio', 'Giờ phỏng vấn', 4, 5),
    'diadiem'=>$validator->optionalString('diadiem', 255),
    'ghichu'=>$validator->optionalString('ghichu', 1000)
    ];

    if (!preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $data['gio'])) {
        WebResponder::backWithMessage('Giờ phỏng vấn không hợp lệ.', 'error', 'index.php?controller=tuyendung');
    }

    if (!$validator->isValid()) {
        AppLogger::warning('Validation failed in TuyenDungController::themLichPhongVan', ['errors' => $validator->allErrors()]);
        WebResponder::backWithMessage($validator->firstError(), 'error', 'index.php?controller=tuyendung');
    }

    $this->model->themLichPhongVan($data);

/* LẤY EMAIL ỨNG VIÊN */

    $uv = $this->model->getEmailUngVien($data['mahs']);

    if($uv){

        $this->guiEmailPhongVan(
        $uv['Email'],
        $uv['HoTen'],
        $data['ngay'],
        $data['gio'],
        $data['diadiem']
        );

    }

    WebResponder::backWithMessage('Thêm lịch phỏng vấn thành công.', 'success', 'index.php?controller=tuyendung');

}
}
/* ================= DASHBOARD TUYỂN DỤNG ================= */

public function dashboard(){
    AuthMiddleware::check($this->conn, 'xem_dashboard');
    $quyen = $_SESSION['quyen'] ?? [];

    $thongke = $this->model->thongKeTuyenDung();

    include "views/tuyendung/dashboard.php";
}
public function kanban(){

$data = $this->model->kanban();

include "views/tuyendung/kanban.php";

}
public function updateKanban(){

    AuthMiddleware::check($this->conn, 'capnhat_trangthai');
    $quyen = $_SESSION['quyen'] ?? [];

if($_SERVER['REQUEST_METHOD']!=="POST"){
    WebResponder::ajaxText('invalid_method', 405);
}

$validator = new RequestValidator($_POST);
$maHS = $validator->requiredInt('MaHS', 'Mã hồ sơ', 1);
$trangthai = $validator->in('TrangThai', 'Trạng thái', ['Nộp hồ sơ', 'Sàng lọc', 'Phỏng vấn', 'Offer', 'Nhận việc', 'Rớt']);

if (!$validator->isValid()) {
    AppLogger::warning('Validation failed in TuyenDungController::updateKanban', ['errors' => $validator->allErrors()]);
    WebResponder::ajaxText('invalid_data', 422);
}

$this->model->capNhatTrangThai($maHS,$trangthai);

WebResponder::ajaxText('ok');

}
public function chonDot(){
    AuthMiddleware::check($this->conn, 'chon_dot_tuyen');
    $quyen = $_SESSION['quyen'] ?? [];

    $maUV = (int)($_GET['id'] ?? 0);
    if ($maUV <= 0) {
        WebResponder::redirectWithMessage('index.php?controller=tuyendung&action=ungvien', 'Thiếu mã ứng viên.', 'error');
    }

$dot = $this->model->getAllDot();

include "views/tuyendung/chondot.php";

}
public function themDanhGia(){
    AuthMiddleware::check($this->conn, 'them_danh_gia');
    $quyen = $_SESSION['quyen'] ?? [];

if($_SERVER['REQUEST_METHOD']=="POST"){
    $validator = new RequestValidator($_POST);

    $data = [
    'mahs'=>$validator->requiredInt('MaHS', 'Mã hồ sơ', 1),
    'kynang'=>$validator->requiredInt('kynang', 'Điểm kỹ năng', 1),
    'kinhnghiem'=>$validator->requiredInt('kinhnghiem', 'Điểm kinh nghiệm', 1),
    'thaido'=>$validator->requiredInt('thaido', 'Điểm thái độ', 1),
    'nhanxet'=>$validator->optionalString('nhanxet', 2000)
    ];

    if (
        $data['kynang'] > 10 ||
        $data['kinhnghiem'] > 10 ||
        $data['thaido'] > 10
    ) {
        WebResponder::backWithMessage('Các điểm đánh giá phải trong khoảng 1 đến 10.', 'error', 'index.php?controller=tuyendung');
    }

    if (!$validator->isValid()) {
        AppLogger::warning('Validation failed in TuyenDungController::themDanhGia', ['errors' => $validator->allErrors()]);
        WebResponder::backWithMessage($validator->firstError(), 'error', 'index.php?controller=tuyendung');
    }

    $this->model->themDanhGia($data);

    WebResponder::backWithMessage('Lưu đánh giá thành công.', 'success', 'index.php?controller=tuyendung');

}

}
public function topUngVien(){
    AuthMiddleware::check($this->conn, 'xem_top_ung_vien');
    $quyen = $_SESSION['quyen'] ?? [];

$data = $this->model->topUngVien();

include "views/tuyendung/topungvien.php";

}
private function guiEmailPhongVan($email,$ten,$ngay,$gio,$diadiem){

$mail = new PHPMailer(true);

try{

$mail->CharSet = 'UTF-8';

$mail->isSMTP();
$mail->Host = 'smtp.gmail.com';
$mail->SMTPAuth = true;

$mail->Username = 'youremail@gmail.com';
$mail->Password = 'app_password';

$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port = 587;

$mail->setFrom('youremail@gmail.com','Phòng nhân sự');

$mail->addAddress($email,$ten);

$mail->isHTML(true);

$mail->Subject = "Thư mời phỏng vấn";

$mail->Body = "
Xin chào <b>$ten</b>,<br><br>

Bạn được mời tham gia phỏng vấn với thông tin sau:<br><br>

<b>Ngày:</b> $ngay<br>
<b>Giờ:</b> $gio<br>
<b>Địa điểm:</b> $diadiem<br><br>

Vui lòng đến đúng giờ.<br><br>

Trân trọng,<br>
<b>Phòng nhân sự</b>
";

$mail->send();

}catch(Exception $e){
    AppLogger::error('Send interview email failed', ['error' => $mail->ErrorInfo]);

}

}
public function timUngVien(){
    AuthMiddleware::check($this->conn, 'tim_ung_vien');
    $quyen = $_SESSION['quyen'] ?? [];

    $keyword = trim((string)($_GET['keyword'] ?? ''));
    if (mb_strlen($keyword) > 100) {
        WebResponder::backWithMessage('Từ khóa tìm kiếm quá dài.', 'error', 'index.php?controller=tuyendung&action=ungvien');
    }

    $ungvien = $this->model->timUngVien($keyword);

    include "views/tuyendung/ungvien.php";
}
}
