<?php
class PhanQuyenModel {
    private $conn;

    public function __construct($db){
        $this->conn = $db;
    }

    /* ================= LẤY DANH SÁCH QUYỀN ================= */
    public function getQuyenByTaiKhoan($maTK){
        $sql = "
            SELECT cn.MaCN, cn.TenChucNang
            FROM taikhoanvaitro tkvt
            JOIN phanquyen pq ON tkvt.MaVaiTro = pq.MaVaiTro
            JOIN chucnang cn ON pq.MaCN = cn.MaCN
            WHERE tkvt.MaTK = ?
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i",$maTK);
        $stmt->execute();

        $rs = $stmt->get_result();

        $data = [];
        while($row = $rs->fetch_assoc()){
            $data[] = $row['TenChucNang'];
        }

        return $data;
    }

    /* ================= CHECK QUYỀN ================= */
    public function hasPermission($maTK, $tenChucNang){
        $sql = "
            SELECT 1
            FROM taikhoanvaitro tkvt
            JOIN phanquyen pq ON tkvt.MaVaiTro = pq.MaVaiTro
            JOIN chucnang cn ON pq.MaCN = cn.MaCN
            WHERE tkvt.MaTK = ?
            AND cn.TenChucNang = ?
            LIMIT 1
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("is",$maTK,$tenChucNang);
        $stmt->execute();

        return $stmt->get_result()->num_rows > 0;
    }

    public function isAdminAccount(int $maTK): bool {
        $sql = "
            SELECT 1
            FROM taikhoanvaitro tkvt
            JOIN vaitro vt ON tkvt.MaVaiTro = vt.MaVaiTro
            WHERE tkvt.MaTK = ?
              AND LOWER(vt.TenVaiTro) = 'admin'
            LIMIT 1
        ";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("i", $maTK);
        $stmt->execute();
        $ok = $stmt->get_result()->num_rows > 0;
        $stmt->close();

        return $ok;
    }
    // 🔥 Lấy toàn bộ phân quyền
public function getAllPermissions(){
    $sql = "
        SELECT vt.TenVaiTro, cn.TenChucNang
        FROM phanquyen pq
        JOIN vaitro vt ON pq.MaVaiTro = vt.MaVaiTro
        JOIN chucnang cn ON pq.MaCN = cn.MaCN
        ORDER BY vt.TenVaiTro
    ";
    return mysqli_query($this->conn, $sql);
}

// 🔥 Lấy role của user
public function getRolesByUser($maTK){
    $sql = "
        SELECT vt.*
        FROM taikhoanvaitro tkvt
        JOIN vaitro vt ON tkvt.MaVaiTro = vt.MaVaiTro
        WHERE tkvt.MaTK = ?
    ";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $maTK);
    $stmt->execute();
    return $stmt->get_result();
}

// 🔥 Lấy quyền của user
public function getPermissionsByUser($maTK){
    $sql = "
        SELECT DISTINCT cn.TenChucNang
        FROM taikhoanvaitro tkvt
        JOIN phanquyen pq ON tkvt.MaVaiTro = pq.MaVaiTro
        JOIN chucnang cn ON pq.MaCN = cn.MaCN
        WHERE tkvt.MaTK = ?
    ";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $maTK);
    $stmt->execute();
    return $stmt->get_result();
}
// Lấy tất cả vai trò
public function getAllRoles(){
    return mysqli_query($this->conn, "SELECT * FROM vaitro");
}

// Lấy tất cả chức năng
public function getAllFunctions(){
    return mysqli_query($this->conn, "SELECT * FROM chucnang");
}

// Lấy quyền theo vai trò
public function getPermissionByRole($maVaiTro){
    $sql = "SELECT MaCN FROM phanquyen WHERE MaVaiTro = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $maVaiTro);
    $stmt->execute();

    $result = $stmt->get_result();
    $data = [];

    while($row = $result->fetch_assoc()){
        $data[] = $row['MaCN'];
    }

    return $data;
}

// Xóa quyền
public function deleteByRole($maVaiTro){
    $stmt = $this->conn->prepare("DELETE FROM phanquyen WHERE MaVaiTro=?");
    $stmt->bind_param("i", $maVaiTro);
    $stmt->execute();
}

// Thêm quyền
public function insertPermission($maVaiTro, $maCN){
    $stmt = $this->conn->prepare("
        INSERT INTO phanquyen(MaVaiTro, MaCN)
        VALUES(?,?)
    ");
    $stmt->bind_param("ii", $maVaiTro, $maCN);
    $stmt->execute();
}

// Lấy quyền mặc định của vai trò từ file seed database.sql
public function getDefaultPermissionsFromSeed($maVaiTro){
    $seedPath = __DIR__ . '/../database.sql';

    if (!file_exists($seedPath)) {
        return [];
    }

    $sql = file_get_contents($seedPath);
    if ($sql === false) {
        return [];
    }

    preg_match_all(
        '/INSERT\s+INTO\s+`?phanquyen`?\s*(?:\(\s*`?MaVaiTro`?\s*,\s*`?MaCN`?\s*\))?\s*VALUES\s*(.+?);/is',
        $sql,
        $matches,
        PREG_SET_ORDER
    );

    if (empty($matches)) {
        return [];
    }

    $defaults = [];
    foreach ($matches as $insertMatch) {
        $valuesBlock = $insertMatch[1] ?? '';
        preg_match_all('/\((\d+)\s*,\s*(\d+)\)/', $valuesBlock, $pairs, PREG_SET_ORDER);

        foreach ($pairs as $pair) {
            if ((int)$pair[1] === (int)$maVaiTro) {
                $defaults[] = (int)$pair[2];
            }
        }
    }

    return array_values(array_unique($defaults));
}
}
