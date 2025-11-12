<?php
require_once 'ketnoi.php';

class NhanVienModel {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Lấy danh sách nhân viên
    public function getAllNhanVien() {
        $sql = "SELECT nv.MaNV, nv.HoTen, nv.GioiTinh, nv.NgaySinh, pb.TenPB, nv.ChucVu, l.LuongCB
                FROM nhanvien nv
                LEFT JOIN phongban pb ON nv.PhongBan = pb.MaPB
                LEFT JOIN (
                    SELECT l1.MaNV, l1.LuongCB
                    FROM luong l1
                    INNER JOIN (
                        SELECT MaNV, MAX(Thang) AS ThangMoiNhat
                        FROM luong
                        GROUP BY MaNV
                    ) l2 ON l1.MaNV = l2.MaNV AND l1.Thang = l2.ThangMoiNhat
                ) l ON nv.MaNV = l.MaNV";

        return mysqli_query($this->conn, $sql);
        
    }
     public function getAllPhongBan() {
        $sql = "SELECT MaPB, TenPB FROM phongban";
        return mysqli_query($this->conn, $sql);
    }

    public function insertNhanVien($manv, $hoten, $gioitinh, $ngaysinh, $phongban, $chucvu) {
    $manv = mysqli_real_escape_string($this->conn, $manv);
    $hoten = mysqli_real_escape_string($this->conn, $hoten);
    $gioitinh = mysqli_real_escape_string($this->conn, $gioitinh);
    $ngaysinh = mysqli_real_escape_string($this->conn, $ngaysinh);
    $phongban = mysqli_real_escape_string($this->conn, $phongban);
    $chucvu = mysqli_real_escape_string($this->conn, $chucvu);

    $sql = "INSERT INTO nhanvien (MaNV, HoTen, GioiTinh, NgaySinh, PhongBan, ChucVu)
            VALUES ('$manv', '$hoten', '$gioitinh', '$ngaysinh', '$phongban', '$chucvu')";
    return mysqli_query($this->conn, $sql);
}
    public function getNhanVienById($manv) {
        $manv = mysqli_real_escape_string($this->conn, $manv);
        $sql = "SELECT * FROM nhanvien WHERE MaNV='$manv'";
        $result = mysqli_query($this->conn, $sql);
        if ($result && mysqli_num_rows($result) > 0) {
            return mysqli_fetch_assoc($result);
        }
        return null;
    }
     public function updateNhanVien($manv, $hoten, $gioitinh, $ngaysinh, $phongban, $chucvu, $luong) {
        $manv = mysqli_real_escape_string($this->conn, $manv);
        $hoten = mysqli_real_escape_string($this->conn, $hoten);
        $gioitinh = mysqli_real_escape_string($this->conn, $gioitinh);
        $ngaysinh = mysqli_real_escape_string($this->conn, $ngaysinh);
        $phongban = mysqli_real_escape_string($this->conn, $phongban);
        $chucvu = mysqli_real_escape_string($this->conn, $chucvu);
        $luong = mysqli_real_escape_string($this->conn, $luong);

        $sql = "UPDATE nhanvien 
                SET HoTen='$hoten', GioiTinh='$gioitinh', NgaySinh='$ngaysinh',
                    PhongBan='$phongban', ChucVu='$chucvu', Luong='$luong'
                WHERE MaNV='$manv'";
        return mysqli_query($this->conn, $sql);
    }
    public function deleteNhanVien($manv) {
    $manv = mysqli_real_escape_string($this->conn, $manv);
    $sql = "DELETE FROM nhanvien WHERE MaNV='$manv'";
    return mysqli_query($this->conn, $sql);
}
public function searchNhanVien($keyword) {
    $keyword = mysqli_real_escape_string($this->conn, $keyword);
    $sql = "SELECT nv.MaNV, nv.HoTen, nv.GioiTinh, nv.NgaySinh, nv.PhongBan, nv.ChucVu,
                   l.LuongCB AS Luong
            FROM nhanvien nv
            LEFT JOIN (
                SELECT MaNV, LuongCB
                FROM luong
                WHERE (MaNV, Thang) IN (
                    SELECT MaNV, MAX(Thang)
                    FROM luong
                    GROUP BY MaNV
                )
            ) l ON nv.MaNV = l.MaNV
            WHERE nv.MaNV LIKE '%$keyword%' 
               OR nv.HoTen LIKE '%$keyword%' 
               OR nv.PhongBan LIKE '%$keyword%'";
    return mysqli_query($this->conn, $sql);
}


}
