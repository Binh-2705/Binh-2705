<?php
class GiangVienModel {
    private $conn;
    public function __construct($conn) { $this->conn = $conn; }
    
    public function getAll() {
        $sql = "SELECT * FROM giangvien ORDER BY HoTen";
        return $this->conn->query($sql);
    }
    
    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM giangvien WHERE MaGV = ?");
        $stmt->bind_param("s", $id);
        $stmt->execute();
        return $stmt->get_result();
    }
    
    public function insert($data) {
        $sql = "INSERT INTO giangvien (MaGV, HoTen, ChuyenMon, KinhNghiem, Email, SDT, GhiChu) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssssss", 
            $data['MaGV'], $data['HoTen'], $data['ChuyenMon'], 
            $data['KinhNghiem'], $data['Email'], $data['SDT'], $data['GhiChu']);
        return $stmt->execute();
    }
    
    public function update($data) {
        $sql = "UPDATE giangvien SET HoTen=?, ChuyenMon=?, KinhNghiem=?, Email=?, SDT=?, GhiChu=? 
                WHERE MaGV=?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssssss", 
            $data['HoTen'], $data['ChuyenMon'], $data['KinhNghiem'], 
            $data['Email'], $data['SDT'], $data['GhiChu'], $data['MaGV']);
        return $stmt->execute();
    }
    
    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM giangvien WHERE MaGV = ?");
        $stmt->bind_param("s", $id);
        return $stmt->execute();
    }
    
    public function search($keyword) {
        $sql = "SELECT * FROM giangvien WHERE MaGV LIKE ? OR HoTen LIKE ? OR ChuyenMon LIKE ?";
        $stmt = $this->conn->prepare($sql);
        $like = "%".$keyword."%";
        $stmt->bind_param("sss", $like, $like, $like);
        $stmt->execute();
        return $stmt->get_result();
    }
    
    public function getThongKeGiangVien() {
        $sql = "SELECT 
                    COUNT(*) as tongGiangVien,
                    AVG(KinhNghiem) as kinhNghiemTB,
                    COUNT(DISTINCT ChuyenMon) as soChuyenMon
                FROM giangvien";
        
        $result = $this->conn->query($sql);
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        return ['tongGiangVien' => 0, 'kinhNghiemTB' => 0, 'soChuyenMon' => 0];
    }
    
    public function getTopGiangVien($limit = 5) {
        $sql = "SELECT gv.*, 
                       COUNT(d.MaDT) as soKhoaHoc,
                       SUM(d.ChiPhi) as tongChiPhi,
                       COUNT(DISTINCT dh.MaNV) as tongHocVien
                FROM giangvien gv
                LEFT JOIN daotao d ON gv.MaGV = d.GiangVien
                LEFT JOIN daotao_hocvien dh ON d.MaDT = dh.MaDT
                GROUP BY gv.MaGV, gv.HoTen, gv.ChuyenMon, gv.KinhNghiem
                ORDER BY soKhoaHoc DESC, tongHocVien DESC
                LIMIT ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        return $stmt->get_result();
    }
    
    public function countAll() {
        $result = $this->conn->query("SELECT COUNT(*) as total FROM giangvien");
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['total'] ?? 0;
        }
        return 0;
    }
}
?>