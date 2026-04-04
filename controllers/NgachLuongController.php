<?php
require_once 'models/NgachLuongModel.php';
require_once 'core/AuthMiddleware.php';
class NgachLuongController {
    private $model;
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
        $this->model = new NgachLuongModel($conn);
    }

    /* ======================
       DANH SÁCH NGẠCH LƯƠNG
    ====================== */
    public function index() {
        AuthMiddleware::check($this->conn, 'xem_ngachluong');
        $quyen = $_SESSION['quyen'] ?? [];
        $ngachluongs = $this->model->getAllNgachLuong();
        include 'views/ngachluong/index.php';
    }

}

