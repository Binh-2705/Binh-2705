<?php
class TaiKhoanModel {
    private $conn;

    public function __construct($conn){
        $this->conn = $conn;
    }
    public function dangNhap($tenDangNhap, $matKhau){
    $sql = "SELECT * FROM taikhoan WHERE TenDangNhap = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("s", $tenDangNhap);
    $stmt->execute();

    $tk = $stmt->get_result()->fetch_assoc();

    if ($tk && password_verify($matKhau, $tk['MatKhau'])) {
        return $tk;
    }
    return false;
}


    // LẤY DANH SÁCH + TÌM KIẾM
    public function getAll($key = ''){
        $sql = "SELECT * FROM taikhoan 
                WHERE TenDangNhap LIKE ?";
        $stmt = $this->conn->prepare($sql);
        $k = "%$key%";
        $stmt->bind_param("s", $k);
        $stmt->execute();
        return $stmt->get_result();
    }

    public function getById($id){
        return $this->conn
            ->query("SELECT * FROM taikhoan WHERE MaTK=$id")
            ->fetch_assoc();
    }

    // THÊM
    public function insert($user, $pass, $vaitro, $manv){
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        $sql = "INSERT INTO taikhoan 
                (TenDangNhap, MatKhau, VaiTro, MaNV)
                VALUES (?,?,?,?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssss", $user, $hash, $vaitro, $manv);
        return $stmt->execute();
    }

    // SỬA
    public function update($id, $vaitro, $manv){
        $sql = "UPDATE taikhoan 
                SET VaiTro=?, MaNV=?
                WHERE MaTK=?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssi", $vaitro, $manv, $id);
        return $stmt->execute();
    }

    // XÓA
    public function delete($id){
        return $this->conn->query(
            "DELETE FROM taikhoan WHERE MaTK=$id"
        );
    }
    public function resetMatKhau($tenDangNhap, $matKhauMoi){
    $sql = "UPDATE taikhoan SET MatKhau = ? WHERE TenDangNhap = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("ss", $matKhauMoi, $tenDangNhap);
    return $stmt->execute();
}

public function checkTenDangNhap($tenDangNhap){
    $sql = "SELECT * FROM taikhoan WHERE TenDangNhap = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("s", $tenDangNhap);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

}
