<?php
class NghiPhepModel {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    /* ===== DANH SÁCH ===== */
    public function getAllNghiPhep() {
        $sql = "SELECT 
                    np.MaNP,
                    np.MaNV,
                    nv.HoTen,
                    np.TuNgay,
                    np.DenNgay,
                    np.SoNgayNghi,
                    np.LyDo,
                    np.LoaiNghi,
                    np.TrangThai,
                    np.NgayNopDon,
                    np.NgayDuyet
                FROM nghiphep np
                JOIN nhanvien nv ON np.MaNV = nv.MaNV
                ORDER BY np.NgayNopDon DESC";
        return mysqli_query($this->conn, $sql);
    }

    public function countNghiPhep($keyword = '', $maNV = null) {
        $keyword = trim((string)$keyword);
        $sql = "SELECT COUNT(*) AS total
                FROM nghiphep np
                JOIN nhanvien nv ON np.MaNV = nv.MaNV";

        $where = [];
        if ($maNV !== null) {
            $where[] = "np.MaNV = " . (int)$maNV;
        }

        if ($keyword !== '') {
            $kw = mysqli_real_escape_string($this->conn, $keyword);
            $where[] = "(nv.HoTen LIKE '%$kw%' OR np.MaNV LIKE '%$kw%')";
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        $rs = mysqli_query($this->conn, $sql);
        $row = $rs ? mysqli_fetch_assoc($rs) : null;
        return (int)($row['total'] ?? 0);
    }

    public function getNghiPhepPage($keyword = '', $limit = 10, $offset = 0, $maNV = null) {
        $keyword = trim((string)$keyword);
        $limit = max(1, (int)$limit);
        $offset = max(0, (int)$offset);

        $sql = "SELECT 
                    np.MaNP,
                    np.MaNV,
                    nv.HoTen,
                    np.TuNgay,
                    np.DenNgay,
                    np.SoNgayNghi,
                    np.LyDo,
                    np.LoaiNghi,
                    np.TrangThai,
                    np.NgayNopDon,
                    np.NgayDuyet
                FROM nghiphep np
                JOIN nhanvien nv ON np.MaNV = nv.MaNV";

        $where = [];
        if ($maNV !== null) {
            $where[] = "np.MaNV = " . (int)$maNV;
        }

        if ($keyword !== '') {
            $kw = mysqli_real_escape_string($this->conn, $keyword);
            $where[] = "(nv.HoTen LIKE '%$kw%' OR np.MaNV LIKE '%$kw%')";
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        $sql .= " ORDER BY np.NgayNopDon DESC LIMIT $limit OFFSET $offset";
        return mysqli_query($this->conn, $sql);
    }

    /* ===== TÌM KIẾM ===== */
    public function searchNghiPhep($keyword) {
        $keyword = mysqli_real_escape_string($this->conn, $keyword);
        $sql = "SELECT 
                    np.MaNP,
                    np.MaNV,
                    nv.HoTen,
                    np.TuNgay,
                    np.DenNgay,
                    np.SoNgayNghi,
                    np.LyDo,
                    np.LoaiNghi,
                    np.TrangThai,
                    np.NgayNopDon,
                    np.NgayDuyet
                FROM nghiphep np
                JOIN nhanvien nv ON np.MaNV = nv.MaNV
                WHERE nv.HoTen LIKE '%$keyword%'
                   OR np.MaNV LIKE '%$keyword%'
                ORDER BY np.NgayNopDon DESC";
        return mysqli_query($this->conn, $sql);
    }

    /* ===== LẤY THEO ID ===== */
    public function getNghiPhepById($maNP) {
        $maNP = (int)$maNP;
        $sql = "SELECT * FROM nghiphep WHERE MaNP = $maNP";
        return mysqli_query($this->conn, $sql);
    }

    public function getNghiPhepRowById($maNP) {
        $maNP = (int)$maNP;
        $sql = "SELECT np.*, nv.HoTen
                FROM nghiphep np
                JOIN nhanvien nv ON np.MaNV = nv.MaNV
                WHERE np.MaNP = $maNP
                LIMIT 1";
        $rs = mysqli_query($this->conn, $sql);
        return $rs ? mysqli_fetch_assoc($rs) : null;
    }

    /* ===== NHÂN VIÊN ===== */
    public function getAllNhanVien() {
        return mysqli_query(
            $this->conn,
            "SELECT MaNV, HoTen FROM nhanvien ORDER BY HoTen ASC"
        );
    }

    public function getNhanVienById($maNV) {
        $maNV = (int)$maNV;
        return mysqli_query(
            $this->conn,
            "SELECT MaNV, HoTen FROM nhanvien WHERE MaNV = $maNV LIMIT 1"
        );
    }

    /* ===== TÍNH NGÀY ===== */
    private function tinhSoNgayNghi($tuNgay, $denNgay) {
        $start = new DateTime($tuNgay);
        $end   = new DateTime($denNgay);
        return $start->diff($end)->days + 1;
    }

    /* ===== THÊM ===== */
    public function insertNghiPhep($maNV, $tuNgay, $denNgay, $soNgay, $lyDo, $loaiNghi) {
        $maNV = (int)$maNV;
        $tuNgay = mysqli_real_escape_string($this->conn, $tuNgay);
        $denNgay = mysqli_real_escape_string($this->conn, $denNgay);
        $lyDo = mysqli_real_escape_string($this->conn, $lyDo);
        $loaiNghi = mysqli_real_escape_string($this->conn, $loaiNghi);

        $sql = "INSERT INTO nghiphep
                (MaNV, TuNgay, DenNgay, SoNgayNghi, LyDo, LoaiNghi)
                VALUES
                ($maNV, '$tuNgay', '$denNgay', $soNgay, '$lyDo', '$loaiNghi')";
        return mysqli_query($this->conn, $sql);
    }

    /* ===== CẬP NHẬT ===== */
    public function updateNghiPhep($maNP, $maNV, $tuNgay, $denNgay, $soNgay, $lyDo, $loaiNghi) {
        $maNP = (int)$maNP;
        $maNV = (int)$maNV;
        $tuNgay = mysqli_real_escape_string($this->conn, $tuNgay);
        $denNgay = mysqli_real_escape_string($this->conn, $denNgay);
        $lyDo = mysqli_real_escape_string($this->conn, $lyDo);
        $loaiNghi = mysqli_real_escape_string($this->conn, $loaiNghi);

        $sql = "UPDATE nghiphep SET
                    MaNV = $maNV,
                    TuNgay = '$tuNgay',
                    DenNgay = '$denNgay',
                    SoNgayNghi = $soNgay,
                    LyDo = '$lyDo',
                    LoaiNghi = '$loaiNghi'
                WHERE MaNP = $maNP";
        return mysqli_query($this->conn, $sql);
    }

    /* ===== DUYỆT ===== */
 public function duyet($maNP)
    {
        $conn = $this->conn;
        $conn->begin_transaction();

        try {
            /* 1. Lấy thông tin đơn */
            $stmt = $conn->prepare("
                SELECT MaNV, TuNgay, DenNgay
                FROM nghiphep
                WHERE MaNP=? FOR UPDATE
            ");
            $stmt->bind_param("i", $maNP);
            $stmt->execute();
            $nghi = $stmt->get_result()->fetch_assoc();

            if(!$nghi){
                throw new Exception("Không tìm thấy đơn nghỉ!");
            }

            $maNV   = $nghi['MaNV'];
            $tuNgay = strtotime($nghi['TuNgay']);
            $denNgay= strtotime($nghi['DenNgay']);

            /* 2. Cập nhật trạng thái đơn */
            $stmt2 = $conn->prepare("
                UPDATE nghiphep
                SET TrangThai='Đã duyệt', NgayDuyet=CURDATE()
                WHERE MaNP=?
            ");
            $stmt2->bind_param("i", $maNP);
            $stmt2->execute();

            /* 3. Ghi nghỉ phép vào bảng chấm công */
            while ($tuNgay <= $denNgay) {

                $ngay = date('Y-m-d', $tuNgay);

                $sqlCC = "
                    INSERT INTO chamcong (MaNV, Ngay, TrangThai, GioVao, GioRa)
                    VALUES (?, ?, 'Nghi phep', NULL, NULL)
                    ON DUPLICATE KEY UPDATE
                        TrangThai='Nghi phep',
                        GioVao=NULL,
                        GioRa=NULL
                ";

                $stmtCC = $conn->prepare($sqlCC);
                if(!$stmtCC){
                    throw new Exception($conn->error);
                }

                $stmtCC->bind_param("is", $maNV, $ngay);
                $stmtCC->execute();

                $tuNgay = strtotime("+1 day", $tuNgay);
            }

            $conn->commit();
            return true;

        } catch (Exception $e) {
            $conn->rollback();
            die("Lỗi duyệt nghỉ: ".$e->getMessage());
        }
    }

    /* ===== TỪ CHỐI ===== */
    public function tuchoi($maNP) {
        $maNP = (int)$maNP;
        return mysqli_query(
            $this->conn,
            "UPDATE nghiphep
             SET TrangThai='Từ chối', NgayDuyet=CURRENT_DATE
             WHERE MaNP=$maNP"
        );
    }

    /* ===== XÓA ===== */
    public function xoa($maNP) {
        $maNP = (int)$maNP;
        return mysqli_query($this->conn, "DELETE FROM nghiphep WHERE MaNP=$maNP");
    }
}
?>
