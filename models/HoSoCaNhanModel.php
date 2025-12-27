<?php
class HoSoCaNhanModel {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getAllNhanVien() {
        $sql = "SELECT MaNV, HoTen, PhongBan, ChucVu FROM nhanvien";
        $result = mysqli_query($this->conn, $sql);
        return $result;
    }

    public function getChiTietNhanVien($manv) {
        $manv = mysqli_real_escape_string($this->conn, $manv);
        $sql = "SELECT nv.*, pb.TenPB, cv.TenChucVu 
                FROM nhanvien nv
                LEFT JOIN phongban pb ON nv.PhongBan = pb.TenPB
                LEFT JOIN tbl_chucvu cv ON nv.ChucVu = cv.TenChucVu
                WHERE nv.MaNV = '$manv'";
        $result = mysqli_query($this->conn, $sql);
        return mysqli_fetch_assoc($result);
    }
}