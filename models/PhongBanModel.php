<?php
class PhongBanModel {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    /* =========================
       LẤY TẤT CẢ PHÒNG BAN
    ========================== */
    public function getAllPhongBan() {
        $sql = "SELECT * FROM phongban ORDER BY MaPB DESC";
        return mysqli_query($this->conn, $sql);
    }

    /* =========================
       TÌM KIẾM PHÒNG BAN
    ========================== */
    public function searchPhongBan($keyword) {
        $keyword = mysqli_real_escape_string($this->conn, $keyword);

        if (is_numeric($keyword)) {
            $sql = "SELECT * FROM phongban 
                    WHERE MaPB = $keyword 
                       OR TenPB LIKE '%$keyword%'";
        } else {
            $sql = "SELECT * FROM phongban 
                    WHERE TenPB LIKE '%$keyword%'";
        }

        return mysqli_query($this->conn, $sql);
    }

    /* =========================
       LẤY PHÒNG BAN THEO ID
    ========================== */
    public function getPhongBanById($mapb) {
        $mapb = (int)$mapb;
        $sql = "SELECT * FROM phongban WHERE MaPB = $mapb";
        $result = mysqli_query($this->conn, $sql);

        return mysqli_num_rows($result) > 0
            ? mysqli_fetch_assoc($result)
            : null;
    }

    /* =========================
       THÊM PHÒNG BAN
    ========================== */
    public function insertPhongBan($tenpb, $mota) {
        $tenpb = mysqli_real_escape_string($this->conn, $tenpb);
        $mota  = mysqli_real_escape_string($this->conn, $mota);

        $sql = "INSERT INTO phongban (TenPB, MoTa)
                VALUES ('$tenpb', '$mota')";

        return mysqli_query($this->conn, $sql);
    }

    /* =========================
       CẬP NHẬT PHÒNG BAN
    ========================== */
    public function updatePhongBan($mapb, $tenpb, $mota) {
        $mapb = (int)$mapb;
        $tenpb = mysqli_real_escape_string($this->conn, $tenpb);
        $mota  = mysqli_real_escape_string($this->conn, $mota);

        $sql = "UPDATE phongban
                SET TenPB = '$tenpb',
                    MoTa  = '$mota'
                WHERE MaPB = $mapb";

        return mysqli_query($this->conn, $sql);
    }

    /* =========================
       XÓA PHÒNG BAN
    ========================== */
    public function deletePhongBan($mapb) {
        $mapb = (int)$mapb;
        $sql = "DELETE FROM phongban WHERE MaPB = $mapb";
        return mysqli_query($this->conn, $sql);
    }
}
