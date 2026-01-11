<?php
require_once 'models/TaiKhoanModel.php';

class TaiKhoanController {
    private $model;

    public function __construct($conn){
        $this->model = new TaiKhoanModel($conn);
    }

    // DANH SÁCH + TÌM KIẾM
    public function index(){
        $key = $_GET['key'] ?? '';
        $data = $this->model->getAll($key);
        require 'views/taikhoan/index.php';
    }

    // THÊM
    public function them(){
        if ($_POST){
            $this->model->insert(
                $_POST['user'],
                $_POST['pass'],
                $_POST['vaitro'],
                $_POST['manv']
            );
            header("Location: index.php?controller=taikhoan");
        }
        require 'views/taikhoan/them.php';
    }

    // SỬA
    public function sua(){
        $id = $_GET['id'];
        $tk = $this->model->getById($id);

        if ($_POST){
            $this->model->update(
                $id,
                $_POST['vaitro'],
                $_POST['manv']
            );
            header("Location: index.php?controller=taikhoan");
        }
        require 'views/taikhoan/sua.php';
    }

    // XÓA
    public function xoa(){
        $this->model->delete($_GET['id']);
        header("Location: index.php?controller=taikhoan");
    }
    public function quenMatKhau(){
    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        $tenDangNhap = $_POST['TenDangNhap'];

        if($this->model->checkTenDangNhap($tenDangNhap)){
            // tạo mật khẩu mới
            $matKhauMoi = rand(100000,999999);
            $matKhauMaHoa = password_hash($matKhauMoi, PASSWORD_DEFAULT);

            $this->model->resetMatKhau($tenDangNhap, $matKhauMaHoa);

            $thongbao = "Mật khẩu mới của bạn là: <b>$matKhauMoi</b>";
        }else{
            $loi = "Tên đăng nhập không tồn tại!";
        }
    }
    include 'views/dangnhap/quenmatkhau.php';
}

}
