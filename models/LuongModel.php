<?php
class LuongModel {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

   
    public function getAll() {
        $sql = "SELECT l.MaLuong, l.MaNV, nv.HoTen, l.Thang, l.LuongCB, l.PhuCap, l.Thuong, l.KhauTru,
                       (l.LuongCB + l.PhuCap + l.Thuong - l.KhauTru) AS TongLuong
                FROM luong l
                LEFT JOIN nhanvien nv ON l.MaNV = nv.MaNV
                ORDER BY l.Thang DESC";
        $result = mysqli_query($this->conn, $sql);
        $data = [];
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = $row;
            }
        }
        return $data;
    }

    public function timkiem($keyword) {
        $keyword_sql = mysqli_real_escape_string($this->conn, $keyword);
        $sql = "SELECT l.MaLuong, l.MaNV, nv.HoTen, l.Thang, l.LuongCB, l.PhuCap, l.Thuong, l.KhauTru,
                       (l.LuongCB + l.PhuCap + l.Thuong - l.KhauTru) AS TongLuong
                FROM luong l
                LEFT JOIN nhanvien nv ON l.MaNV = nv.MaNV
                WHERE l.MaNV LIKE '%$keyword_sql%'
                ORDER BY l.Thang DESC";
        $result = mysqli_query($this->conn, $sql);
        $data = [];
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = $row;
            }
        }
        return $data;
    }
    public function getNhanVien() {
        $sql = "SELECT MaNV, HoTen FROM nhanvien ORDER BY HoTen ASC";
        $result = mysqli_query($this->conn, $sql);
        $data = [];
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = $row;
            }
        }
        return $data;
    }

    public function insertLuong($data) {
        $maluong = mysqli_real_escape_string($this->conn, $data['maluong']);
        $manv = mysqli_real_escape_string($this->conn, $data['manv']);
        $thang = mysqli_real_escape_string($this->conn, $data['thang']);
        $luongcb = (float)$data['luongcb'];
        $phucap = (float)$data['phucap'];
        $thuong = (float)$data['thuong'];
        $songaylam = (int)$data['soNgayLam'];
        $khautru = (float)$data['khautru'];

        $sql = "INSERT INTO luong (MaLuong, MaNV, Thang, LuongCB, PhuCap, Thuong, KhauTru) 
                VALUES ('$maluong', '$manv', '$thang', $luongcb, $phucap, $thuong, $khautru)";
        return mysqli_query($this->conn, $sql);
    }
    public function getLuongById($maluong) {
        $maluong = mysqli_real_escape_string($this->conn, $maluong);
        $sql = "SELECT * FROM luong WHERE MaLuong='$maluong'";
        $result = mysqli_query($this->conn, $sql);
        return mysqli_fetch_assoc($result);
    }
    public function updateLuong($data) {
        $maluong = mysqli_real_escape_string($this->conn, $data['maluong']);
        $manv = mysqli_real_escape_string($this->conn, $data['manv']);
        $thang = mysqli_real_escape_string($this->conn, $data['thang']);
        $luongcb = floatval($data['luongcb']);
        $phucap = floatval($data['phucap']);
        $thuong = floatval($data['thuong']);
        $khautru = floatval($data['khautru']);
        $tongluong = $luongcb + $phucap + $thuong - $khautru;

        $sql = "UPDATE luong SET 
                    MaNV='$manv',
                    Thang='$thang',
                    LuongCB='$luongcb',
                    PhuCap='$phucap',
                    Thuong='$thuong',
                    KhauTru='$khautru',
                    TongLuong='$tongluong'
                WHERE MaLuong='$maluong'";
        return mysqli_query($this->conn, $sql);
    }
    public function deleteLuong($maluong) {
    $maluong = mysqli_real_escape_string($this->conn, $maluong);
    $sql = "DELETE FROM luong WHERE MaLuong='$maluong'";
    return mysqli_query($this->conn, $sql);
}
public function getAllForExcel() {
    $sql = "SELECT l.MaLuong, l.MaNV, nv.HoTen, l.Thang, l.LuongCB, l.PhuCap, l.Thuong, l.KhauTru,
                   (l.LuongCB + l.PhuCap + l.Thuong - l.KhauTru) AS TongLuong
            FROM luong l
            LEFT JOIN nhanvien nv ON l.MaNV = nv.MaNV
            ORDER BY l.Thang DESC";
    $result = mysqli_query($this->conn, $sql);
    $data = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
    }
    return $data;
}


}
