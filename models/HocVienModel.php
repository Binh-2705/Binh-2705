<?php
class HocVienModel {
    private $conn;
    public function __construct($conn) { $this->conn = $conn; }
    
    public function getByKhoaHoc($maDT) {
        $sql = "SELECT dh.*, nv.HoTen, nv.ChucVu, nv.PhongBan 
                FROM daotao_hocvien dh 
                JOIN nhanvien nv ON dh.MaNV = nv.MaNV 
                WHERE dh.MaDT = ? 
                ORDER BY nv.HoTen";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $maDT);
        $stmt->execute();
        return $stmt->get_result();
    }
    
    public function getNhanVienChuaThamGia($maDT) {
        $sql = "SELECT nv.* FROM nhanvien nv 
                WHERE nv.MaNV NOT IN (
                    SELECT MaNV FROM daotao_hocvien WHERE MaDT = ?
                ) 
                ORDER BY nv.HoTen";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $maDT);
        $stmt->execute();
        return $stmt->get_result();
    }
    
    public function insert($data) {
        $sql = "INSERT INTO daotao_hocvien (MaDT, MaNV, Diem, TrangThai, DanhGia, GhiChu) 
                VALUES (?, ?, NULL, 'Đang tham gia', NULL, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sss", $data['MaDT'], $data['MaNV'], $data['GhiChu']);
        return $stmt->execute();
    }
    
    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM daotao_hocvien WHERE ID = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
    
    public function updateDiem($data) {
        $sql = "UPDATE daotao_hocvien SET Diem = ?, KetQua = ?, GhiChu = ? WHERE ID = ?";
        $stmt = $this->conn->prepare($sql);
        
        $diem = $data['Diem'];
        $ketQua = ($diem >= 5) ? 'Đạt' : 'Không đạt';
        
        $stmt->bind_param("sssi", $data['Diem'], $ketQua, $data['GhiChu'], $data['ID']);
        return $stmt->execute();
    }
    
    public function updateDiemDanh($id, $trangThai) {
        $sql = "UPDATE daotao_hocvien SET TrangThai = ? WHERE ID = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $trangThai, $id);
        return $stmt->execute();
    }
    
    public function getDanhGia($maDT) {
        $sql = "SELECT dh.*, nv.HoTen 
                FROM daotao_hocvien dh 
                JOIN nhanvien nv ON dh.MaNV = nv.MaNV 
                WHERE dh.MaDT = ? AND dh.DanhGia IS NOT NULL 
                ORDER BY dh.NgayDanhGia DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $maDT);
        $stmt->execute();
        return $stmt->get_result();
    }
    
    public function thongKeHocVien() {
        $sql = "SELECT 'Tổng học viên' as ChiTieu, COUNT(DISTINCT MaNV) as GiaTri FROM daotao_hocvien
                UNION ALL
                SELECT 'Học viên đạt', COUNT(*) FROM daotao_hocvien WHERE KetQua = 'Đạt'
                UNION ALL
                SELECT 'Học viên không đạt', COUNT(*) FROM daotao_hocvien WHERE KetQua = 'Không đạt'
                UNION ALL
                SELECT 'Đang tham gia', COUNT(*) FROM daotao_hocvien WHERE TrangThai = 'Đang tham gia'
                UNION ALL
                SELECT 'Hoàn thành', COUNT(*) FROM daotao_hocvien WHERE TrangThai = 'Hoàn thành'";
        
        return $this->conn->query($sql);
    }
    
    public function getTopHocVien($limit = 10) {
        $sql = "SELECT dh.MaNV, nv.HoTen, nv.PhongBan, 
                       COUNT(DISTINCT dh.MaDT) as soKhoaHoc,
                       AVG(dh.Diem) as diemTrungBinh,
                       SUM(CASE WHEN dh.KetQua = 'Đạt' THEN 1 ELSE 0 END) as soKhoaDat
                FROM daotao_hocvien dh
                JOIN nhanvien nv ON dh.MaNV = nv.MaNV
                WHERE dh.Diem IS NOT NULL
                GROUP BY dh.MaNV, nv.HoTen, nv.PhongBan
                HAVING COUNT(dh.MaDT) >= 1
                ORDER BY diemTrungBinh DESC, soKhoaHoc DESC
                LIMIT ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        return $stmt->get_result();
    }
    
    public function countAll() {
        $result = $this->conn->query("SELECT COUNT(DISTINCT MaNV) as total FROM daotao_hocvien");
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['total'] ?? 0;
        }
        return 0;
    }
}
?>