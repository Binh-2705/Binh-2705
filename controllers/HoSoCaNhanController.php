<?php
require_once 'models/HoSoCaNhanModel.php'; 

class HoSoCaNhanController {
    private $model;

    public function __construct($conn) {
        $this->model = new HoSoCaNhanModel($conn);
    }

    public function index() {
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            $nv = $this->model->getChiTietNhanVien($id);
            include 'views/hosocanhan/chitiet.php';
        } else {
            $danhSach = $this->model->getAllNhanVien(); 
            include 'views/hosocanhan/index.php';
        }
    }
}