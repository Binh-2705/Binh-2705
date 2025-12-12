<?php
// models/ChucVuModel.php

class ChucVuModel {
    private $conn;
    private $table = 'tbl_chucvu'; 

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // 1. READ (Lấy danh sách chức vụ, có thể kèm tìm kiếm)
    public function getList($keyword = '') {
        $keyword = $this->conn->real_escape_string($keyword);
        
        $sql = "SELECT 
                    c.MaCV, 
                    c.TenChucVu, 
                    COUNT(nv.MaNV) as SoLuongNV 
                FROM 
                    {$this->table} c
                LEFT JOIN 
                    nhanvien nv ON c.MaCV = nv.MaCV"; 
        
        if (!empty($keyword)) {
            // Áp dụng tìm kiếm theo Mã CV hoặc Tên Chức vụ
            $sql .= " WHERE c.TenChucVu LIKE '%$keyword%' OR c.MaCV LIKE '%$keyword%'";
        }
        
        // Bắt buộc phải GROUP BY khi sử dụng COUNT
        $sql .= " GROUP BY c.MaCV, c.TenChucVu";
        $sql .= " ORDER BY c.MaCV ASC";

        $result = $this->conn->query($sql);
        $data = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }
        return $data;
    }

    // 2. CREATE (Thêm mới)
    public function add($maCV, $tenChucVu) {
        $maCV = $this->conn->real_escape_string($maCV);
        $tenChucVu = $this->conn->real_escape_string($tenChucVu);

        $sql = "INSERT INTO {$this->table} (MaCV, TenChucVu)
                VALUES ('$maCV', '$tenChucVu')";
        
        return $this->conn->query($sql);
    }
    
    // READ (Lấy chi tiết theo ID)
    public function getById($id) {
        $id = $this->conn->real_escape_string($id);
        $sql = "SELECT * FROM {$this->table} WHERE MaCV = '$id'";
        $result = $this->conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        return null;
    }

    // 3. UPDATE (Cập nhật)
    public function update($maCV, $tenChucVu) {
        $maCV = $this->conn->real_escape_string($maCV);
        $tenChucVu = $this->conn->real_escape_string($tenChucVu);

        $sql = "UPDATE {$this->table} SET
                TenChucVu = '$tenChucVu'
                WHERE MaCV = '$maCV'";
        
        return $this->conn->query($sql);
    }
    
    // 4. DELETE (Xóa)
    public function delete($id) {
        $id = $this->conn->real_escape_string($id);
        $sql = "DELETE FROM {$this->table} WHERE MaCV = '$id'";
        return $this->conn->query($sql);
    }

    // Kiểm tra Mã chức vụ có tồn tại không
    public function checkMaCV($maCV) {
        $maCV = $this->conn->real_escape_string($maCV);
        $sql = "SELECT MaCV FROM {$this->table} WHERE MaCV = '$maCV'";
        $result = $this->conn->query($sql);
        return $result && $result->num_rows > 0;
    }
}
?>