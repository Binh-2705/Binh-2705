<?php
class NhanVienModel {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    /* ================== READ ================== */

    public function getAll() {
    $sql = "SELECT 
                nv.*, 
                bl.TenBac, 
                bl.HeSoLuong,
                nl.TenNgach
            FROM nhanvien nv
            LEFT JOIN bacluong bl ON nv.MaBac = bl.MaBac
            LEFT JOIN ngachluong nl ON bl.MaNgach = nl.MaNgach
            ORDER BY nv.MaNV ASC";
    return mysqli_query($this->conn, $sql);
    }

    public function countAll(): int {
        $sql = "SELECT COUNT(*) AS total FROM nhanvien";
        $result = mysqli_query($this->conn, $sql);
        $row = $result ? mysqli_fetch_assoc($result) : ['total' => 0];
        return (int)($row['total'] ?? 0);
    }

    public function getPage(int $page = 1, int $perPage = 10) {
        $page = max(1, $page);
        $perPage = max(1, $perPage);
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT 
                    nv.*, 
                    bl.TenBac, 
                    bl.HeSoLuong,
                    nl.TenNgach
                FROM nhanvien nv
                LEFT JOIN bacluong bl ON nv.MaBac = bl.MaBac
                LEFT JOIN ngachluong nl ON bl.MaNgach = nl.MaNgach
                ORDER BY nv.MaNV ASC
                LIMIT $offset, $perPage";

        return mysqli_query($this->conn, $sql);
    }
    public function getById($maNV) {
        $maNV = (int)$maNV;
        $sql = "SELECT * FROM nhanvien WHERE MaNV = $maNV";
        $rs = mysqli_query($this->conn, $sql);
        return ($rs && mysqli_num_rows($rs) > 0) ? mysqli_fetch_assoc($rs) : null;
    }
    public function getBacByNgach($maNgach) {
    // Bảo mật tránh SQL Injection
    $maNgach = mysqli_real_escape_string($this->conn, $maNgach);
    
    $sql = "SELECT MaBac, TenBac, HeSoLuong 
            FROM bacluong 
            WHERE MaNgach = '$maNgach' 
            ORDER BY HeSoLuong ASC";
            
    return mysqli_query($this->conn, $sql);
}
public function getAllNgachLuong() {
    $sql = "SELECT MaNgach, TenNgach FROM ngachluong ORDER BY TenNgach ASC";
    return mysqli_query($this->conn, $sql);
}

    public function getAllBacLuong() {
        $sql = "SELECT MaBac, TenBac FROM bacluong ORDER BY MaBac";
        return mysqli_query($this->conn, $sql);
    }
public function getNgachByBac($maBac) {
    $maBac = (int)$maBac;
    $sql = "SELECT MaNgach FROM bacluong WHERE MaBac = $maBac";
    $rs = mysqli_query($this->conn, $sql);
    return mysqli_fetch_assoc($rs);
}
    /* ================== CREATE ================== */

    public function insert($data) {
        $hoTen     = mysqli_real_escape_string($this->conn, $data['HoTen']);
        $gioiTinh  = mysqli_real_escape_string($this->conn, $data['GioiTinh']);
        $ngaySinh  = mysqli_real_escape_string($this->conn, $data['NgaySinh']);
        $email     = mysqli_real_escape_string($this->conn, $data['Email']);
        $dienThoai = mysqli_real_escape_string($this->conn, $data['DienThoai']);
        $trangThai = mysqli_real_escape_string($this->conn, $data['TrangThai']);
        $maBac     = (int)$data['MaBac'];
        $mahs = (int)$data['MaHS'];

        $sql = "INSERT INTO nhanvien
                (HoTen, GioiTinh, NgaySinh, Email, DienThoai, TrangThai, MaBac, MaHS)
                VALUES
                ('$hoTen','$gioiTinh','$ngaySinh','$email','$dienThoai','$trangThai',$maBac,$mahs)";

        return mysqli_query($this->conn, $sql);
    }

    /* ================== UPDATE ================== */

