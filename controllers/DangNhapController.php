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
    public function dangxuat(){
        session_unset();      // xoá biến session
        session_destroy();    // huỷ session
        header("Location: index.php?controller=dangnhap&action=login");
        exit;
    }
}
