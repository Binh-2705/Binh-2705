<?php
// models/KhenThuongModel.php

class KhenThuongModel {
    private $conn;
    private $table = 'tbl_khenthuongkyluat'; 

    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    // 0. HELPER: Kiểm tra Mã Quyết định tồn tại
    public function checkMaQDExists($maQD) {
        $maQD = mysqli_real_escape_string($this->conn, $maQD); 
        $sql = "SELECT MaQuyetDinh FROM {$this->table} WHERE MaQuyetDinh = '$maQD'";
        $result = mysqli_query($this->conn, $sql);
        return $result && mysqli_num_rows($result) > 0;
    }

    // 1. READ: Lấy tất cả Quyết định (Dùng cho trang Index)
    public function getAllQuyetDinh($keyword = '') {
        $sql = "SELECT 
                    qd.MaQuyetDinh, qd.MaNV, qd.LoaiQD, qd.NgayRaQD, 
                    qd.TieuDe, qd.NoiDung, qd.GiaTri, 
                    nv.HoTen 
                FROM 
                    {$this->table} qd
                JOIN 
                    nhanvien nv ON qd.MaNV = nv.MaNV";

        if (!empty($keyword)) {
            $keyword = mysqli_real_escape_string($this->conn, $keyword);
            $sql .= " WHERE qd.MaQuyetDinh LIKE '%$keyword%' 
                     OR qd.MaNV LIKE '%$keyword%'
                     OR qd.TieuDe LIKE '%$keyword%'
                     OR nv.HoTen LIKE '%$keyword%'";
        }
        
        $sql .= " ORDER BY qd.NgayRaQD DESC, qd.MaQuyetDinh DESC";
        return mysqli_query($this->conn, $sql);
    }

    // 2. CREATE: Thêm mới Quyết định
    public function insertQuyetDinh($maQD, $maNV, $loaiQD, $ngayRaQD, $tieuDe, $noiDung, $giaTri) {
        $maQD = mysqli_real_escape_string($this->conn, $maQD); 
        $maNV = mysqli_real_escape_string($this->conn, $maNV);
        $loaiQD = mysqli_real_escape_string($this->conn, $loaiQD);
        $ngayRaQD = mysqli_real_escape_string($this->conn, $ngayRaQD);
        $tieuDe = mysqli_real_escape_string($this->conn, $tieuDe); 
        $noiDung = mysqli_real_escape_string($this->conn, $noiDung); 
        $giaTri = (float)mysqli_real_escape_string($this->conn, $giaTri); 

        $sql = "INSERT INTO {$this->table} (MaQuyetDinh, MaNV, LoaiQD, NgayRaQD, TieuDe, NoiDung, GiaTri)
                VALUES ('$maQD', '$maNV', '$loaiQD', '$ngayRaQD', '$tieuDe', '$noiDung', $giaTri)";
        return mysqli_query($this->conn, $sql);
    }

    // 3. READ: Lấy Quyết định theo ID (QUAN TRỌNG: ĐÃ THÊM JOIN)
    public function getQuyetDinhById($maQD) {
        $maQD = mysqli_real_escape_string($this->conn, $maQD);
        $sql = "SELECT 
                    qd.*, nv.HoTen 
                FROM 
                    {$this->table} qd
                JOIN 
                    nhanvien nv ON qd.MaNV = nv.MaNV
                WHERE 
                    qd.MaQuyetDinh='$maQD'";
        
        $result = mysqli_query($this->conn, $sql);
        // Trả về mảng kết hợp bao gồm cả HoTen
        return $result ? mysqli_fetch_assoc($result) : null;
    }

    // 4. UPDATE: Cập nhật Quyết định
    public function updateQuyetDinh($maQD, $maNV, $loaiQD, $ngayRaQD, $tieuDe, $noiDung, $giaTri) {
        $maQD = mysqli_real_escape_string($this->conn, $maQD);
        $maNV = mysqli_real_escape_string($this->conn, $maNV);
        $loaiQD = mysqli_real_escape_string($this->conn, $loaiQD);
        $ngayRaQD = mysqli_real_escape_string($this->conn, $ngayRaQD);
        $tieuDe = mysqli_real_escape_string($this->conn, $tieuDe);
        $noiDung = mysqli_real_escape_string($this->conn, $noiDung);
        $giaTri = (float)mysqli_real_escape_string($this->conn, $giaTri);

        $sql = "UPDATE {$this->table} 
                SET MaNV='$maNV', LoaiQD='$loaiQD', NgayRaQD='$ngayRaQD', 
                    TieuDe='$tieuDe', NoiDung='$noiDung', GiaTri=$giaTri
                WHERE MaQuyetDinh='$maQD'";
        return mysqli_query($this->conn, $sql);
    }

    // 5. DELETE: Xóa Quyết định
    public function deleteQuyetDinh($maQD) {
        $maQD = mysqli_real_escape_string($this->conn, $maQD);
        $sql = "DELETE FROM {$this->table} WHERE MaQuyetDinh='$maQD'";
        return mysqli_query($this->conn, $sql);
    }
    
    // 6. HELPER: Lấy danh sách Nhân viên để chọn trong Form
    public function getAllNhanVienForSelect() {
        $sql = "SELECT MaNV, HoTen FROM nhanvien ORDER BY HoTen ASC";
        return mysqli_query($this->conn, $sql);
    }
}
?>