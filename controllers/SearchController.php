<?php
require_once 'core/AuthMiddleware.php';

class SearchController{

    private $model;
    private $conn;

    public function __construct($conn){
        $this->conn = $conn;
        require_once "models/SearchModel.php";
        $this->model = new SearchModel($conn);
    }

    public function index(){
        AuthMiddleware::check($this->conn, 'timkiem_nhanvien');

        $keyword = trim((string)($_GET['keyword'] ?? $_POST['keyword'] ?? ''));

        // Khi chưa nhập từ khóa thì trả danh sách rỗng để tránh query toàn bộ dữ liệu.
        if($keyword === ''){
            $nhanvien = null;
            $phongban = null;
            $hopdong = null;
        } else {
            $nhanvien = $this->model->timNhanVien($keyword);
            $phongban = $this->model->timPhongBan($keyword);
            $hopdong = $this->model->timHopDong($keyword);
        }

        include "views/search/result.php";

    }

}