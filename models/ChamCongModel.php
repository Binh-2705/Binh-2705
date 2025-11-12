<?php 
class ChamCongModel {
    private $conn;
     public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getAllChamCong() {
        $sql = "SELECT cc.MaCC, cc.MaNV, nv.HoTen, cc.Thang, cc.SoNgayLam, cc.SoNgayNghi, cc.GhiChu
                FROM chamcong cc
                LEFT JOIN nhanvien nv ON cc.MaNV = nv.MaNV
                ORDER BY cc.Thang DESC";
        return $this->conn->query($sql);
    }
    public function searchChamCong($keyword) {
        $keyword = $this->conn->real_escape_string($keyword);
        $sql = "SELECT cc.MaCC, cc.MaNV, nv.HoTen, cc.Thang, cc.SoNgayLam, cc.SoNgayNghi, cc.GhiChu
                FROM chamcong cc
                LEFT JOIN nhanvien nv ON cc.MaNV = nv.MaNV
                WHERE nv.HoTen LIKE '%$keyword%' OR cc.MaNV LIKE '%$keyword%'
                ORDER BY cc.Thang DESC";
        return $this->conn->query($sql);
    }
    public function getChamCongById($macc) {
        $macc = $this->conn->real_escape_string($macc);
        $sql = "SELECT * FROM chamcong WHERE MaCC='$macc'";
        return $this->conn->query($sql);
    }

    public function updateChamCong($macc, $manv, $thang, $songaylam, $songaynghi, $ghichu) {
        $sql = "UPDATE chamcong 
                SET MaNV='$manv', Thang='$thang', SoNgayLam='$songaylam', SoNgayNghi='$songaynghi', GhiChu='$ghichu'
                WHERE MaCC='$macc'";
        return $this->conn->query($sql);
    }

    public function getAllNhanVien() {
        return $this->conn->query("SELECT MaNV, HoTen FROM nhanvien");
    }
    public function getNewMaCC() {
        $result = $this->conn->query("SELECT MaCC FROM chamcong ORDER BY MaCC DESC LIMIT 1");
        if ($result && $row = $result->fetch_assoc()) {
            $num = intval(substr($row['MaCC'], 2)) + 1;
            return "CC" . str_pad($num, 3, "0", STR_PAD_LEFT);
        }
        return "CC001";
    }

    public function insertChamCong($macc, $manv, $thang, $songaylam, $songaynghi, $ghichu) {
        $sql = "INSERT INTO chamcong (MaCC, MaNV, Thang, SoNgayLam, SoNgayNghi, GhiChu)
                VALUES ('$macc', '$manv', '$thang', '$songaylam', '$songaynghi', '$ghichu')";
        return $this->conn->query($sql);
    }
    public function xoa($macc) {
        $macc = mysqli_real_escape_string($this->conn, $macc);
        $sql = "DELETE FROM chamcong WHERE MaCC = '$macc'";
        return mysqli_query($this->conn, $sql);
    }
    public function getSoNgayLam($manv, $thang) {
        $manv = mysqli_real_escape_string($this->conn, $manv);
        $thang = mysqli_real_escape_string($this->conn, $thang);

        $sql = "SELECT SoNgayLam FROM chamcong WHERE MaNV='$manv' AND Thang='$thang'";
        $result = mysqli_query($this->conn, $sql);

        if ($result && $row = mysqli_fetch_assoc($result)) {
            return $row['SoNgayLam'];
        }
        return 0;
    }

}
?>