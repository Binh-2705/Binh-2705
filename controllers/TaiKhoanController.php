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
}
