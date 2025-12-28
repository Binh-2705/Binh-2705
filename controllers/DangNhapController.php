<?php
require_once 'models/TaiKhoanModel.php';

class DangNhapController {
    private $model;

    public function __construct($conn){
        $this->model = new TaiKhoanModel($conn);
    }

    public function login(){
        if($_POST){
            $user = $_POST['TenDangNhap'];
            $pass = $_POST['MatKhau'];

            $tk = $this->model->dangNhap($user, $pass);

            if($tk){
                $_SESSION['taikhoan'] = $tk;
                $_SESSION['vaitro']   = $tk['VaiTro'];

                header("Location: index.php");
                exit;
            }else{
                $loi = "Sai tên đăng nhập hoặc mật khẩu";
            }
        }
        require 'views/dangnhap/login.php';
    }

    public function dangxuat(){
        session_destroy();
        header("Location: index.php?controller=dangnhap&action=login");
        exit;
    }
}
