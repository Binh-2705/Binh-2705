<?php
// models/HopDongModel.php - ĐÃ SỬA THÊM LuongCoBan

class HopDongModel {
    private $conn;
    private $table = 'tbl_hopdong'; 

    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    // 0. HELPER: Kiểm tra Mã hợp đồng có bị trùng không (khi thêm mới)
    public function checkMaHDExists($maHD) {
        $maHD = mysqli_real_escape_string($this->conn, $maHD); 
        $sql = "SELECT MaHD FROM {$this->table} WHERE MaHD = '$maHD'";
        $result = mysqli_query($this->conn, $sql);
        return $result && mysqli_num_rows($result) > 0;
    }
    
    // 1. READ: Lấy tất cả Hợp đồng (JOIN với Nhân viên để lấy tên)
    public function getAllHopDong($keyword = '') {
        $sql = "SELECT 
                    hd.MaHD, hd.MaNV, hd.LoaiHopDong, hd.NgayBatDau, hd.NgayKetThuc, hd.TrangThai, hd.LuongCoBan, 
                    nv.HoTen 
                FROM 
                    {$this->table} hd
                JOIN 
                    nhanvien nv ON hd.MaNV = nv.MaNV";
        
        // Xử lý logic Tìm kiếm
        if (!empty($keyword)) {
            $keyword = mysqli_real_escape_string($this->conn, $keyword);
            $sql .= " WHERE hd.MaHD LIKE '%$keyword%' 
                      OR hd.MaNV LIKE '%$keyword%'
                      OR nv.HoTen LIKE '%$keyword%'
                      OR hd.LoaiHopDong LIKE '%$keyword%'";
        }

        $sql .= " ORDER BY hd.NgayBatDau DESC";
        return mysqli_query($this->conn, $sql);
    }

    // 2. READ: Lấy Hợp đồng theo ID (Dùng MaHD)
    public function getHopDongById($maHD) {
        $maHD = mysqli_real_escape_string($this->conn, $maHD);
        $sql = "SELECT * FROM {$this->table} WHERE MaHD='$maHD'";
        $result = mysqli_query($this->conn, $sql);
        return $result ? mysqli_fetch_assoc($result) : null;
    }

    // 3. CREATE: Thêm mới Hợp đồng (THÊM $luongCoBan)
    public function insertHopDong($maHD, $maNV, $loaiHopDong, $ngayBatDau, $ngayKetThuc, $trangThai, $luongCoBan) {
        $maHD = mysqli_real_escape_string($this->conn, $maHD);
        $maNV = mysqli_real_escape_string($this->conn, $maNV);
        $loaiHopDong = mysqli_real_escape_string($this->conn, $loaiHopDong);
        $ngayBatDau = mysqli_real_escape_string($this->conn, $ngayBatDau);
        $trangThai = mysqli_real_escape_string($this->conn, $trangThai);
        $luongCoBan = (float)$luongCoBan; // Đảm bảo là số

        $ngayKetThuc_sql = !empty($ngayKetThuc) ? "'" . mysqli_real_escape_string($this->conn, $ngayKetThuc) . "'" : 'NULL';

        // THÊM cột LuongCoBan vào câu lệnh INSERT
        $sql = "INSERT INTO {$this->table} (MaHD, MaNV, LoaiHopDong, NgayBatDau, NgayKetThuc, TrangThai, LuongCoBan)
                VALUES ('$maHD', '$maNV', '$loaiHopDong', '$ngayBatDau', $ngayKetThuc_sql, '$trangThai', $luongCoBan)";
        return mysqli_query($this->conn, $sql);
    }
    
    // 4. UPDATE: Cập nhật Hợp đồng (THÊM $luongCoBan)
    public function updateHopDong($maHD, $maNV, $loaiHopDong, $ngayBatDau, $ngayKetThuc, $trangThai, $luongCoBan) {
        $maHD = mysqli_real_escape_string($this->conn, $maHD);
        $maNV = mysqli_real_escape_string($this->conn, $maNV);
        $loaiHopDong = mysqli_real_escape_string($this->conn, $loaiHopDong);
        $ngayBatDau = mysqli_real_escape_string($this->conn, $ngayBatDau);
        $trangThai = mysqli_real_escape_string($this->conn, $trangThai);
        $luongCoBan = (float)$luongCoBan; // Đảm bảo là số

        $ngayKetThuc_sql = !empty($ngayKetThuc) ? "'" . mysqli_real_escape_string($this->conn, $ngayKetThuc) . "'" : 'NULL';

        // THÊM LuongCoBan vào câu lệnh UPDATE
        $sql = "UPDATE {$this->table} 
                SET MaNV='$maNV', LoaiHopDong='$loaiHopDong', NgayBatDau='$ngayBatDau', 
                    NgayKetThuc=$ngayKetThuc_sql, TrangThai='$trangThai', LuongCoBan=$luongCoBan
                WHERE MaHD='$maHD'";
        return mysqli_query($this->conn, $sql);
    }
    
    // 5. DELETE: Xóa Hợp đồng
    public function deleteHopDong($maHD) {
        $maHD = mysqli_real_escape_string($this->conn, $maHD);
        $sql = "DELETE FROM {$this->table} WHERE MaHD='$maHD'";
        return mysqli_query($this->conn, $sql);
    }

    // 6. HELPER: Lấy danh sách Nhân viên để chọn trong Form
    public function getAllNhanVienForSelect() {
        $sql = "SELECT MaNV, HoTen FROM nhanvien ORDER BY HoTen ASC";
        return mysqli_query($this->conn, $sql);
    }
}
?>