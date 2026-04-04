<?php
// models/ChucVuModel.php

class ChucVuModel {
    private $conn;
    private $table = 'chucvu';

    public function __construct($conn) {
        $this->conn = $conn;
    }

    /* ==============================
       1. DANH SÁCH + TÌM KIẾM
    ============================== */
    public function getList($keyword = '') {
        $keyword = $this->conn->real_escape_string($keyword);

        $sql = "SELECT 
                    MaCV,
                    TenCV,
                    HeSoChucVu,
                    PhuCap
                FROM {$this->table}";

        if (!empty($keyword)) {
            $sql .= " WHERE TenCV LIKE '%$keyword%'
                      OR MaCV LIKE '%$keyword%'";
        }

        $sql .= " ORDER BY MaCV ASC";

        $result = $this->conn->query($sql);
        $data = [];

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }
        return $data;
    }

    /* ==============================
       2. THÊM CHỨC VỤ (AUTO_INCREMENT)
    ============================== */
    public function add($tenCV, $heSo, $phuCap) {
        $tenCV = $this->conn->real_escape_string($tenCV);
        $heSo = $heSo !== '' ? (float)$heSo : null;
        $phuCap = $phuCap !== '' ? (float)$phuCap : null;

        $sql = "INSERT INTO {$this->table} (TenCV, HeSoChucVu, PhuCap)
                VALUES ('$tenCV', " .
                ($heSo === null ? "NULL" : $heSo) . ", " .
                ($phuCap === null ? "NULL" : $phuCap) . ")";

        return $this->conn->query($sql);
    }

    /* ==============================
       3. LẤY THEO ID
    ============================== */
    public function getById($maCV) {
        $maCV = (int)$maCV;

        $sql = "SELECT * FROM {$this->table} WHERE MaCV = $maCV";
        $result = $this->conn->query($sql);

        return ($result && $result->num_rows > 0)
            ? $result->fetch_assoc()
            : null;
    }

    /* ==============================
       4. CẬP NHẬT
    ============================== */
    public function update($maCV, $tenCV, $heSo, $phuCap) {
        $maCV = (int)$maCV;
        $tenCV = $this->conn->real_escape_string($tenCV);
        $heSo = $heSo !== '' ? (float)$heSo : null;
        $phuCap = $phuCap !== '' ? (float)$phuCap : null;

        $sql = "UPDATE {$this->table}
                SET TenCV = '$tenCV',
                    HeSoChucVu = " . ($heSo === null ? "NULL" : $heSo) . ",
                    PhuCap = " . ($phuCap === null ? "NULL" : $phuCap) . "
                WHERE MaCV = $maCV";

        return $this->conn->query($sql);
    }

    /* ==============================
       5. XÓA
    ============================== */
    public function delete($maCV) {
        $maCV = (int)$maCV;
        $sql = "DELETE FROM {$this->table} WHERE MaCV = $maCV";
        return $this->conn->query($sql);
    }
}
?>
