<?php
class NghiPhepModel {
    private $conn;
    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Lấy toàn bộ danh sách nghỉ phép
    public function getAllNghiPhep() {
        $sql = "SELECT np.MaNP, np.MaNV, nv.HoTen, np.TuNgay, np.DenNgay, 
                       np.LyDo, np.TrangThai, np.NgayDangKy
                FROM nghiphep np
                LEFT JOIN nhanvien nv ON np.MaNV = nv.MaNV
                ORDER BY np.NgayDangKy DESC";
        return $this->conn->query($sql);
    }

    // Tìm kiếm theo mã NV hoặc tên NV
    public function searchNghiPhep($keyword) {
        $keyword = $this->conn->real_escape_string($keyword);
        $sql = "SELECT np.MaNP, np.MaNV, nv.HoTen, np.TuNgay, np.DenNgay, 
                       np.LyDo, np.TrangThai, np.NgayDangKy
                FROM nghiphep np
                LEFT JOIN nhanvien nv ON np.MaNV = nv.MaNV
                WHERE nv.HoTen LIKE '%$keyword%' OR np.MaNV LIKE '%$keyword%'
                ORDER BY np.NgayDangKy DESC";
        return $this->conn->query($sql);
    }

    // Lấy nghỉ phép theo mã
    public function getNghiPhepById($manp) {
        $manp = $this->conn->real_escape_string($manp);
        $sql = "SELECT * FROM nghiphep WHERE MaNP='$manp'";
        return $this->conn->query($sql);
    }

    // Lấy danh sách nhân viên
    public function getAllNhanVien() {
        return $this->conn->query("SELECT MaNV, HoTen FROM nhanvien");
    }

    // Sinh mã nghỉ phép mới
    public function getNewMaNP() {
        $result = $this->conn->query("SELECT MaNP FROM nghiphep ORDER BY MaNP DESC LIMIT 1");
        if ($result && $row = $result->fetch_assoc()) {
            $num = intval(substr($row['MaNP'], 2)) + 1;
            return "NP" . str_pad($num, 3, "0", STR_PAD_LEFT);
        }
        return "NP001";
    }

    // Thêm nghỉ phép
    public function insertNghiPhep($manp, $manv, $tungay, $denngay, $lydo, $trangthai, $ngaydangky) {
        $sql = "INSERT INTO nghiphep (MaNP, MaNV, TuNgay, DenNgay, LyDo, TrangThai, NgayDangKy)
                VALUES ('$manp', '$manv', '$tungay', '$denngay', '$lydo', '$trangthai', '$ngaydangky')";
        return $this->conn->query($sql);
    }

    // Cập nhật nghỉ phép
    public function updateNghiPhep($manp, $manv, $tungay, $denngay, $lydo, $trangthai) {
        $sql = "UPDATE nghiphep
                SET MaNV='$manv', TuNgay='$tungay', DenNgay='$denngay',
                    LyDo='$lydo', TrangThai='$trangthai'
                WHERE MaNP='$manp'";
        return $this->conn->query($sql);
    }

    // Xóa nghỉ phép
    public function xoa($manp) {
        $manp = mysqli_real_escape_string($this->conn, $manp);
        $sql = "DELETE FROM nghiphep WHERE MaNP='$manp'";
        return mysqli_query($this->conn, $sql);
    }

    // Kiểm tra trùng mã
    public function checkma($manp) {
        $sql = "SELECT * FROM nghiphep WHERE MaNP='$manp'";
        $result = mysqli_query($this->conn, $sql);
        return mysqli_num_rows($result) > 0;
    }

    public function duyet($manp) {
        $manp = mysqli_real_escape_string($this->conn, $manp);
        $sql = "UPDATE nghiphep SET TrangThai = 'Đã duyệt' WHERE MaNP = '$manp'";
        return mysqli_query($this->conn, $sql);
    }

    public function tuchoi($manp) {
        $manp = mysqli_real_escape_string($this->conn, $manp);
        $sql = "UPDATE nghiphep SET TrangThai = 'Từ chối' WHERE MaNP = '$manp'";
        return mysqli_query($this->conn, $sql);
    }

}
?>