   public function update($data) {
    $sql = "UPDATE nhanvien SET 
            HoTen = ?, GioiTinh = ?, NgaySinh = ?, 
            Email = ?, DienThoai = ?, TrangThai = ?, MaBac = ?, MaHS = ? 
            WHERE MaNV = ?";

    $stmt = mysqli_prepare($this->conn, $sql);

    mysqli_stmt_bind_param($stmt, "ssssssiii",
        $data['HoTen'],
        $data['GioiTinh'],
        $data['NgaySinh'],
        $data['Email'],
        $data['DienThoai'],
        $data['TrangThai'],
        $data['MaBac'],
        $data['MaHS'],
        $data['MaNV']
    );

    mysqli_stmt_execute($stmt);

    $affected = mysqli_stmt_affected_rows($stmt);

    mysqli_stmt_close($stmt); // ⭐ phải đóng

    return $affected;
}
    /* ================== DELETE ================== */

   public function delete($maNV) {
    $sql = "DELETE FROM nhanvien WHERE MaNV = ?";
    $stmt = mysqli_prepare($this->conn, $sql);

    mysqli_stmt_bind_param($stmt, "i", $maNV);
    mysqli_stmt_execute($stmt);

    $affected = mysqli_stmt_affected_rows($stmt);

    mysqli_stmt_close($stmt); // ⭐ phải đóng

    return $affected;
}

    /* ================== CHECK ================== */

    public function exists($maNV) {
        $maNV = (int)$maNV;
        $sql = "SELECT MaNV FROM nhanvien WHERE MaNV = $maNV";
        $rs = mysqli_query($this->conn, $sql);
        return $rs && mysqli_num_rows($rs) > 0;
    }
    // Lấy hợp đồng đang còn hiệu lực của nhân viên
public function getHopDongConHieuLuc($maNV) {
    $maNV = (int)$maNV;
    $sql = "SELECT MaHopDong, MaBac FROM hopdong 
            WHERE MaNV = $maNV AND TrangThai = 'Còn hiệu lực' 
            LIMIT 1";
    $rs = mysqli_query($this->conn, $sql);
    return mysqli_fetch_assoc($rs);
}

// Lấy giá trị lương thực tế dựa trên MaBac
public function getLuongThucTeByBac($maBac) {
    $maBac = (int)$maBac;
    $sql = "SELECT (LuongCoSo * HeSoLuong) as Luong FROM bacluong WHERE MaBac = $maBac";
    $rs = mysqli_query($this->conn, $sql);
    $row = mysqli_fetch_assoc($rs);
    return $row['Luong'] ?? 0;
}

// Cập nhật MaBac cho Hợp đồng
public function updateMaBacHopDong($maHD, $maBac) {
    $sql = "UPDATE hopdong SET MaBac = ? WHERE MaHopDong = ?";
    $stmt = mysqli_prepare($this->conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $maBac, $maHD);
    return mysqli_stmt_execute($stmt);
}

// Ghi lịch sử lương
public function insertLichSuLuong($data) {
    $sql = "INSERT INTO lichsu_luong (MaHopDong, LuongCu, LuongMoi, NgayApDung, LyDo) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($this->conn, $sql);
    mysqli_stmt_bind_param($stmt, "iddss", 
        $data['MaHopDong'], $data['LuongCu'], $data['LuongMoi'], 
        $data['NgayApDung'], $data['LyDo']
    );
    return mysqli_stmt_execute($stmt);
}
public function search($keyword) {
    $keyword = "%" . $keyword . "%";

    $sql = "SELECT nv.*, bl.TenBac, bl.HeSoLuong, nl.TenNgach
            FROM nhanvien nv
            LEFT JOIN bacluong bl ON nv.MaBac = bl.MaBac
            LEFT JOIN ngachluong nl ON bl.MaNgach = nl.MaNgach
            WHERE nv.HoTen LIKE ? OR nv.MaNV LIKE ?
            ORDER BY nv.MaNV ASC";

    $stmt = mysqli_prepare($this->conn, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $keyword, $keyword);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    mysqli_stmt_close($stmt); // ⭐ QUAN TRỌNG

    return $result;
}
}
