<?php
class HopDongModel {
    private $conn;
    private $table = 'hopdong';

    public function __construct($conn) {
        $this->conn = $conn;
    }

    /* ================== HELPER ================== */

    public function checkSoHopDongExists($soHopDong) {
        $soHopDong = mysqli_real_escape_string($this->conn, $soHopDong);
        $sql = "SELECT 1 FROM {$this->table} WHERE SoHopDong='$soHopDong' LIMIT 1";
        $rs = mysqli_query($this->conn, $sql);
        return $rs && mysqli_num_rows($rs) > 0;
    }

    public function updateMaBac($maHD, $maBac) {
    $sql = "UPDATE hopdong SET MaBac = ? WHERE MaHopDong = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("ii", $maBac, $maHD);
    return $stmt->execute();
}



    public function getLuongByHopDong($maHopDong) {

    $sql = "SELECT bl.HeSoLuong * bl.LuongCoSo AS Luong
            FROM hopdong hd
            JOIN bacluong bl ON hd.MaBac = bl.MaBac
            WHERE hd.MaHopDong = ?";

    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $maHopDong);
    $stmt->execute();

    $rs = $stmt->get_result();
    $row = $rs->fetch_assoc();

    return $row['Luong'] ?? 0;
}

public function getLuongByBac($maBac) {

    $sql = "SELECT HeSoLuong * LuongCoSo AS Luong
            FROM bacluong
            WHERE MaBac = ?";

    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $maBac);
    $stmt->execute();

    $rs = $stmt->get_result();
    $row = $rs->fetch_assoc();

    return $row['Luong'] ?? 0;
}



public function themLichSuLuong($data) {

    $sql = "INSERT INTO lichsu_luong
            (MaHopDong, LuongCu, LuongMoi, NgayApDung, LyDo)
            VALUES (?, ?, ?, ?, ?)";

    $stmt = $this->conn->prepare($sql);

    $stmt->bind_param(
        "iddss",
        $data['MaHopDong'],
        $data['LuongCu'],
        $data['LuongMoi'],
        $data['NgayApDung'],
        $data['LyDo']
    );

    return $stmt->execute();
}


    /* ================== CHẤM DỨT ================== */
   public function updateTrangThaiChamdut($maHD) {
    $sql = "
        UPDATE hopdong
        SET TrangThai = 'Hết hiệu lực',
            NgayKetThuc = CURDATE()
        WHERE MaHopDong = ?
          AND TrangThai <> 'Hết hiệu lực'
    ";

    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $maHD);
    return $stmt->execute();
}

