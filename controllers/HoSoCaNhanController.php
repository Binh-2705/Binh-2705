<?php
require_once 'models/HoSoCaNhanModel.php';

class HoSoCaNhanController {
    private $model;

    public function __construct($conn) {
        $this->model = new HoSoCaNhanModel($conn);
    }

    public function index() {

    // ===== XEM CHI TIẾT =====
    if (isset($_GET['id']) && $_GET['id'] !== '') {

        $id = $_GET['id'];

        $nv       = $this->model->getChiTietNhanVien($id);
        $luong    = $this->model->getLuongGanNhat($id);
        $hopdong  = $this->model->getHopDongHienTai($id);
        $chamcong = $this->model->getChamCongThangGanNhat($id);
        $ktkl     = $this->model->getKTKL($id);

        include 'views/hosocanhan/chitiet.php';
        return;
    }

    // ===== DANH SÁCH =====
    $danhSach = $this->model->getAllNhanVien();
    include 'views/hosocanhan/index.php';
}

}
