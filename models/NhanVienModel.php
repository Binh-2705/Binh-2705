<?php
// models/NhanVienModel.php

require_once 'ketnoi.php';

class NhanVienModel {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // THÊM: Lấy danh sách Chức vụ để dùng trong Form Thêm/Sửa
    public function getAllChucVu() {
        $sql = "SELECT MaCV, TenChucVu FROM tbl_chucvu ORDER BY TenChucVu ASC";
        $result = mysqli_query($this->conn, $sql);
        $data = [];
        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = $row;
            }
        }
        return $data;
    }

    // READ: Lấy danh sách nhân viên (Sử dụng MaPB và MaCV, JOIN để lấy Tên)
    public function getAllNhanVien() {
        $sql = "SELECT 
                nv.MaNV, 
                nv.HoTen, 
                nv.GioiTinh, 
                nv.NgaySinh, 
                pb.TenPB, 
                cv.TenChucVu, 
                l.LuongCB
            FROM 
                nhanvien nv
            LEFT JOIN 
                phongban pb ON nv.MaPB = pb.MaPB 
            LEFT JOIN 
                tbl_chucvu cv ON nv.MaCV = cv.MaCV
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
    
    // READ: Lấy danh sách Phòng ban
    public function getAllPhongBan() {
        $sql = "SELECT MaPB, TenPB FROM phongban";
        return mysqli_query($this->conn, $sql);
    }

    // CREATE: Thêm mới Nhân viên
    public function insertNhanVien($manv, $hoten, $gioitinh, $ngaysinh, $maPB, $maCV) {
        $manv = mysqli_real_escape_string($this->conn, $manv);
        $hoten = mysqli_real_escape_string($this->conn, $hoten);
        $gioitinh = mysqli_real_escape_string($this->conn, $gioitinh);
        $ngaysinh = mysqli_real_escape_string($this->conn, $ngaysinh);
        $maPB = mysqli_real_escape_string($this->conn, $maPB); 
        $maCV = mysqli_real_escape_string($this->conn, $maCV); 

        $sql = "INSERT INTO nhanvien (MaNV, HoTen, GioiTinh, NgaySinh, MaPB, MaCV)
                VALUES ('$manv', '$hoten', '$gioitinh', '$ngaysinh', '$maPB', '$maCV')";
        return mysqli_query($this->conn, $sql);
    }
    
    // READ: Lấy thông tin Nhân viên theo ID
    public function getNhanVienById($manv) {
        $manv = mysqli_real_escape_string($this->conn, $manv);
        $sql = "SELECT * FROM nhanvien WHERE MaNV='$manv'";
        $result = mysqli_query($this->conn, $sql);
        if ($result && mysqli_num_rows($result) > 0) {
            return mysqli_fetch_assoc($result);
        }
        return null;
    }
    
    // UPDATE: Cập nhật Nhân viên (Bỏ $luong vì nó thuộc bảng luong)
    public function updateNhanVien($manv, $hoten, $gioitinh, $ngaysinh, $maPB, $maCV) {
        $manv = mysqli_real_escape_string($this->conn, $manv);
        $hoten = mysqli_real_escape_string($this->conn, $hoten);
        $gioitinh = mysqli_real_escape_string($this->conn, $gioitinh);
        $ngaysinh = mysqli_real_escape_string($this->conn, $ngaysinh);
        $maPB = mysqli_real_escape_string($this->conn, $maPB); 
        $maCV = mysqli_real_escape_string($this->conn, $maCV); 

        $sql = "UPDATE nhanvien 
                SET HoTen='$hoten', GioiTinh='$gioitinh', NgaySinh='$ngaysinh',
                    MaPB='$maPB', MaCV='$maCV'
                WHERE MaNV='$manv'";
        return mysqli_query($this->conn, $sql);
    }
    
    // DELETE: Xóa Nhân viên
    public function deleteNhanVien($manv) {
        $manv = mysqli_real_escape_string($this->conn, $manv);
        $sql = "DELETE FROM nhanvien WHERE MaNV='$manv'";
        return mysqli_query($this->conn, $sql);
    }

    // READ: Tìm kiếm Nhân viên
    public function searchNhanVien($keyword) {
        $keyword = mysqli_real_escape_string($this->conn, $keyword);
        
        $sql = "SELECT 
                    nv.MaNV, nv.HoTen, nv.GioiTinh, nv.NgaySinh, pb.TenPB, cv.TenChucVu,
                    l.LuongCB AS Luong
                FROM 
                    nhanvien nv
                LEFT JOIN 
                    phongban pb ON nv.MaPB = pb.MaPB
                LEFT JOIN 
                    tbl_chucvu cv ON nv.MaCV = cv.MaCV
                LEFT JOIN (
                    SELECT MaNV, LuongCB
                    FROM luong
                    WHERE (MaNV, Thang) IN (
                        SELECT MaNV, MAX(Thang)
                        FROM luong
                        GROUP BY MaNV
                    )
                ) l ON nv.MaNV = l.MaNV
                WHERE 
                    nv.MaNV LIKE '%$keyword%' 
                    OR nv.HoTen LIKE '%$keyword%' 
                    OR pb.TenPB LIKE '%$keyword%'";
        return mysqli_query($this->conn, $sql);
    }

    // CHECK: Kiểm tra trùng Mã Nhân viên
    function checkma( $manv) {
        $sql = "Select * from nhanvien where MaNV='$manv'";
        $result = mysqli_query($this->conn, $sql);
        if (mysqli_num_rows($result) > 0) {
            return true;
        } else
            return false;
    }
}
?>