public function getLichSuLuongByHopDong($maHopDong)
{
    $maHopDong = (int)$maHopDong;

    $sql = "SELECT *
            FROM lichsu_luong
            WHERE MaHopDong = $maHopDong
            ORDER BY NgayApDung DESC";

    return mysqli_query($this->conn, $sql);
}


    /* ================== GIA HẠN ================== */
   public function insertHopDongGiaHan($data) {
    $maNV = (int)$data['MaNV'];
    $maBac = (int)$data['MaBac'];
    $hopDongGoc = (int)$data['HopDongGoc'];

    $soHD = mysqli_real_escape_string($this->conn, $data['SoHopDong']);
    $loai = mysqli_real_escape_string($this->conn, $data['LoaiHopDong']);
    $ngayBD = mysqli_real_escape_string($this->conn, $data['NgayBatDau']);

    $ngayKT = !empty($data['NgayKetThuc'])
        ? "'" . mysqli_real_escape_string($this->conn, $data['NgayKetThuc']) . "'"
        : "NULL";

    $sql = "INSERT INTO hopdong
            (MaNV, MaBac, SoHopDong, LoaiHopDong, NgayKy, NgayBatDau, NgayKetThuc, TrangThai, HopDongGoc)
            VALUES
            ($maNV, $maBac, '$soHD', '$loai', CURDATE(), '$ngayBD', $ngayKT, 'Còn hiệu lực', $hopDongGoc)";

    return mysqli_query($this->conn, $sql);
}


    /* ================== DANH SÁCH ================== */
    public function getAllHopDong($filters = []) {

        $sql = "SELECT
                    hd.MaHopDong,
                    hd.SoHopDong,
                    hd.LoaiHopDong,
                    hd.NgayKy,
                    hd.NgayBatDau,
                    hd.NgayKetThuc,
                    hd.TrangThai,

                    nv.HoTen,

                    bl.TenBac,
                    bl.HeSoLuong,
                    bl.LuongCoSo,
                    (bl.LuongCoSo * bl.HeSoLuong) AS LuongThucTe,

                   CASE
    WHEN hd.TrangThai = 'Hết hiệu lực' THEN 0
    WHEN hd.NgayKetThuc IS NULL THEN NULL
    ELSE DATEDIFF(hd.NgayKetThuc, CURDATE())
END AS SoNgayConLai

                FROM hopdong hd
                LEFT JOIN nhanvien nv ON hd.MaNV = nv.MaNV
                LEFT JOIN bacluong bl ON hd.MaBac = bl.MaBac
                WHERE 1=1";

        /* TỪ KHÓA */
        if (!empty($filters['keyword'])) {
            $kw = mysqli_real_escape_string($this->conn, $filters['keyword']);
            $sql .= " AND (
                hd.SoHopDong LIKE '%$kw%' OR
                nv.HoTen LIKE '%$kw%' OR
                hd.LoaiHopDong LIKE '%$kw%'
            )";
        }

        /* LOẠI HĐ */
        if (!empty($filters['loaiHD'])) {
            $loai = mysqli_real_escape_string($this->conn, $filters['loaiHD']);
            $sql .= " AND hd.LoaiHopDong='$loai'";
        }

        /* TRẠNG THÁI */
        if (!empty($filters['trangThai'])) {

    if ($filters['trangThai'] === 'con') {
        $sql .= " AND hd.TrangThai='Còn hiệu lực'
                  AND (hd.NgayKetThuc IS NULL OR hd.NgayKetThuc >= CURDATE())";
    }

    if ($filters['trangThai'] === 'het') {
        $sql .= " AND hd.TrangThai='Còn hiệu lực'
                  AND hd.NgayKetThuc < CURDATE()";
    }

    if ($filters['trangThai'] === 'chamdut') {
        $sql .= " AND hd.TrangThai='Hết hiệu lực'";
    }
}


        /* NGÀY */
        if (!empty($filters['tuNgay'])) {
            $sql .= " AND hd.NgayBatDau >= '{$filters['tuNgay']}'";
        }
        if (!empty($filters['denNgay'])) {
            $sql .= " AND hd.NgayBatDau <= '{$filters['denNgay']}'";
        }

        $sql .= " ORDER BY hd.MaHopDong DESC";
        return mysqli_query($this->conn, $sql);
    }

    public function countHopDong($filters = []) {
        $sql = "SELECT COUNT(*) AS total
                FROM hopdong hd
                LEFT JOIN nhanvien nv ON hd.MaNV = nv.MaNV
                WHERE 1=1";

        if (!empty($filters['keyword'])) {
            $kw = mysqli_real_escape_string($this->conn, $filters['keyword']);
            $sql .= " AND (
                hd.SoHopDong LIKE '%$kw%' OR
                nv.HoTen LIKE '%$kw%' OR
                hd.LoaiHopDong LIKE '%$kw%'
            )";
        }

        if (!empty($filters['loaiHD'])) {
            $loai = mysqli_real_escape_string($this->conn, $filters['loaiHD']);
            $sql .= " AND hd.LoaiHopDong='$loai'";
        }

        if (!empty($filters['trangThai'])) {
            if ($filters['trangThai'] === 'con') {
                $sql .= " AND hd.TrangThai='Còn hiệu lực'
                          AND (hd.NgayKetThuc IS NULL OR hd.NgayKetThuc >= CURDATE())";
            }

            if ($filters['trangThai'] === 'het') {
                $sql .= " AND hd.TrangThai='Còn hiệu lực'
                          AND hd.NgayKetThuc < CURDATE()";
            }

            if ($filters['trangThai'] === 'chamdut') {
                $sql .= " AND hd.TrangThai='Hết hiệu lực'";
            }
        }

        if (!empty($filters['tuNgay'])) {
            $tuNgay = mysqli_real_escape_string($this->conn, $filters['tuNgay']);
            $sql .= " AND hd.NgayBatDau >= '$tuNgay'";
        }
        if (!empty($filters['denNgay'])) {
            $denNgay = mysqli_real_escape_string($this->conn, $filters['denNgay']);
            $sql .= " AND hd.NgayBatDau <= '$denNgay'";
        }

        if (!empty($filters['maNV'])) {
            $sql .= " AND hd.MaNV = " . (int)$filters['maNV'];
        }

        $rs = mysqli_query($this->conn, $sql);
        $row = $rs ? mysqli_fetch_assoc($rs) : null;
        return (int)($row['total'] ?? 0);
    }

    public function getHopDongPage($filters = [], $limit = 10, $offset = 0) {
        $limit = max(1, (int)$limit);
        $offset = max(0, (int)$offset);

        $sql = "SELECT
                    hd.MaHopDong,
                    hd.SoHopDong,
                    hd.LoaiHopDong,
                    hd.NgayKy,
                    hd.NgayBatDau,
                    hd.NgayKetThuc,
                    hd.TrangThai,
                    nv.HoTen,
                    bl.TenBac,
                    bl.HeSoLuong,
                    bl.LuongCoSo,
                    (bl.LuongCoSo * bl.HeSoLuong) AS LuongThucTe,
                    CASE
                        WHEN hd.TrangThai = 'Hết hiệu lực' THEN 0
                        WHEN hd.NgayKetThuc IS NULL THEN NULL
                        ELSE DATEDIFF(hd.NgayKetThuc, CURDATE())
                    END AS SoNgayConLai
                FROM hopdong hd
                LEFT JOIN nhanvien nv ON hd.MaNV = nv.MaNV
                LEFT JOIN bacluong bl ON hd.MaBac = bl.MaBac
                WHERE 1=1";

        if (!empty($filters['keyword'])) {
            $kw = mysqli_real_escape_string($this->conn, $filters['keyword']);
            $sql .= " AND (
                hd.SoHopDong LIKE '%$kw%' OR
                nv.HoTen LIKE '%$kw%' OR
                hd.LoaiHopDong LIKE '%$kw%'
            )";
        }

        if (!empty($filters['loaiHD'])) {
            $loai = mysqli_real_escape_string($this->conn, $filters['loaiHD']);
            $sql .= " AND hd.LoaiHopDong='$loai'";
        }

        if (!empty($filters['trangThai'])) {
            if ($filters['trangThai'] === 'con') {
                $sql .= " AND hd.TrangThai='Còn hiệu lực'
                          AND (hd.NgayKetThuc IS NULL OR hd.NgayKetThuc >= CURDATE())";
            }

            if ($filters['trangThai'] === 'het') {
                $sql .= " AND hd.TrangThai='Còn hiệu lực'
                          AND hd.NgayKetThuc < CURDATE()";
            }

            if ($filters['trangThai'] === 'chamdut') {
                $sql .= " AND hd.TrangThai='Hết hiệu lực'";
            }
        }

        if (!empty($filters['tuNgay'])) {
            $tuNgay = mysqli_real_escape_string($this->conn, $filters['tuNgay']);
            $sql .= " AND hd.NgayBatDau >= '$tuNgay'";
        }
        if (!empty($filters['denNgay'])) {
            $denNgay = mysqli_real_escape_string($this->conn, $filters['denNgay']);
            $sql .= " AND hd.NgayBatDau <= '$denNgay'";
        }

        if (!empty($filters['maNV'])) {
            $sql .= " AND hd.MaNV = " . (int)$filters['maNV'];
        }

        $sql .= " ORDER BY hd.MaHopDong DESC LIMIT $limit OFFSET $offset";
        return mysqli_query($this->conn, $sql);
    }

    /* ================== CHI TIẾT ================== */
    public function getHopDongById($maHD) {
        $maHD = (int)$maHD;
        $sql = "SELECT hd.*, nv.HoTen, bl.TenBac,
                       bl.HeSoLuong, bl.LuongCoSo,
                       (bl.LuongCoSo * bl.HeSoLuong) AS LuongThucTe
                FROM hopdong hd
                LEFT JOIN nhanvien nv ON hd.MaNV=nv.MaNV
                LEFT JOIN bacluong bl ON hd.MaBac=bl.MaBac
                WHERE hd.MaHopDong=$maHD";

        $rs = mysqli_query($this->conn, $sql);
        return ($rs && mysqli_num_rows($rs)) ? mysqli_fetch_assoc($rs) : null;
    }

    /* ================== CRUD ================== */
    public function insertHopDong($data) {
        $sql = "INSERT INTO {$this->table}
                (MaNV, MaBac, SoHopDong, LoaiHopDong, NgayKy, NgayBatDau, NgayKetThuc, TrangThai, GhiChu)
                VALUES (
                    {$data['MaNV']},
                    {$data['MaBac']},
                    '{$data['SoHopDong']}',
                    '{$data['LoaiHopDong']}',
                    '{$data['NgayKy']}',
                    '{$data['NgayBatDau']}',
                    " . (!empty($data['NgayKetThuc']) ? "'{$data['NgayKetThuc']}'" : "NULL") . ",
                    'con',
                    '{$data['GhiChu']}'
                )";
        return mysqli_query($this->conn, $sql);
    }

    public function updateHopDong($data) {
        $sql = "UPDATE {$this->table}
                SET MaNV={$data['MaNV']},
                    MaBac={$data['MaBac']},
                    LoaiHopDong='{$data['LoaiHopDong']}',
                    NgayKy='{$data['NgayKy']}',
                    NgayBatDau='{$data['NgayBatDau']}',
                    NgayKetThuc=" . (!empty($data['NgayKetThuc']) ? "'{$data['NgayKetThuc']}'" : "NULL") . ",
                    GhiChu='{$data['GhiChu']}'
                WHERE MaHopDong={$data['MaHopDong']}";
        return mysqli_query($this->conn, $sql);
    }

    public function deleteHopDong($maHD) {
        return mysqli_query($this->conn, "DELETE FROM {$this->table} WHERE MaHopDong=".(int)$maHD);
    }

    public function getAllNhanVienForSelect() {
    // Thêm MaBac vào để View dòng 51 không bị lỗi
    $sql = "SELECT MaNV, HoTen, MaBac FROM nhanvien ORDER BY HoTen";
    return mysqli_query($this->conn, $sql);
}

public function getAllBacLuongForSelect() {
    // Đảm bảo lấy đủ LuongCoSo và HeSoLuong cho dòng 91
    $sql = "SELECT MaBac, TenBac, LuongCoSo, HeSoLuong FROM bacluong ORDER BY TenBac";
    return mysqli_query($this->conn, $sql);
}
}
