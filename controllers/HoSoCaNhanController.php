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
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 10;
        $totalItems = $this->model->countAll();
        $totalPages = max(1, (int)ceil($totalItems / $perPage));
        if ($page > $totalPages) {
            $page = $totalPages;
        }
        $hosos = $this->model->getPage($page, $perPage);
        include 'views/hosocanhan/index.php';
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

}
