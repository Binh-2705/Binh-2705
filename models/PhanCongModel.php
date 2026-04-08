<?php
class PhanCongModel {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    /* ================== READ ================== */
    public function getAll($keyword = '', ?int $maNV = null) {
        $keyword = trim((string)$keyword);

        $sql = "SELECT 
                    pc.MaQT,
                    nv.MaNV,
                    nv.HoTen,
                    pb.TenPB,
                    cv.TenCV,
                    pc.NgayBatDau,
                    pc.NgayKetThuc,
                    pc.LoaiDieuChuyen
                FROM phancong pc
                INNER JOIN nhanvien nv ON pc.MaNV = nv.MaNV
                INNER JOIN phongban pb ON pc.MaPB = pb.MaPB
                INNER JOIN chucvu cv ON pc.MaCV = cv.MaCV";

        $conditions = [];
        if ($maNV !== null) {
            $conditions[] = "pc.MaNV = " . (int)$maNV;
        }

        if ($keyword === '') {
            $where = $conditions ? ' WHERE ' . implode(' AND ', $conditions) : '';
            $sql .= $where . " ORDER BY pc.NgayBatDau DESC";
            return mysqli_query($this->conn, $sql);
        }

        $baseWhere = $conditions ? ' WHERE ' . implode(' AND ', $conditions) . ' AND ' : ' WHERE ';
        $sql .= $baseWhere . "(CAST(nv.MaNV AS CHAR) LIKE ?
                  OR nv.HoTen LIKE ?
                  OR pb.TenPB LIKE ?
                  OR cv.TenCV LIKE ?
                  OR pc.LoaiDieuChuyen LIKE ?)
                  ORDER BY pc.NgayBatDau DESC";

        $search = '%' . $keyword . '%';
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('sssss', $search, $search, $search, $search, $search);
        $stmt->execute();

        return $stmt->get_result();
    }





   public function getById($maQT) {
    $maQT = (int)$maQT;
    $sql = "SELECT 
                pc.*,
                nv.HoTen,
                pb.TenPB,
                cv.TenCV
            FROM phancong pc
            JOIN nhanvien nv ON pc.MaNV = nv.MaNV
            JOIN phongban pb ON pc.MaPB = pb.MaPB
            JOIN chucvu cv ON pc.MaCV = cv.MaCV
            WHERE pc.MaQT = $maQT";

    $rs = mysqli_query($this->conn, $sql);
    return mysqli_fetch_assoc($rs);
}


    /* ================== CREATE ================== */
   public function insert($data) {
    $maNV = (int)$data['MaNV'];
    $maPB = (int)$data['MaPB'];
    $maCV = (int)$data['MaCV'];
    $ngayBD = mysqli_real_escape_string($this->conn, $data['NgayBatDau']);
    $lyDo = mysqli_real_escape_string($this->conn, $data['LyDoThayDoi']);
    $loai = mysqli_real_escape_string($this->conn, $data['LoaiDieuChuyen']);

    $sql = "INSERT INTO phancong
            (MaNV, MaPB, MaCV, NgayBatDau, NgayKetThuc, LyDoThayDoi, LoaiDieuChuyen)
            VALUES (
                $maNV,
                $maPB,
                $maCV,
                '$ngayBD',
                NULL,
                '$lyDo',
                '$loai'
            )";

    return mysqli_query($this->conn, $sql);
}

    /* ================== UPDATE ================== */
    public function update($data) {
    $maQT = (int)$data['MaQT'];
    $maPB = (int)$data['MaPB'];
    $maCV = (int)$data['MaCV'];

    $ngayBD = mysqli_real_escape_string($this->conn, $data['NgayBatDau']);
    $ngayKT = $data['NgayKetThuc']
        ? "'" . mysqli_real_escape_string($this->conn, $data['NgayKetThuc']) . "'"
        : "NULL";

    $lyDo = mysqli_real_escape_string($this->conn, $data['LyDoThayDoi']);
    $loai = mysqli_real_escape_string($this->conn, $data['LoaiDieuChuyen']); // ✅ thêm dòng này

    $sql = "UPDATE phancong SET
                MaPB = $maPB,
                MaCV = $maCV,
                NgayBatDau = '$ngayBD',
                NgayKetThuc = $ngayKT,
                LyDoThayDoi = '$lyDo',
                LoaiDieuChuyen = '$loai'   -- ✅ thêm dòng này
            WHERE MaQT = $maQT";

    return mysqli_query($this->conn, $sql);
}

    /* ================== DELETE ================== */
    public function delete($maQT) {
        $maQT = (int)$maQT;
        $sql = "DELETE FROM phancong WHERE MaQT = $maQT";
        return mysqli_query($this->conn, $sql);
    }

    /* ================== SUPPORT ================== */
    public function getNhanVien() {
        return mysqli_query($this->conn, "SELECT MaNV, HoTen FROM nhanvien");
    }

    public function getPhongBan() {
        return mysqli_query($this->conn, "SELECT MaPB, TenPB FROM phongban");
    }

    public function getChucVu() {
        return mysqli_query($this->conn, "SELECT MaCV, TenCV FROM chucvu");
    }
    public function closeCurrentAssignment($maNV, $ngayKetThuc) {
    $maNV = (int)$maNV;
    $ngayKT = mysqli_real_escape_string($this->conn, $ngayKetThuc);

    $sql = "UPDATE phancong
            SET NgayKetThuc = '$ngayKT'
            WHERE MaNV = $maNV
              AND NgayKetThuc IS NULL";

    return mysqli_query($this->conn, $sql);
}
public function insertWithTransition($data) {

    mysqli_begin_transaction($this->conn);

    try {
        $maNV = (int)$data['MaNV'];
        $ngayBD = $data['NgayBatDau'];

        // Đóng phân công cũ
        $ngayKT = date('Y-m-d', strtotime($ngayBD . ' -1 day'));
        $this->closeCurrentAssignment($maNV, $ngayKT);

        // Tạo phân công mới
        $sql = "INSERT INTO phancong
                (MaNV, MaPB, MaCV, NgayBatDau, LyDoThayDoi, LoaiDieuChuyen)
                VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare($this->conn, $sql);

        mysqli_stmt_bind_param(
            $stmt,
            "iiisss",
            $data['MaNV'],
            $data['MaPB'],
            $data['MaCV'],
            $data['NgayBatDau'],
            $data['LyDoThayDoi'],
            $data['LoaiDieuChuyen']
        );

        mysqli_stmt_execute($stmt);

        mysqli_commit($this->conn);
        return true;

    } catch (Exception $e) {
        mysqli_rollback($this->conn);
        return false;
    }
}
public function hasActiveAssignment($maNV, $ngayBD) {
    $maNV = (int)$maNV;
    $ngayBD = mysqli_real_escape_string($this->conn, $ngayBD);

    $sql = "SELECT 1 FROM phancong
            WHERE MaNV = $maNV
            AND (NgayKetThuc IS NULL OR NgayKetThuc >= '$ngayBD')";

    $rs = mysqli_query($this->conn, $sql);
    return mysqli_num_rows($rs) > 0;
}
public function getCareerPath($maNV) {
    $maNV = (int)$maNV;

    $sql = "SELECT pb.TenPB, cv.TenCV, NgayBatDau, NgayKetThuc
            FROM phancong pc
            JOIN phongban pb ON pc.MaPB = pb.MaPB
            JOIN chucvu cv ON pc.MaCV = cv.MaCV
            WHERE pc.MaNV = $maNV
            ORDER BY NgayBatDau ASC";

    return mysqli_query($this->conn, $sql);
}
}
