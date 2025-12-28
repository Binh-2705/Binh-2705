<?php
class DaoTaoModel {
    private $conn;
    public function __construct($conn) { $this->conn = $conn; }

    public function getAll() { 
        return $this->conn->query("SELECT * FROM daotao ORDER BY NgayBatDau DESC"); 
    }

    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM daotao WHERE MaDT=?");
        $stmt->bind_param("s", $id);
        $stmt->execute();
        return $stmt->get_result();
    }

    public function insert($data) {
        $stmt = $this->conn->prepare("INSERT INTO daotao VALUES (?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param("sssssssss", $data['MaDT'], $data['TenKhoaHoc'], $data['NoiDung'],
            $data['NgayBatDau'], $data['NgayKetThuc'], $data['GiangVien'], $data['DiaDiem'],
            $data['ChiPhi'], $data['GhiChu']);
        return $stmt->execute();
    }

    public function update($data) {
        $stmt = $this->conn->prepare("UPDATE daotao SET TenKhoaHoc=?, NoiDung=?, NgayBatDau=?, NgayKetThuc=?, GiangVien=?, DiaDiem=?, ChiPhi=?, GhiChu=? WHERE MaDT=?");
        $stmt->bind_param("sssssssss", $data['TenKhoaHoc'], $data['NoiDung'], $data['NgayBatDau'],
            $data['NgayKetThuc'], $data['GiangVien'], $data['DiaDiem'], $data['ChiPhi'],
            $data['GhiChu'], $data['MaDT']);
        return $stmt->execute();
    }

    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM daotao WHERE MaDT=?");
        $stmt->bind_param("s", $id);
        return $stmt->execute();
    }

    public function search($keyword) {
        $sql = "SELECT * FROM daotao WHERE MaDT LIKE ? OR TenKhoaHoc LIKE ?";
        $stmt = $this->conn->prepare($sql);
        $like = "%".$keyword."%";
        $stmt->bind_param("ss", $like, $like);
        $stmt->execute();
        return $stmt->get_result();
    }
    
    // === THỐNG KÊ ===
    
    public function thongKe() {
        $result = [];
        
        // Tổng số khóa học
        $sql1 = "SELECT COUNT(*) as tongKhoaHoc FROM daotao";
        $res1 = $this->conn->query($sql1);
        $result['tongKhoaHoc'] = $res1->fetch_assoc()['tongKhoaHoc'] ?? 0;
        
        // Tổng chi phí
        $sql2 = "SELECT SUM(ChiPhi) as tongChiPhi FROM daotao";
        $res2 = $this->conn->query($sql2);
        $result['tongChiPhi'] = $res2->fetch_assoc()['tongChiPhi'] ?? 0;
        
        // Tổng giảng viên
        $sql3 = "SELECT COUNT(DISTINCT GiangVien) as tongGiangVien FROM daotao WHERE GiangVien IS NOT NULL AND GiangVien != ''";
        $res3 = $this->conn->query($sql3);
        $result['tongGiangVien'] = $res3->fetch_assoc()['tongGiangVien'] ?? 0;
        
        // Tổng học viên
        $sql4 = "SELECT COUNT(DISTINCT MaNV) as tongHocVien FROM daotao_hocvien";
        $res4 = $this->conn->query($sql4);
        $result['tongHocVien'] = $res4->fetch_assoc()['tongHocVien'] ?? 0;
        
        // Khóa học theo tháng
        $sql5 = "SELECT DATE_FORMAT(NgayBatDau, '%Y-%m') as Thang, COUNT(*) as soLuong 
                FROM daotao 
                WHERE NgayBatDau >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                GROUP BY DATE_FORMAT(NgayBatDau, '%Y-%m') 
                ORDER BY Thang DESC";
        $res5 = $this->conn->query($sql5);
        $result['theoThang'] = $res5;
        
        return $result;
    }
    
    public function getTopKhoaHoc($limit = 5) {
        $sql = "SELECT d.*, 
                       COUNT(DISTINCT dh.MaNV) as soHocVien,
                       SUM(CASE WHEN dh.TrangThai = 'Hoàn thành' THEN 1 ELSE 0 END) as hoanThanh
                FROM daotao d
                LEFT JOIN daotao_hocvien dh ON d.MaDT = dh.MaDT
                GROUP BY d.MaDT, d.TenKhoaHoc, d.GiangVien, d.ChiPhi
                HAVING COUNT(dh.MaNV) > 0
                ORDER BY soHocVien DESC, hoanThanh DESC
                LIMIT ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        return $stmt->get_result();
    }
    
    public function countAll() {
        $result = $this->conn->query("SELECT COUNT(*) as total FROM daotao");
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['total'] ?? 0;
        }
        return 0;
    }
}
?>