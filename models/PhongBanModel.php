<?php
class PhongBanModel {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getAllPhongBan() {
        $sql = "SELECT * FROM phongban";
        return $this->conn->query($sql);
    }

    public function searchPhongBan($keyword) {
        $keyword = $this->conn->real_escape_string($keyword);
        $sql = "SELECT * FROM phongban WHERE TenPB LIKE '%$keyword%' OR MaPB LIKE '%$keyword%'";
        return $this->conn->query($sql);
    }
    public function getPhongBanById($mapb) {
        $mapb = $this->conn->real_escape_string($mapb);
        $sql = "SELECT * FROM phongban WHERE MaPB='$mapb'";
        $result = $this->conn->query($sql);
        return $result->num_rows > 0 ? $result->fetch_assoc() : null;
    }
    public function insertPhongBan($mapb, $tenpb, $mota){
        $mapb = mysqli_real_escape_string($this->conn, $mapb);
        $tenpb = mysqli_real_escape_string($this->conn, $tenpb);
        $mota = mysqli_real_escape_string($this->conn, $mota);
        $check = $this->getPhongBanById($mapb);
        if ($check) return false;
        $sql = "INSERT INTO phongban (MaPB, TenPB, MoTa) VALUES ('$mapb', '$tenpb', '$mota')";
        return mysqli_query($this->conn, $sql);

    }
    public function updatePhongBan($mapb, $tenpb, $mota) {
        $mapb = $this->conn->real_escape_string($mapb);
        $tenpb = $this->conn->real_escape_string($tenpb);
        $mota = $this->conn->real_escape_string($mota);

        $sql = "UPDATE phongban SET TenPB='$tenpb', MoTa='$mota' WHERE MaPB='$mapb'";
        return $this->conn->query($sql);
    }

    public function deletePhongBan($mapb) {
        $mapb = $this->conn->real_escape_string($mapb);
        $sql = "DELETE FROM phongban WHERE MaPB='$mapb'";
        return $this->conn->query($sql);
    }
}
