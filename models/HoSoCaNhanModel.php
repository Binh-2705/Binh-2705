<?php
class HoSoCaNhanModel {

    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // ===== DANH SÁCH NHÂN VIÊN =====
    public function getAllNhanVien() {
        $sql = "SELECT MaNV, HoTen, PhongBan, ChucVu 
                FROM nhanvien
                ORDER BY MaNV";
        return mysqli_query($this->conn, $sql);
    }

    // ===== THÔNG TIN NHÂN VIÊN =====
    public function getChiTietNhanVien($manv) {
    $manv = mysqli_real_escape_string($this->conn, $manv);

    $sql = "SELECT nv.*,
                   pb.TenPB,
                   cv.TenChucVu
            FROM nhanvien nv
            LEFT JOIN phongban pb 
                ON nv.PhongBan = pb.MaPB
            LEFT JOIN tbl_chucvu cv 
                ON nv.ChucVu = cv.MaCV
            WHERE nv.MaNV = '$manv'";

    return mysqli_fetch_assoc(mysqli_query($this->conn, $sql));
}


    // ===== LƯƠNG GẦN NHẤT =====
    public function getLuongGanNhat($manv) {
    $manv = mysqli_real_escape_string($this->conn, $manv);

    $sql = "SELECT 
                l.*,
                (l.LuongCB + l.PhuCap + l.Thuong - l.KyLuat - l.KhauTru) AS TongLuong
            FROM luong l
            WHERE l.MaNV = '$manv'
            ORDER BY STR_TO_DATE(CONCAT(l.Thang, '-01'), '%Y-%m-%d') DESC
            LIMIT 1";

    $result = mysqli_query($this->conn, $sql);
    return $result ? mysqli_fetch_assoc($result) : null;
}


    // ===== HỢP ĐỒNG HIỆN TẠI =====
    public function getHopDongHienTai($manv) {
        $sql = "SELECT *
                FROM tbl_hopdong
                WHERE MaNV = '$manv'
                AND TrangThai = 'Còn hiệu lực'
                ORDER BY NgayBatDau DESC
                LIMIT 1";
        return mysqli_fetch_assoc(mysqli_query($this->conn, $sql));
    }

    // ===== CHẤM CÔNG GẦN NHẤT =====
    public function getChamCongThangGanNhat($manv) {
        $sql = "SELECT *
                FROM chamcong
                WHERE MaNV = '$manv'
                ORDER BY Thang DESC
                LIMIT 1";
        return mysqli_fetch_assoc(mysqli_query($this->conn, $sql));
    }

    // ===== KHEN THƯỞNG / KỶ LUẬT =====
    public function getKTKL($manv) {
        $sql = "SELECT *
                FROM tbl_khenthuongkyluat
                WHERE MaNV = '$manv'
                ORDER BY NgayRaQD DESC";
        return mysqli_query($this->conn, $sql);
    }
}
