<?php
class LuongModel {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

   
    public function getAll() {
        $sql = "SELECT l.MaLuong, l.MaNV, nv.HoTen, l.Thang, l.LuongCB, l.PhuCap, l.Thuong, l.KyLuat, l.KhauTru,
                       (l.LuongCB + l.PhuCap + l.Thuong - l.KyLuat - l.KhauTru) AS TongLuong

                FROM luong l
                LEFT JOIN nhanvien nv ON l.MaNV = nv.MaNV
                ORDER BY l.Thang DESC";
        $result = mysqli_query($this->conn, $sql);
        $data = [];
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = $row;
            }
        }
        return $data;
    }
    public function getLuongCBFromHopDong($maNV) {
    $sql = "SELECT LuongCoBan
            FROM tbl_hopdong
            WHERE MaNV = ?
              AND TrangThai = 'Còn hiệu lực'
            ORDER BY NgayBatDau DESC
            LIMIT 1";

    $stmt = $this->conn->prepare($sql);
    if (!$stmt) {
        die("SQL ERROR: " . $this->conn->error);
    }

    $stmt->bind_param("s", $maNV);
    $stmt->execute();

    $result = $stmt->get_result()->fetch_assoc();
    return $result ? (float)$result['LuongCoBan'] : 0;
}
public function getThuongVaKyLuat($maNV, $thang) {
    $sql = "SELECT
                SUM(CASE WHEN LoaiQD = ? THEN GiaTri ELSE 0 END) AS TongThuong,
                SUM(CASE WHEN LoaiQD = ? THEN GiaTri ELSE 0 END) AS TongKyLuat
            FROM tbl_khenthuongkyluat
            WHERE MaNV = ?
              AND DATE_FORMAT(NgayRaQD, '%Y-%m') = ?";

    $stmt = $this->conn->prepare($sql);
if (!$stmt) {
    die("Prepare failed: " . $this->conn->error);
}


    $loaiThuong = 'Khen thưởng';
    $loaiKyLuat = 'Kỷ luật';

    $stmt->bind_param("ssss", $loaiThuong, $loaiKyLuat, $maNV, $thang);
    $stmt->execute();

    return $stmt->get_result()->fetch_assoc();
}





    public function timkiem($keyword) {
        $keyword_sql = mysqli_real_escape_string($this->conn, $keyword);
        $sql = "SELECT  l.MaLuong, l.MaNV, nv.HoTen, l.Thang, l.LuongCB, l.PhuCap, l.Thuong, l.KyLuat, l.KhauTru,
                       (l.LuongCB + l.PhuCap + l.Thuong - l.KyLuat - l.KhauTru) AS TongLuong
                FROM luong l
                LEFT JOIN nhanvien nv ON l.MaNV = nv.MaNV
                WHERE l.MaNV LIKE '%$keyword_sql%'
                ORDER BY l.Thang DESC";
        $result = mysqli_query($this->conn, $sql);
        $data = [];
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = $row;
            }
        }
        return $data;
    }
    public function getNhanVien() {
        $sql = "SELECT MaNV, HoTen FROM nhanvien ORDER BY HoTen ASC";
        $result = mysqli_query($this->conn, $sql);
        $data = [];
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = $row;
            }
        }
        return $data;
    }

    public function insertLuong($data) {
    $maluong = mysqli_real_escape_string($this->conn, $data['maluong']);
    $manv    = mysqli_real_escape_string($this->conn, $data['manv']);
    $thang   = mysqli_real_escape_string($this->conn, $data['thang']);

    // 1️⃣ Lương cơ bản từ hợp đồng
    $luongcb = $this->getLuongCBFromHopDong($manv);

    // 2️⃣ Phụ cấp (nhập tay)
    $phucap = (float)$data['phucap'];

    // 3️⃣ Thưởng & kỷ luật từ quyết định
    $ktkl   = $this->getThuongVaKyLuat($manv, $thang);
    $thuong = (float)$ktkl['TongThuong'];
    $kyLuat = (float)$ktkl['TongKyLuat'];

    // 4️⃣ Khấu trừ ngày công
    $khauTruNgayCong = $this->tinhKhauTruNgayCong($manv, $thang);

    // 5️⃣ Insert (KHÔNG GỘP)
    $sql = "INSERT INTO luong 
        (MaLuong, MaNV, Thang, LuongCB, PhuCap, Thuong, KyLuat, KhauTru)
        VALUES (
            '$maluong',
            '$manv',
            '$thang',
            $luongcb,
            $phucap,
            $thuong,
            $kyLuat,
            $khauTruNgayCong
        )";

    return mysqli_query($this->conn, $sql);
}

    public function getLuongById($maluong) {
        $maluong = mysqli_real_escape_string($this->conn, $maluong);
        $sql = "SELECT * FROM luong WHERE MaLuong='$maluong'";
        $result = mysqli_query($this->conn, $sql);
        return mysqli_fetch_assoc($result);
    }
    public function updateLuong($data) {
    $maluong = mysqli_real_escape_string($this->conn, $data['maluong']);
    $manv    = mysqli_real_escape_string($this->conn, $data['manv']);
    $thang   = mysqli_real_escape_string($this->conn, $data['thang']);

    // 1️⃣ Lương cơ bản từ hợp đồng
    $luongcb = $this->getLuongCBFromHopDong($manv);

    // 2️⃣ Phụ cấp (nhập tay)
    $phucap = (float)$data['phucap'];

    // 3️⃣ Thưởng & kỷ luật
    $ktkl   = $this->getThuongVaKyLuat($manv, $thang);
    $thuong = (float)$ktkl['TongThuong'];
    $kyLuat = (float)$ktkl['TongKyLuat'];

    // 4️⃣ Khấu trừ ngày công
    $khauTruNgayCong = $this->tinhKhauTruNgayCong($manv, $thang);

    // 5️⃣ Update (KHÔNG GỘP)
    $sql = "UPDATE luong SET
                MaNV    = '$manv',
                Thang   = '$thang',
                LuongCB = $luongcb,
                PhuCap  = $phucap,
                Thuong  = $thuong,
                KyLuat  = $kyLuat,
                KhauTru = $khauTruNgayCong
            WHERE MaLuong = '$maluong'";

    return mysqli_query($this->conn, $sql);
}

    public function deleteLuong($maluong) {
    $maluong = mysqli_real_escape_string($this->conn, $maluong);
    $sql = "DELETE FROM luong WHERE MaLuong='$maluong'";
    return mysqli_query($this->conn, $sql);
}
public function getAllForExcel() {
    $sql = "SELECT l.MaLuong, l.MaNV, nv.HoTen, l.Thang, l.LuongCB, l.PhuCap, l.Thuong, l.KyLuat, l.KhauTru,
                   (l.LuongCB + l.PhuCap + l.Thuong - l.KyLuat - l.KhauTru) AS TongLuong

            FROM luong l
            LEFT JOIN nhanvien nv ON l.MaNV = nv.MaNV
            ORDER BY l.Thang DESC";
    $result = mysqli_query($this->conn, $sql);
    $data = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
    }
    return $data;
}

 public function checkma( $maluong)
{
    $sql = "Select * from luong where MaLuong='$maluong'";
    $result = mysqli_query($this->conn, $sql);
    if (mysqli_num_rows($result) > 0) {
        return true; //trùng mã tg
    } else
        return false; //ko trùng ãm
}
public function tinhKhauTruNgayCong($maNV, $thang) {
    $sql = "SELECT SoNgayLam
            FROM chamcong
            WHERE MaNV = ?
              AND Thang = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("ss", $maNV, $thang);
    $stmt->execute();

    $row = $stmt->get_result()->fetch_assoc();
    if (!$row) return 0;

    $soNgayLam = (int)$row['SoNgayLam'];
    if ($soNgayLam >= 26) return 0;

    $luongCB = $this->getLuongCBFromHopDong($maNV);
    $luongNgay = $luongCB / 26;

    return (26 - $soNgayLam) * $luongNgay;
}



}
