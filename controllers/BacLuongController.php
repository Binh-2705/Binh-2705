<?php
// controllers/BacLuongController.php
require_once 'models/BacLuongModel.php';
require_once 'models/NgachLuongModel.php';
require_once 'core/AuthMiddleware.php';

class BacLuongController {
    private $bacLuongModel;
    private $conn;
    private $ngachLuongModel;

    public function __construct($conn) {
        $this->conn = $conn;
        $this->bacLuongModel  = new BacLuongModel($conn);
        $this->ngachLuongModel = new NgachLuongModel($conn);
    }

    public function index() {
        AuthMiddleware::check($this->conn, 'xem_bacluong');
        $quyen = $_SESSION['quyen'] ?? [];
         
        $bacluong = $this->bacLuongModel->getAll();
        require 'views/bacluong/index.php';
    }

    

    

   
}