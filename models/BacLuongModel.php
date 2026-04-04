<?php

require_once 'ketnoi.php';

class BacLuongModel {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

   
   public function getAll() {
    $sql = "
        SELECT bl.*, nl.TenNgach, (bl.HeSoLuong * bl.LuongCoSo) AS LuongTinh
        FROM bacluong bl
        JOIN ngachluong nl ON bl.MaNgach = nl.MaNgach
        ORDER BY nl.TenNgach ASC, bl.HeSoLuong ASC
    ";
    return mysqli_query($this->conn, $sql);
}

    public function getByNgach($MaNgach) {
        $sql = "
            SELECT * FROM bacluong
            WHERE MaNgach = ?
            ORDER BY HeSoLuong
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $MaNgach);
        $stmt->execute();
        return $stmt->get_result();
    }

    public function getById($MaBac) {
        $sql = "SELECT * FROM bacluong WHERE MaBac = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $MaBac);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }


    public function tinhLuong($MaBac) {
        $sql = "
            SELECT HeSoLuong, LuongCoSo,
                   (HeSoLuong * LuongCoSo) AS LuongTinh
            FROM bacluong
            WHERE MaBac = ?
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $MaBac);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
}
