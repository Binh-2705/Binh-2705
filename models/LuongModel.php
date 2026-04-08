<?php
class LuongModel {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    /* ================= DANH SÁCH NHÂN VIÊN ================= */
public function getDanhSachNhanVien(){
    // Lấy những người Đang làm HOẶC những người có dữ liệu chấm công trong bảng lương
    $sql = "SELECT DISTINCT MaNV FROM nhanvien WHERE TrangThai='Đang làm' 
            UNION 
            SELECT DISTINCT MaNV FROM chamcong"; 
    return $this->conn->query($sql);
}

    /* ================= LẤY BẢNG LƯƠNG ================= */
public function getAll(?int $maNV = null) {
    $sql = "SELECT bl.*, nv.HoTen
            FROM bangluong bl
            LEFT JOIN nhanvien nv ON bl.MaNV = nv.MaNV";

    if ($maNV !== null) {
        $sql .= " WHERE bl.MaNV = " . (int)$maNV;
    }

    $sql .= " ORDER BY bl.Nam DESC, bl.Thang DESC, bl.MaNV ASC";

    $rs = $this->conn->query($sql);

    $data = [];
    while($row = $rs->fetch_assoc()){
        $data[] = $row;
    }

    return $data;
}

    /* ================= LẤY HỢP ĐỒNG ÁP DỤNG ================= */
 public function getThongTinLuongHopDong($maNV, $thang, $nam)
{
    $tuNgay  = date('Y-m-01', strtotime("$nam-$thang-01"));
    $denNgay = date('Y-m-t', strtotime($tuNgay));

    $sql = "
        SELECT 
            hd.MaHopDong,
            bl.HeSoLuong,
            bl.LuongCoSo,

            IFNULL(cv.HeSoChucVu,1) AS HeSoChucVu,
            IFNULL(cv.PhuCap,0)     AS PhuCap

        FROM hopdong hd
        JOIN bacluong bl ON hd.MaBac = bl.MaBac

        /* ===== LẤY PHÂN CÔNG MỚI NHẤT ===== */
        LEFT JOIN phancong pc ON pc.MaNV = hd.MaNV
        AND pc.NgayBatDau = (
            SELECT MAX(pc2.NgayBatDau)
            FROM phancong pc2
            WHERE pc2.MaNV = hd.MaNV
            AND pc2.NgayBatDau <= ?
        )

        LEFT JOIN chucvu cv ON cv.MaCV = pc.MaCV

        WHERE hd.MaNV = ?
        AND hd.NgayBatDau <= ?
        AND (hd.NgayKetThuc IS NULL OR hd.NgayKetThuc >= ?)

        ORDER BY hd.NgayBatDau DESC
        LIMIT 1
    ";

    $stmt = $this->conn->prepare($sql);
    if(!$stmt){
        die("SQL ERROR: ".$this->conn->error);
    }

    $stmt->bind_param("siss",
        $denNgay,  // tìm phân công gần nhất
        $maNV,
        $denNgay,  // hợp đồng phải bắt đầu trước khi tháng kết thúc
        $tuNgay    // và chưa kết thúc trước tháng
    );

    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();

    if(!$data){
        // Trả về dữ liệu mặc định thay vì ném lỗi để code tiếp tục chạy
        return [
            'MaHopDong' => 0,
            'HeSoLuong' => 1.0, 
            'LuongCoSo' => 5310000, // Lấy mức sàn trong file SQL của bạn
            'HeSoChucVu' => 1.0,
            'PhuCap' => 0
        ];
    }
    return $data;
}

