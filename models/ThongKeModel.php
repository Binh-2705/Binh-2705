<?php
class ThongKeModel {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function layTatCaBaoCao($loai = '', $thang = '', $maPB = '') {
        $sql = "SELECT bc.*, pb.TenPB 
                FROM baocao bc 
                LEFT JOIN phongban pb ON pb.MaPB = bc.ma_pb 
                WHERE 1=1";
        if (!empty($loai)) $sql .= " AND bc.loai = '$loai'";
        if (!empty($thang)) $sql .= " AND bc.thang = '$thang'";
        if (!empty($maPB)) $sql .= " AND bc.ma_pb = '$maPB'";
        $sql .= " ORDER BY bc.created_at DESC";
        return mysqli_query($this->conn, $sql);
    }

    public function layBaoCaoTheoId($id) {
        $result = mysqli_query($this->conn, "SELECT * FROM baocao WHERE id=$id");
        return mysqli_fetch_assoc($result);
    }

    public function taoBaoCao($data) {
        $tieu_de = $data['tieu_de'] ?? '';
        $loai    = $data['loai'] ?? '';
        $thang   = $data['thang'] ?? null;
        $ma_pb   = $data['ma_pb'] ?? null;
        $noi_dung= $data['noi_dung'] ?? null;

        $sql = "INSERT INTO baocao (tieu_de, loai, thang, ma_pb, noi_dung) 
                VALUES ('$tieu_de', '$loai', " .
                (is_null($thang) ? "NULL" : "'$thang'") . ", " .
                (is_null($ma_pb) ? "NULL" : "'$ma_pb'") . ", " .
                (is_null($noi_dung) ? "NULL" : "'$noi_dung'") . ")";
        return mysqli_query($this->conn, $sql);
    }

    public function suaBaoCao($id, $data) {
        $tieu_de = $data['tieu_de'] ?? '';
        $loai    = $data['loai'] ?? '';
        $thang   = $data['thang'] ?? null;
        $ma_pb   = $data['ma_pb'] ?? null;
        $noi_dung= $data['noi_dung'] ?? null;

        $sql = "UPDATE baocao SET 
                    tieu_de='$tieu_de', 
                    loai='$loai', 
                    thang=" . (is_null($thang) ? "NULL" : "'$thang'") . ", 
                    ma_pb=" . (is_null($ma_pb) ? "NULL" : "'$ma_pb'") . ", 
                    noi_dung=" . (is_null($noi_dung) ? "NULL" : "'$noi_dung'") . "
                WHERE id=$id";
        return mysqli_query($this->conn, $sql);
    }

    public function xoaBaoCao($id) {
        return mysqli_query($this->conn, "DELETE FROM baocao WHERE id=$id");
    }

    public function thongKeNhanVien() {
        $sql = "SELECT PhongBan, COUNT(*) AS soLuong FROM nhanvien GROUP BY PhongBan";
        return mysqli_query($this->conn, $sql);
    }

    public function thongKeChamCong($thang = '') {
        $sql = "SELECT Thang, SUM(SoNgayLam) AS tongNgayLam, SUM(SoNgayNghi) AS tongNgayNghi FROM chamcong";
        if (!empty($thang)) $sql .= " WHERE Thang = '$thang'";
        $sql .= " GROUP BY Thang";
        return mysqli_query($this->conn, $sql);
    }

    public function thongKeLuong($thang = '') {
        $sql = "SELECT Thang, 
                        SUM(LuongCB) AS tongLuongCB, 
                        SUM(PhuCap) AS tongPhuCap, 
                        SUM(Thuong) AS tongThuong, 
                        SUM(KhauTru) AS tongKhauTru, 
                        SUM(TongLuong) AS tongTongLuong 
                FROM luong";
        if (!empty($thang)) $sql .= " WHERE Thang = '$thang'";
        $sql .= " GROUP BY Thang";
        return mysqli_query($this->conn, $sql);
    }

    public function thongKeDaoTao() {
        $sql = "SELECT COUNT(*) AS soKhoaHoc, SUM(ChiPhi) AS tongChiPhi FROM daotao";
        return mysqli_query($this->conn, $sql);
    }

    public function layNhanVienTheoPhong($maPB) {
        $sql = "SELECT MaNV, HoTen, GioiTinh, NgaySinh, ChucVu, Luong 
                FROM nhanvien 
                WHERE PhongBan = '$maPB'";
        return mysqli_query($this->conn, $sql);
    }

    public function layChamCongTheoMaCC($maCC) {
        $sql = "SELECT cc.MaCC, cc.MaNV, nv.HoTen, cc.Thang, cc.SoNgayLam, cc.SoNgayNghi, cc.GhiChu
                FROM chamcong cc 
                JOIN nhanvien nv ON nv.MaNV = cc.MaNV
                WHERE cc.MaCC = '$maCC'";
        return mysqli_query($this->conn, $sql);
    }

public function layLuongTheoMaLuong($maLuong) {
    $sql = "SELECT l.MaLuong, l.MaNV, nv.HoTen, l.Thang, 
                   l.LuongCB, l.PhuCap, l.Thuong, l.KhauTru, l.TongLuong
            FROM luong l 
            JOIN nhanvien nv ON nv.MaNV = l.MaNV
            WHERE l.MaLuong = '$maLuong'";
    return mysqli_query($this->conn, $sql);
}


    public function layDaoTaoTheoPhong($maPB = null) {
        $sql = "SELECT TenKhoaHoc, NgayBatDau, NgayKetThuc, ChiPhi FROM daotao";
        return mysqli_query($this->conn, $sql);
    }
}
