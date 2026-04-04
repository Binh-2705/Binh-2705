<?php
require_once 'models/PhanQuyenModel.php';
require_once 'core/AuthMiddleware.php';

class PhanQuyenController {
    private $model;
    private $conn;

    public function __construct($conn){
        $this->conn = $conn;
        $this->model = new PhanQuyenModel($conn);
    }

    // 🔥 Xem toàn bộ phân quyền
    public function index(){
        AuthMiddleware::check($this->conn, 'xem_taikhoan');
        $roles = $this->model->getAllRoles();
        $functions = $this->model->getAllFunctions();
        $data = $this->model->getAllPermissions();
        require 'views/phanquyen/index.php';
    }

    // 🔥 Xem quyền của 1 tài khoản
    public function xemTheoTaiKhoan(){
        AuthMiddleware::check($this->conn, 'xem_taikhoan');
        $maTK = $_GET['matk'] ?? 0;

        $roles = $this->model->getRolesByUser($maTK);
        $permissions = $this->model->getPermissionsByUser($maTK);

        require 'views/phanquyen/detail.php';
    }
    public function capNhat(){
        AuthMiddleware::check($this->conn, 'sua_taikhoan');
    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        $maVaiTro = $_POST['MaVaiTro'];
        $chucNangs = $_POST['chucnang'] ?? [];

        // 🔥 Xóa quyền cũ
        $this->model->deleteByRole($maVaiTro);

        // 🔥 Thêm lại quyền mới
        foreach($chucNangs as $maCN){
            $this->model->insertPermission($maVaiTro, $maCN);
        }

        header("Location: index.php?controller=phanquyen&action=index");
        exit;
    }
}

    public function khoiPhucMacDinh(){
        AuthMiddleware::check($this->conn, 'sua_taikhoan');

        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            header("Location: index.php?controller=phanquyen&action=index");
            exit;
        }

        $maVaiTro = (int)($_POST['MaVaiTro'] ?? 0);
        if ($maVaiTro <= 0) {
            header("Location: index.php?controller=phanquyen&action=index&msg=invalid_role");
            exit;
        }

        $defaults = $this->model->getDefaultPermissionsFromSeed($maVaiTro);
        if (empty($defaults)) {
            header("Location: index.php?controller=phanquyen&action=index&msg=no_seed_default");
            exit;
        }

        $this->model->deleteByRole($maVaiTro);
        foreach($defaults as $maCN){
            $this->model->insertPermission($maVaiTro, $maCN);
        }

        header("Location: index.php?controller=phanquyen&action=index&msg=reset_success");
        exit;
    }
}