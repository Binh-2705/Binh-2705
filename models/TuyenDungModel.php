
<?php
require_once 'vendor/autoload.php';
use Smalot\PdfParser\Parser;
class TuyenDungModel{
    private $conn;

    public function __construct($conn){
        $this->conn = $conn;
    }

    /* ================= ĐỢT TUYỂN DỤNG ================= */

    public function getAllDot(){
        $sql = "SELECT * FROM dottuyendung ORDER BY TuNgay DESC";
        return $this->conn->query($sql);
    }

    public function countDot() {
        $sql = "SELECT COUNT(*) AS total FROM dottuyendung";
        $result = $this->conn->query($sql);
        $row = $result ? $result->fetch_assoc() : null;
        return (int)($row['total'] ?? 0);
    }

    public function getDotPage($limit = 10, $offset = 0) {
        $limit = max(1, (int)$limit);
        $offset = max(0, (int)$offset);
        $sql = "SELECT * FROM dottuyendung ORDER BY TuNgay DESC LIMIT $limit OFFSET $offset";
        return $this->conn->query($sql);
    }

    public function themDot($data){

        $sql = "INSERT INTO dottuyendung
                (TenDotTuyenDung,ViTriTuyenDung,SoLuong,TuNgay,DenNgay,MoTa)
                VALUES (?,?,?,?,?,?)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param(
            "ssisss",
            $data['ten'],
            $data['vitri'],
            $data['soluong'],
            $data['tu'],
            $data['den'],
            $data['mota']
        );

        return $stmt->execute();
    }

    public function xoaDot($id){
        $sql = "DELETE FROM dottuyendung WHERE MaDTD=?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i",$id);
        return $stmt->execute();
    }

    /* ================= ỨNG VIÊN ================= */

    public function getAllUngVien(){
        $sql = "SELECT * FROM ungvien ORDER BY MaUV DESC";
        return $this->conn->query($sql);
    }
public function themUngVien($data){

    $diem = $this->chamDiemCV($data);

    $sql = "INSERT INTO ungvien
            (HoTen,NgaySinh,GioiTinh,Email,DienThoai,TrinhDo,KinhNghiem,FileCV,DiemCV)
            VALUES (?,?,?,?,?,?,?,?,?)";

    $stmt = $this->conn->prepare($sql);

    if(!$stmt){
        die("SQL ERROR: ".$this->conn->error);
    }

    $stmt->bind_param(
        "ssssssssi",
        $data['hoten'],
        $data['ngaysinh'],
        $data['gioitinh'],
        $data['email'],
        $data['dienthoai'],
        $data['trinhdo'],
        $data['kinhnghiem'],
        $data['cv'],
        $diem
    );

    return $stmt->execute();
}

    /* ================= HỒ SƠ ỨNG TUYỂN ================= */

    public function getHoSoTheoDot($maDTD){

        $sql = "SELECT hs.*,uv.HoTen,uv.FileCV
                FROM hosoungtuyen hs
                JOIN ungvien uv ON hs.MaUV=uv.MaUV
                WHERE hs.MaDTD=?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i",$maDTD);
        $stmt->execute();

        return $stmt->get_result();
    }

    public function themHoSo($maUV,$maDTD){

        $sql = "INSERT INTO hosoungtuyen (MaUV,MaDTD)
                VALUES (?,?)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii",$maUV,$maDTD);

        return $stmt->execute();
    }

    public function capNhatTrangThai($maHS,$trangthai){

        $sql = "UPDATE hosoungtuyen
                SET TrangThai=?
                WHERE MaHS=?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si",$trangthai,$maHS);

        return $stmt->execute();
    }
    /* ================= LỊCH PHỎNG VẤN ================= */

public function getLichPhongVan($maHS){

    $sql = "SELECT * FROM lichphongvan WHERE MaHS=?";

    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i",$maHS);
    $stmt->execute();

    return $stmt->get_result();
}

public function themLichPhongVan($data){

    $sql = "INSERT INTO lichphongvan
            (MaHS,NgayPhongVan,GioPhongVan,DiaDiem,GhiChu)
            VALUES (?,?,?,?,?)";

    $stmt = $this->conn->prepare($sql);

    $stmt->bind_param(
        "issss",
        $data['mahs'],
        $data['ngay'],
        $data['gio'],
        $data['diadiem'],
        $data['ghichu']
    );

    return $stmt->execute();
}
/* ================= CHUYỂN THÀNH NHÂN VIÊN ================= */

