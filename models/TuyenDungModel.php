<?php

class TuyenDungModel {
    private $conn;
    private $table = 'tuyendung'; 

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // 1. READ (Lấy danh sách ứng viên, có tìm kiếm)
    public function getList($keyword = '') {
        $keyword = $this->conn->real_escape_string($keyword);
        
        $sql = "SELECT * FROM {$this->table}"; 
        
        if (!empty($keyword)) {
            $sql .= " WHERE HoTen LIKE '%$keyword%' OR ViTriUngTuyen LIKE '%$keyword%' OR SoDienThoai LIKE '%$keyword%'";
        }
        
        $sql .= " ORDER BY NgayNop DESC";

        $result = $this->conn->query($sql);
        $data = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }
        return $data;
    }

    // 2. CREATE (Thêm mới ứng viên)
    public function add($hoTen, $email, $sdt, $viTri, $ngayNop, $ghiChu) {
    // 1. Tìm MaUV lớn nhất hiện tại
    $sql_max = "SELECT MAX(MaUV) as max_id FROM tuyendung";
    $result = $this->conn->query($sql_max);
    $row = $result->fetch_assoc();
    
    // Nếu bảng chưa có ai, bắt đầu từ 1. Nếu có rồi, lấy mã đó + 1
    $new_id = ($row['max_id'] != null) ? $row['max_id'] + 1 : 1;

    // 2. Làm sạch dữ liệu đầu vào
    $hoTen = $this->conn->real_escape_string($hoTen);
    $email = $this->conn->real_escape_string($email);
    $sdt = $this->conn->real_escape_string($sdt);
    $viTri = $this->conn->real_escape_string($viTri);
    $ngayNop = $this->conn->real_escape_string($ngayNop);
    $ghiChu = $this->conn->real_escape_string($ghiChu);

    // 3. Thực hiện chèn với MaUV tự tính toán (truyền cả biến $new_id vào câu lệnh)
    $sql = "INSERT INTO tuyendung (MaUV, HoTen, Email, SoDienThoai, ViTriUngTuyen, NgayNop, GhiChu)
            VALUES ('$new_id', '$hoTen', '$email', '$sdt', '$viTri', '$ngayNop', '$ghiChu')";
    
    return $this->conn->query($sql);
}
    
    // 3. READ (Lấy chi tiết theo ID)
    public function getById($id) {
        $id = $this->conn->real_escape_string($id);
        $sql = "SELECT * FROM {$this->table} WHERE MaUV = '$id'";
        $result = $this->conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        return null;
    }

    // 4. UPDATE (Cập nhật trạng thái/thông tin)
    public function update($id, $trangThai, $ghiChu) {
        $id = $this->conn->real_escape_string($id);
        $trangThai = $this->conn->real_escape_string($trangThai);
        $ghiChu = $this->conn->real_escape_string($ghiChu);

        $sql = "UPDATE {$this->table} SET
                TrangThai = '$trangThai',
                GhiChu = '$ghiChu'
                WHERE MaUV = '$id'";
        
        return $this->conn->query($sql);
    }
    
    // 5. DELETE (Xóa ứng viên)
    public function delete($id) {
        $id = $this->conn->real_escape_string($id);
        $sql = "DELETE FROM {$this->table} WHERE MaUV = '$id'";
        return $this->conn->query($sql);
    }

    //6. DUYỆT ỨNG VIÊN (Cập nhật trạng thái)
    public function updateStatus($id, $status) {
    $id = $this->conn->real_escape_string($id);
    // Chuyển đổi giá trị status từ URL thành chữ tiếng Việt để lưu vào database
    $trangThaiText = ($status == 'approved') ? 'Đã duyệt' : 'Từ chối';
    
    $sql = "UPDATE {$this->table} SET TrangThai = '$trangThaiText' WHERE MaUV = '$id'";
    return $this->conn->query($sql);
}
}
?>