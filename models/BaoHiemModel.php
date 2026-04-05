<?php
class BaoHiemModel {
    private $conn;

    public function __construct($conn){
        $this->conn = $conn;
    }

    /* ================= DANH SÁCH ================= */
    public function getAll(){
        $sql = "SELECT bh.*, nv.HoTen
                FROM baohiem bh
                JOIN nhanvien nv ON bh.MaNV = nv.MaNV
                ORDER BY bh.MaBH DESC";

        return $this->conn->query($sql);
    }

    public function getAllByMaNV(int $maNV){
        $maNV = (int)$maNV;
        $sql = "SELECT bh.*, nv.HoTen
                FROM baohiem bh
                JOIN nhanvien nv ON bh.MaNV = nv.MaNV
                WHERE bh.MaNV = ?
                ORDER BY bh.MaBH DESC";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("i", $maNV);
        $stmt->execute();
        return $stmt->get_result();
    }

    /* ================= LẤY 1 ================= */
    public function getById($id){
        $stmt = $this->conn->prepare("
            SELECT * FROM baohiem WHERE MaBH = ?
        ");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    /* ================= THÊM ================= */
    public function them($data){
        $sql = "INSERT INTO baohiem
                (MaNV, SoBHXH, LoaiBaoHiem, NgayThamGia, MucDong, CongTyDong, NhanVienDong, TrangThai)
                VALUES (?,?,?,?,?,?,?,?)";

        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param(
            "isssddds",
            $data['MaNV'],
            $data['SoBHXH'],
            $data['LoaiBaoHiem'],
            $data['NgayThamGia'],
            $data['MucDong'],
            $data['CongTyDong'],
            $data['NhanVienDong'],
            $data['TrangThai']
        );

        return $stmt->execute();
    }

    /* ================= SỬA ================= */
    public function sua($id, $data){
        $sql = "UPDATE baohiem SET
                    MaNV = ?,
                    SoBHXH = ?,
                    LoaiBaoHiem = ?,
                    NgayThamGia = ?,
                    MucDong = ?,
                    CongTyDong = ?,
                    NhanVienDong = ?,
                    TrangThai = ?
                WHERE MaBH = ?";

        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param(
            "isssdddsi",
            $data['MaNV'],
            $data['SoBHXH'],
            $data['LoaiBaoHiem'],
            $data['NgayThamGia'],
            $data['MucDong'],
            $data['CongTyDong'],
            $data['NhanVienDong'],
            $data['TrangThai'],
            $id
        );

        return $stmt->execute();
    }

    /* ================= XÓA ================= */
    public function xoa($id){
        $stmt = $this->conn->prepare("DELETE FROM baohiem WHERE MaBH = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    /* ================= NGỪNG BẢO HIỂM (KHUYẾN KHÍCH) ================= */
    public function dungBaoHiem($id){
        $stmt = $this->conn->prepare("
            UPDATE baohiem SET TrangThai = 'Đã dừng' WHERE MaBH = ?
        ");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    /* ================= LẤY DANH SÁCH NHÂN VIÊN (CHO DROPDOWN) ================= */
    public function getNhanVien(){
        $sql = "SELECT MaNV, HoTen FROM nhanvien ORDER BY HoTen ASC";
        return $this->conn->query($sql);
    }

    /* ================= KIỂM TRA TRÙNG SỐ BH ================= */
   public function checkSoBHXH($soBHXH, $id = null){
    $sql = "SELECT MaBH FROM baohiem WHERE SoBHXH = ?";
    if($id) $sql .= " AND MaBH != ?"; // Khi sửa, bỏ qua chính bản ghi đó

    $stmt = $this->conn->prepare($sql);
    if($id) $stmt->bind_param("si", $soBHXH, $id);
    else $stmt->bind_param("s", $soBHXH);

    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}
public function getExportData() {
    $sql = "SELECT bh.MaBH, nv.HoTen, bh.SoBHXH, bh.LoaiBaoHiem, 
                   bh.MucDong, bh.CongTyDong, bh.NhanVienDong, bh.TrangThai
            FROM baohiem bh
            JOIN nhanvien nv ON bh.MaNV = nv.MaNV";
    return $this->conn->query($sql);
}

public function searchByKeywordAndMaNV(string $keyword, int $maNV) {
    $sql = "SELECT bh.*, nv.HoTen
            FROM baohiem bh
            JOIN nhanvien nv ON bh.MaNV = nv.MaNV
            WHERE nv.HoTen LIKE ? AND bh.MaNV = ?
            ORDER BY bh.MaBH DESC";

    $stmt = $this->conn->prepare($sql);
    if (!$stmt) {
        return false;
    }

    $like = "%$keyword%";
    $stmt->bind_param("si", $like, $maNV);
    $stmt->execute();
    return $stmt->get_result();
}
}