public function chuyenThanhNhanVien($maHS){

    $sql = "SELECT uv.*
            FROM hosoungtuyen hs
            JOIN ungvien uv ON hs.MaUV = uv.MaUV
            WHERE hs.MaHS=?";

    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i",$maHS);
    $stmt->execute();

    $result = $stmt->get_result();
    $uv = $result->fetch_assoc();

    if(!$uv) return false;

    $sqlInsert = "INSERT INTO nhanvien
        (HoTen,NgaySinh,GioiTinh,Email,DienThoai,TrangThai)
        VALUES (?,?,?,?,?, 'Đang làm')";

    $stmt2 = $this->conn->prepare($sqlInsert);

    $stmt2->bind_param(
        "sssss",
        $uv['HoTen'],
        $uv['NgaySinh'],
        $uv['GioiTinh'],
        $uv['Email'],
        $uv['DienThoai']
    );

    return $stmt2->execute();
}
public function daChuyenNhanVien($maHS){

    $sql = "SELECT MaHS FROM nhanvien WHERE MaHS=?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i",$maHS);
    $stmt->execute();

    return $stmt->get_result()->num_rows > 0;
}
/* ================= THỐNG KÊ TUYỂN DỤNG ================= */


public function thongKeTuyenDung(){

$sql = "SELECT

COUNT(*) as Tong,

SUM(CASE WHEN TrangThai='Nộp hồ sơ' THEN 1 ELSE 0 END) as NopHoSo,
SUM(CASE WHEN TrangThai='Sàng lọc' THEN 1 ELSE 0 END) as SangLoc,
SUM(CASE WHEN TrangThai='Phỏng vấn' THEN 1 ELSE 0 END) as PhongVan,
SUM(CASE WHEN TrangThai='Offer' THEN 1 ELSE 0 END) as Offer,
SUM(CASE WHEN TrangThai='Nhận việc' THEN 1 ELSE 0 END) as NhanViec,
SUM(CASE WHEN TrangThai='Rớt' THEN 1 ELSE 0 END) as Rot

FROM hosoungtuyen";

return $this->conn->query($sql)->fetch_assoc();
}

public function kanban(){

$sql = "SELECT hs.MaHS,uv.HoTen,hs.TrangThai
FROM hosoungtuyen hs
JOIN ungvien uv ON hs.MaUV=uv.MaUV";

return $this->conn->query($sql);

}
/* ================= ĐÁNH GIÁ PHỎNG VẤN ================= */

public function themDanhGia($data){

$sql = "INSERT INTO danhgiaphongvan
(MaHS,DiemKyNang,DiemKinhNghiem,DiemThaiDo,NhanXet)
VALUES (?,?,?,?,?)";

$stmt = $this->conn->prepare($sql);

$stmt->bind_param(
"iiiis",
$data['mahs'],
$data['kynang'],
$data['kinhnghiem'],
$data['thaido'],
$data['nhanxet']
);

return $stmt->execute();
}

public function getDanhGia($maHS){

$sql = "SELECT * FROM danhgiaphongvan WHERE MaHS=?";

$stmt = $this->conn->prepare($sql);
$stmt->bind_param("i",$maHS);
$stmt->execute();

return $stmt->get_result();
}
public function topUngVien(){

$sql = "SELECT 
uv.HoTen,
(dg.DiemKyNang + dg.DiemKinhNghiem + dg.DiemThaiDo)/3 as DiemTB
FROM danhgiaphongvan dg
JOIN hosoungtuyen hs ON dg.MaHS = hs.MaHS
JOIN ungvien uv ON hs.MaUV = uv.MaUV
ORDER BY DiemTB DESC
LIMIT 5";

return $this->conn->query($sql);

}
public function chamDiemCV($data){

    $diem = 0;

    $text = $this->docNoiDungCV($data['cv']);

    if(!$text) return 0;

    $skills = [
        "php"=>3,
        "laravel"=>3,
        "mysql"=>2,
        "javascript"=>2,
        "html"=>1,
        "css"=>1,
        "react"=>2,
        "node"=>2,
        "git"=>1,
        "docker"=>2
    ];

    foreach($skills as $skill=>$score){

        if(strpos($text,$skill)!==false){
            $diem += $score;
        }

    }

    if(strpos($text,"đại học")!==false || strpos($text,"university")!==false){
        $diem += 2;
    }

    if(strlen($text) > 1000){
        $diem += 1;
    }

    if($diem > 10){
        $diem = 10;
    }

    return $diem;
}
public function docNoiDungCV($file){

    $path = "uploads/cv/".$file;

    if(!file_exists($path)){
        return "";
    }

    $parser = new Parser();
    $pdf = $parser->parseFile($path);

    $text = $pdf->getText();

    return strtolower($text);
}
public function getEmailUngVien($maHS){

$sql = "SELECT uv.Email,uv.HoTen
FROM hosoungtuyen hs
JOIN ungvien uv ON hs.MaUV = uv.MaUV
WHERE hs.MaHS=?";

$stmt = $this->conn->prepare($sql);
$stmt->bind_param("i",$maHS);
$stmt->execute();

return $stmt->get_result()->fetch_assoc();

}
public function timUngVien($keyword){

    $sql = "SELECT * FROM ungvien 
            WHERE HoTen LIKE ?
            OR Email LIKE ?
            OR DienThoai LIKE ?";

    $stmt = $this->conn->prepare($sql);

    if(!$stmt){
        die("SQL ERROR: ".$this->conn->error);
    }

    $kw = "%".$keyword."%";

    $stmt->bind_param("sss",$kw,$kw,$kw);

    $stmt->execute();

    return $stmt->get_result();
}
}