    /* ================= LẤY CÔNG ================= */
    public function getCongView($maNV, $thang, $nam){
        $stmt = $this->conn->prepare("
            SELECT SoNgayCong, GioOT
            FROM v_tonghopcong
            WHERE MaNV=? AND Thang=? AND Nam=?
        ");

        $stmt->bind_param("iii",$maNV,$thang,$nam);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    /* ================= THƯỞNG / PHẠT ================= */
    public function getThuongPhat($maNV, $thang, $nam)
    {
        $thangFormat = "$nam-" . str_pad($thang, 2, '0', STR_PAD_LEFT);

        $sql = "
            SELECT
                SUM(CASE WHEN l.Loai = 'Khen thưởng' THEN k.SoTien ELSE 0 END) AS Thuong,
                SUM(CASE WHEN l.Loai = 'Kỷ luật' THEN k.SoTien ELSE 0 END) AS Phat
            FROM khenthuongkyluat k
            JOIN loaikhenthuongkyluat l ON k.MaLoai = l.MaLoai
            WHERE k.MaNV = ?
            AND DATE_FORMAT(k.NgayQuyetDinh, '%Y-%m') = ?
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("is", $maNV, $thangFormat);
        $stmt->execute();

        $rs = $stmt->get_result()->fetch_assoc();

        return [
            'Thuong' => $rs['Thuong'] ?? 0,
            'Phat'   => $rs['Phat'] ?? 0
        ];
    }

    /* ================= TÍNH LƯƠNG ================= */
    public function tinhLuong($maNV, $thang, $nam) {
        $baoHiem = $this->getBaoHiemNhanVien($maNV, $thang, $nam);

        $hd = $this->getThongTinLuongHopDong($maNV,$thang,$nam);
        if(!$hd){
            throw new Exception("Không có hợp đồng áp dụng");
        }

        $cong = $this->getCongView($maNV,$thang,$nam);
       if(!$cong){
    $cong = [
        'SoNgayCong' => 0,
        'GioOT' => 0
    ];
}

        $soCongChuan = 26;

        /* ===== LƯƠNG NGẠCH BẬC ===== */
        $luongNgachBac = $hd['LuongCoSo'] * $hd['HeSoLuong'];

        /* ===== PHỤ CẤP CHỨC VỤ ===== */
        $phuCap = $hd['PhuCap'];

        /* ===== LƯƠNG THÁNG CHUẨN ===== */
        $luongThang = $luongNgachBac + $phuCap;

       /* ===== QUY ĐỔI CÔNG CHUẨN ===== */
$luongNgay = $luongThang / $soCongChuan;
$soCongThucTe = $cong['SoNgayCong'];

/* ===== PHÂN LOẠI CÔNG ===== */
if ($soCongThucTe < $soCongChuan) {

    // Làm thiếu → trừ theo ngày
    $luongTheoCong = $luongNgay * $soCongThucTe;
    $congVuot = 0;

} elseif ($soCongThucTe == $soCongChuan) {

    // Làm đủ → nhận trọn lương
    $luongTheoCong = $luongThang;
    $congVuot = 0;

} else {

    // Làm vượt → tính OT
    $luongTheoCong = $luongThang;
    $congVuot = $soCongThucTe - $soCongChuan;
}

/* ===== OT THEO NGÀY VƯỢT ===== */
$tienOTNgay = $congVuot * $luongNgay * 1.5;

/* ===== OT THEO GIỜ (nếu có) ===== */
$luongGio = $luongNgay / 8;
$tienOTGio = $cong['GioOT'] * $luongGio * 1.5;

/* ===== TỔNG OT ===== */
$tienOT = $tienOTNgay + $tienOTGio;

        /* ===== PHẠT ĐI MUỘN ===== */
        $stmt = $this->conn->prepare("
            SELECT COUNT(*) AS SoLanMuon
            FROM chamcong
            WHERE MaNV=? 
            AND MONTH(Ngay)=? AND YEAR(Ngay)=?
            AND TrangThai='M'
        ");
        $stmt->bind_param("iii",$maNV,$thang,$nam);
        $stmt->execute();
        $muon = $stmt->get_result()->fetch_assoc();

        $phatMuon = ($muon['SoLanMuon'] ?? 0) * ($luongNgay * 0.1);

        /* ===== THƯỞNG PHẠT KHÁC ===== */
        $tp = $this->getThuongPhat($maNV,$thang,$nam);

        /* ===== TỔNG ===== */
        $tongLuong =
            $luongTheoCong
            + $tienOT
            + $tp['Thuong']
            - $tp['Phat']
            - $phatMuon
            - $baoHiem;

        return [
            'LuongCoSo'  => $hd['LuongCoSo'],
            'HeSoLuong'  => $hd['HeSoLuong'],
            'HeSoChucVu' => $hd['HeSoChucVu'],
            'PhuCap'     => $phuCap,
            'Thuong'     => $tp['Thuong'],
            'Phat'       => $tp['Phat'] + $phatMuon,
            'BaoHiem'    => $baoHiem,
            'TongLuong'  => $tongLuong
            

        ];
    }

    /* ================= INSERT BẢNG LƯƠNG ================= */
    public function insertBangLuong($maNV,$thang,$nam){

        $luong = $this->tinhLuong($maNV,$thang,$nam);

        $sql = "
            INSERT INTO bangluong
            (MaNV,Thang,Nam,LuongCoSo,HeSoLuong,HeSoChucVu,PhuCap,Thuong,Phat,BaoHiem, TongLuong,TrangThai,NgayTinh)
            VALUES (?,?,?,?,?,?,?,?,?,?,?, 'Chưa chốt', NOW())

            ON DUPLICATE KEY UPDATE
            LuongCoSo  = VALUES(LuongCoSo),
            HeSoLuong  = VALUES(HeSoLuong),
            HeSoChucVu = VALUES(HeSoChucVu),
            PhuCap     = VALUES(PhuCap),
            Thuong     = VALUES(Thuong),
            Phat       = VALUES(Phat),
             BaoHiem    = VALUES(BaoHiem),
            TongLuong  = VALUES(TongLuong),
            NgayTinh   = NOW()
           
        ";

        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param("iiidddddddd",
            $maNV,
            $thang,
            $nam,
            $luong['LuongCoSo'],
            $luong['HeSoLuong'],
            $luong['HeSoChucVu'],
            $luong['PhuCap'],
            $luong['Thuong'],
            $luong['Phat'],
             $luong['BaoHiem'],
            $luong['TongLuong']
           
        );

        $stmt->execute();
    }

    public function updateTrangThai($maBL, $trangThai) {
        $stmt = $this->conn->prepare("
            UPDATE bangluong
            SET TrangThai=?
            WHERE MaBL=?
        ");
        $stmt->bind_param("si", $trangThai, $maBL);
        $stmt->execute();
    }
    public function tinhLuongToanBo($thang, $nam)
{
    $dsNV = $this->getDanhSachNhanVien();

    while($nv = $dsNV->fetch_assoc()){
        try{
            $this->insertBangLuong($nv['MaNV'], $thang, $nam);
        }
      catch(Exception $e){
    echo "NV ".$nv['MaNV']." lỗi: ".$e->getMessage()."<br>";
}
    }
}
public function getBaoHiemNhanVien($maNV, $thang, $nam){
    $thangFormat = "$nam-" . str_pad($thang, 2, '0', STR_PAD_LEFT);

    $sql = "
        SELECT SUM(NhanVienDong) AS TongBH
        FROM baohiem
        WHERE MaNV = ?
        AND TrangThai = 'Đang đóng'
        AND DATE_FORMAT(NgayThamGia, '%Y-%m') <= ?
    ";

    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("is", $maNV, $thangFormat);
    $stmt->execute();

    $rs = $stmt->get_result()->fetch_assoc();

    return $rs['TongBH'] ?? 0;
}
}