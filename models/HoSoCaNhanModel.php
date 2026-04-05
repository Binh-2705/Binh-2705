<?php
class HoSoCaNhanModel {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
}
public function getALL(){
    $sql = "SELECT hs.*, nv.HoTen, pb.TenPB, cv.TenCV
                FROM hosonhanvien hs
                LEFT JOIN nhanvien nv ON hs.MaNV = nv.MaNV
                LEFT JOIN phongban pb ON hs.MaPB = pb.MaPB
                LEFT JOIN chucvu cv ON hs.MaCV = cv.MaCV
                ORDER BY hs.MaHoSo DESC";
    $result = mysqli_query($this->conn, $sql);
    return $result;
}

public function countAll(): int {
    $sql = "SELECT COUNT(*) AS total FROM hosonhanvien";
    $result = mysqli_query($this->conn, $sql);
    $row = $result ? mysqli_fetch_assoc($result) : ['total' => 0];
    return (int)($row['total'] ?? 0);
}

public function countSearch(string $keyword): int {
    $search = '%' . $keyword . '%';
    $sql = "SELECT COUNT(*) AS total
            FROM hosonhanvien hs
            LEFT JOIN nhanvien nv ON hs.MaNV = nv.MaNV
            LEFT JOIN phongban pb ON hs.MaPB = pb.MaPB
            LEFT JOIN chucvu cv ON hs.MaCV = cv.MaCV
            WHERE CAST(hs.MaNV AS CHAR) LIKE ?
               OR nv.HoTen LIKE ?
               OR pb.TenPB LIKE ?
               OR cv.TenCV LIKE ?";

    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("ssss", $search, $search, $search, $search);
    $stmt->execute();

    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : ['total' => 0];
    return (int)($row['total'] ?? 0);
}

public function getPage(int $page = 1, int $perPage = 10){
    $page = max(1, $page);
    $perPage = max(1, $perPage);
    $offset = ($page - 1) * $perPage;

    $sql = "SELECT hs.*, nv.HoTen, pb.TenPB, cv.TenCV
                FROM hosonhanvien hs
                LEFT JOIN nhanvien nv ON hs.MaNV = nv.MaNV
                LEFT JOIN phongban pb ON hs.MaPB = pb.MaPB
                LEFT JOIN chucvu cv ON hs.MaCV = cv.MaCV
                ORDER BY hs.MaHoSo DESC
                LIMIT $offset, $perPage";
    return mysqli_query($this->conn, $sql);
}

public function searchPage(string $keyword, int $page = 1, int $perPage = 10) {
    $page = max(1, $page);
    $perPage = max(1, $perPage);
    $offset = ($page - 1) * $perPage;
    $search = '%' . $keyword . '%';

    $sql = "SELECT hs.*, nv.HoTen, pb.TenPB, cv.TenCV
            FROM hosonhanvien hs
            LEFT JOIN nhanvien nv ON hs.MaNV = nv.MaNV
            LEFT JOIN phongban pb ON hs.MaPB = pb.MaPB
            LEFT JOIN chucvu cv ON hs.MaCV = cv.MaCV
            WHERE CAST(hs.MaNV AS CHAR) LIKE ?
               OR nv.HoTen LIKE ?
               OR pb.TenPB LIKE ?
               OR cv.TenCV LIKE ?
            ORDER BY hs.MaHoSo DESC
            LIMIT ?, ?";

    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("ssssii", $search, $search, $search, $search, $offset, $perPage);
    $stmt->execute();

    return $stmt->get_result();
}

public function getById($id){
    $sql = "SELECT hs.*, nv.HoTen, pb.TenPB, cv.TenCV
            FROM hosonhanvien hs
            LEFT JOIN nhanvien nv ON hs.MaNV = nv.MaNV
            LEFT JOIN phongban pb ON hs.MaPB = pb.MaPB
            LEFT JOIN chucvu cv ON hs.MaCV = cv.MaCV
            WHERE hs.MaHoSo = ?";

    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();

    return $stmt->get_result()->fetch_assoc(); // 🔥 TRẢ LUÔN ARRAY
}
  public function themHoSo($data){

    $sql = "INSERT INTO hosonhanvien(
                MaNV, CCCD, NoiCap, NgayCap, DiaChi,
                DanToc, TonGiao, TrinhDo, ChuyenMon,
                NgayVaoLam, MaPB, MaCV, TrangThaiHonNhan, Anh
            )
            VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

    $stmt = $this->conn->prepare($sql);

    $stmt->bind_param(
        "issssssssiisss",
        $data['MaNV'],
        $data['CCCD'],
        $data['NoiCap'],
        $data['NgayCap'],
        $data['DiaChi'],
        $data['DanToc'],
        $data['TonGiao'],
        $data['TrinhDo'],
        $data['ChuyenMon'],
        $data['NgayVaoLam'],
        $data['MaPB'],
        $data['MaCV'],
        $data['TrangThaiHonNhan'],
        $data['Anh']
    );

    return $stmt->execute();
}
    public function capNhatHoSo($id,$data){

        $sql = "UPDATE hosonhanvien SET
                    CCCD=?,
                    NoiCap=?,
                    NgayCap=?,
                    DiaChi=?,
                    DanToc=?,
                    TonGiao=?,
                    TrinhDo=?,
                    ChuyenMon=?,
                    NgayVaoLam=?,
                    MaPB=?,
                    MaCV=?,
                    TrangThaiHonNhan=?
                WHERE MaHoSo=?";

        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param(
            "sssssssssiisi",
            $data['CCCD'],
            $data['NoiCap'],
            $data['NgayCap'],
            $data['DiaChi'],
            $data['DanToc'],
            $data['TonGiao'],
            $data['TrinhDo'],
            $data['ChuyenMon'],
            $data['NgayVaoLam'],
            $data['MaPB'],
            $data['MaCV'],
            $data['TrangThaiHonNhan'],
            $id
        );

        return $stmt->execute();
    }
    public function xoaHoSo($id){

        $sql = "DELETE FROM hosonhanvien WHERE MaHoSo=?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i",$id);

        return $stmt->execute();
    }
    public function getPhongBan(){
        $sql = "SELECT * FROM phongban ORDER BY TenPB";
        $result = mysqli_query($this->conn, $sql);
        return $result;
    }
    public function getChucVu(){
        $sql = "SELECT * FROM chucvu ORDER BY TenCV";
        $result = mysqli_query($this->conn, $sql);
        return $result;
    }
    public function getNhanVien(){
        $sql = "SELECT * FROM nhanvien ORDER BY HoTen";
        $result = mysqli_query($this->conn, $sql);
        return $result;
    }
    public function getThongTinNhanVien($maNV){
    $sql = "SELECT pc.MaNV, pb.MaPB, pb.TenPB, cv.MaCV, cv.TenCV
            FROM phancong pc
            LEFT JOIN phongban pb ON pc.MaPB = pb.MaPB
            LEFT JOIN chucvu cv ON pc.MaCV = cv.MaCV
            WHERE pc.MaNV = ?
            ORDER BY pc.NgayBatDau DESC
            LIMIT 1";

    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $maNV);
    $stmt->execute();

    return $stmt->get_result();
}


}