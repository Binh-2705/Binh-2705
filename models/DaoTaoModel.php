<?php
class DaoTaoModel {
    private $conn;

    public function __construct($db){
        $this->conn = $db;
    }

    /* ===== LẤY DANH SÁCH KHÓA ===== */
   public function getAllKhoa(){

    $sql = "
        SELECT *,
        CASE
            WHEN CURDATE() < TuNgay THEN 'Lên kế hoạch'
            WHEN CURDATE() BETWEEN TuNgay AND DenNgay THEN 'Đang đào tạo'
            ELSE 'Hoàn thành'
        END AS TrangThaiTuDong
        FROM khoadaotao
        ORDER BY TuNgay DESC
    ";

    return $this->conn->query($sql);
}

    /* ===== THÊM KHÓA ===== */
    public function insertKhoa($ten,$tu,$den,$noidung,$donvi){
        $stmt = $this->conn->prepare("
            INSERT INTO khoadaotao
            (TenKhoaDaoTao,TuNgay,DenNgay,NoiDung,DonViToChuc)
            VALUES (?,?,?,?,?)
        ");
        $stmt->bind_param("sssss",$ten,$tu,$den,$noidung,$donvi);
        $stmt->execute();
    }

    /* ===== XÓA KHÓA ===== */
    public function deleteKhoa($id){
        $stmt = $this->conn->prepare("DELETE FROM khoadaotao WHERE MaKDT=?");
        $stmt->bind_param("i",$id);
        $stmt->execute();
    }

    /* ===== DANH SÁCH NHÂN VIÊN ===== */
    public function getNhanVien(){
        return $this->conn->query("SELECT MaNV,HoTen FROM nhanvien WHERE TrangThai='Đang làm'");
    }

    /* ===== THÊM NHÂN VIÊN VÀO KHÓA ===== */
    public function themNhanVien($maNV,$maKDT){
        $stmt = $this->conn->prepare("
            INSERT INTO thamgiadaotao (MaNV,MaKDT)
            VALUES (?,?)
        ");
        $stmt->bind_param("ii",$maNV,$maKDT);
        $stmt->execute();
    }

    /* ===== DANH SÁCH THAM GIA ===== */
    public function getThamGia($maKDT){
        $stmt = $this->conn->prepare("
            SELECT tg.*, nv.HoTen
            FROM thamgiadaotao tg
            JOIN nhanvien nv ON tg.MaNV = nv.MaNV
            WHERE tg.MaKDT=?
        ");
        $stmt->bind_param("i",$maKDT);
        $stmt->execute();
        return $stmt->get_result();
    }

    /* ===== CẬP NHẬT KẾT QUẢ ===== */
    public function updateKetQua($maTGDT,$ketqua,$diem){
        $stmt = $this->conn->prepare("
            UPDATE thamgiadaotao
            SET KetQua=?, DiemDanhGia=?
            WHERE MaTGDT=?
        ");
        $stmt->bind_param("sdi",$ketqua,$diem,$maTGDT);
        $stmt->execute();
    }
    public function kiemTraHoanThanh($maKDT){
    $stmt = $this->conn->prepare("
        SELECT 
        CASE
            WHEN CURDATE() > DenNgay THEN 1
            ELSE 0
        END AS DuocCham
        FROM khoadaotao
        WHERE MaKDT=?
    ");
    $stmt->bind_param("i",$maKDT);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc()['DuocCham'];
}
}