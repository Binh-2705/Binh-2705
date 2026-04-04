<?php
class NgachLuongModel {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    /* =========================
       LẤY TẤT CẢ PHÒNG BAN
    ========================== */
    public function getAllNgachLuong() {
        $sql = "SELECT * FROM ngachluong ORDER BY MaNgach ASC";
        return mysqli_query($this->conn, $sql);
    }

   

    /* =========================
       LẤY NGẠCH LƯƠNG THEO ID
    ========================== */
    public function getNgachLuongById($mangach) {
        $mangach = (int)$mangach;
        $sql = "SELECT * FROM ngachluong WHERE MaNgach = $mangach";
        $result = mysqli_query($this->conn, $sql);

        return mysqli_num_rows($result) > 0
            ? mysqli_fetch_assoc($result)
            : null;
    }

    
}
