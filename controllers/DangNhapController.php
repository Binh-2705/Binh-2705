<?php

require_once 'models/TaiKhoanModel.php';

class DangNhapController {
    private $model;

    public function __construct($conn){
        $this->model = new TaiKhoanModel($conn);
    }

    public function login(){
        if($_POST){
            $tk = $this->model->dangNhap(
                $_POST['TenDangNhap'],
                $_POST['MatKhau']
            );

            if($tk){
                $_SESSION['taikhoan'] = $tk;
                $_SESSION['vaitro'] = $tk['VaiTro'];
                header("Location: index.php");
                exit;
            }else{
                $loi = "Sai tên đăng nhập hoặc mật khẩu";
            }
        }
        require 'views/dangnhap/login.php';
    }
    public function quenMatKhau(){
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            $tenDangNhap = $_POST['TenDangNhap'];

            if($this->model->checkTenDangNhap($tenDangNhap)){
                $matKhauMoi = rand(100000,999999);
                $hash = password_hash($matKhauMoi, PASSWORD_DEFAULT);

                $this->model->resetMatKhau($tenDangNhap, $hash);

                $thongbao = "Mật khẩu mới của bạn là: <b>$matKhauMoi</b>";
            }else{
                $loi = "Tên đăng nhập không tồn tại!";
            }
        }
        include 'views/dangnhap/quenmatkhau.php';
    }
    public function dangxuat(){
        session_unset();      // xoá biến session
        session_destroy();    // huỷ session
        header("Location: index.php?controller=dangnhap&action=login");
        exit;
    }
}
