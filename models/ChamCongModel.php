<?php
class ChamCongModel {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    /* ================== CHECK TRÙNG NGÀY ================== */
    public function existsChamCong($maNV, $ngay, $ignoreMaCC = null) {

        if ($ignoreMaCC) {
            $sql = "SELECT MaCC FROM chamcong
                    WHERE MaNV=? AND Ngay=? AND MaCC!=?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("isi", $maNV, $ngay, $ignoreMaCC);
        } else {
            $sql = "SELECT MaCC FROM chamcong
                    WHERE MaNV=? AND Ngay=?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("is", $maNV, $ngay);
        }

        $stmt->execute();
        $stmt->store_result();
        return $stmt->num_rows > 0;
    }

    /* ================== DANH SÁCH CHẤM CÔNG ================== */
    public function getAllChamCong() {
        $sql = "SELECT cc.*, nv.HoTen
                FROM chamcong cc
                JOIN nhanvien nv ON cc.MaNV = nv.MaNV
                ORDER BY cc.Ngay DESC, nv.HoTen ASC";

        return $this->conn->query($sql);
    }

    /* ================== LẤY THEO ID ================== */
    public function getChamCongById($maCC) {
        $sql = "SELECT * FROM chamcong WHERE MaCC=?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $maCC);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /* ================== THÊM CHẤM CÔNG ================== */
    public function insertChamCong($maNV, $ngay, $gioVao, $gioRa, $trangThai, $ghiChu = null){

        // nếu không đi làm → không có giờ vào ra
        if ($trangThai !== "Di lam") {
            $gioVao = null;
            $gioRa  = null;
        }

        $sql = "INSERT INTO chamcong
                (MaNV, Ngay, GioVao, GioRa, TrangThai, GhiChu)
                VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("isssss",
            $maNV, $ngay, $gioVao, $gioRa, $trangThai, $ghiChu
        );

        return $stmt->execute();
    }

    /* ================== CẬP NHẬT ================== */
    public function updateChamCong($maCC,$maNV,$ngay,$gioVao,$gioRa,$trangThai,$ghiChu=null){

        if ($trangThai !== "Di lam") {
            $gioVao = null;
            $gioRa  = null;
        }

        $sql = "UPDATE chamcong SET
                MaNV=?,
                Ngay=?,
                GioVao=?,
                GioRa=?,
                TrangThai=?,
                GhiChu=?
                WHERE MaCC=?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("isssssi",
            $maNV,$ngay,$gioVao,$gioRa,$trangThai,$ghiChu,$maCC
        );

        return $stmt->execute();
    }

    /* ================== XOÁ ================== */
    public function deleteChamCong($maCC) {
        $sql="DELETE FROM chamcong WHERE MaCC=?";
        $stmt=$this->conn->prepare($sql);
        $stmt->bind_param("i",$maCC);
        return $stmt->execute();
    }

    /* ================== NHÂN VIÊN ĐANG LÀM ================== */
    public function getAllNhanVien() {
        return $this->conn->query(
            "SELECT MaNV, HoTen FROM nhanvien WHERE TrangThai='Đang làm' ORDER BY HoTen"
        );
    }

    /* ================== DANH SÁCH PHÒNG BAN ================== */
    public function getAllPhongBan(){
        return $this->conn->query(
            "SELECT MaPB, TenPB FROM phongban ORDER BY TenPB"
        );
    }

    /* =========================================================
       BÁO CÁO TỔNG HỢP (KHÔNG LƯU DB - TÍNH ĐỘNG TỪ chamcong)
       ========================================================= */
    public function getBaoCaoThang($thang, $nam, $maPB = null)
    {
        $sql = "
            SELECT
                nv.MaNV,
                nv.HoTen,
                pb.TenPB,

                COUNT(CASE WHEN cc.TrangThai='Di lam' THEN 1 END) AS SoNgayCong,

                ROUND(SUM(
                    CASE
                        WHEN cc.GioVao IS NOT NULL AND cc.GioRa IS NOT NULL
                        THEN TIMESTAMPDIFF(MINUTE, cc.GioVao, cc.GioRa)/60
                        ELSE 0
                    END
                ),2) AS TongGioLam

            FROM chamcong cc

            JOIN nhanvien nv ON nv.MaNV = cc.MaNV

            /* xác định phòng ban theo lịch sử phân công */
            JOIN phancong pc ON pc.MaNV = nv.MaNV
                AND cc.Ngay BETWEEN pc.NgayBatDau AND IFNULL(pc.NgayKetThuc, cc.Ngay)

            JOIN phongban pb ON pb.MaPB = pc.MaPB

            WHERE MONTH(cc.Ngay)=? AND YEAR(cc.Ngay)=?
        ";

        if ($maPB) {
            $sql .= " AND pb.MaPB=?";
        }

        $sql .= " GROUP BY nv.MaNV, pb.MaPB ORDER BY nv.HoTen ASC";

        $stmt = $this->conn->prepare($sql);

        if ($maPB) {
            $stmt->bind_param("iii", $thang, $nam, $maPB);
        } else {
            $stmt->bind_param("ii", $thang, $nam);
        }

        $stmt->execute();
        return $stmt->get_result();
    }
    public function getAllPhongBan_Array(){
    $sql = "SELECT MaPB, TenPB FROM phongban ORDER BY TenPB";
    $rs = $this->conn->query($sql);

    $data = [];
    while($row = $rs->fetch_assoc()){
        $data[] = $row;
    }
    return $data;
}
public function bangChamCongThang($thang, $nam)
{
    $config = $this->getCauHinhChamCong();

$gioChuan = strtotime($config['GioChuanVao']);
    $sql = "
        SELECT
            nv.MaNV,
            nv.HoTen,
            pb.TenPB,
            DAY(cc.Ngay) as Ngay,
            cc.TrangThai,
            cc.GioVao
        FROM nhanvien nv

        JOIN phancong pc ON pc.MaNV = nv.MaNV
        JOIN phongban pb ON pb.MaPB = pc.MaPB

        LEFT JOIN chamcong cc
            ON cc.MaNV = nv.MaNV
            AND MONTH(cc.Ngay)=?
            AND YEAR(cc.Ngay)=?

        WHERE nv.TrangThai='Đang làm'
        ORDER BY pb.TenPB, nv.MaNV, cc.Ngay
    ";

    $stmt = $this->conn->prepare($sql);

    if(!$stmt){
        die("SQL ERROR: ".$this->conn->error); // giúp debug nếu sau này sai nữa
    }

    $stmt->bind_param("ii",$thang,$nam);
    $stmt->execute();
    $rs = $stmt->get_result();

    $data = [];

    while($row = $rs->fetch_assoc()){

        $pb = $row['TenPB'];
        $ma = $row['MaNV'];

        if(!isset($data[$pb][$ma])){
            $data[$pb][$ma] = [
                'MaNV'=>$ma,
                'HoTen'=>$row['HoTen'],
                'Ngay'=>[],
                'TongCong'=>0
            ];
        }

        if(!empty($row['Ngay'])){

            $d = str_pad($row['Ngay'],2,'0',STR_PAD_LEFT);

           if($row['TrangThai']=='Di lam' && !empty($row['GioVao'])){

    $vao = strtotime($row['GioVao']);
    $trePhut = ($vao - $gioChuan) / 60;

    if($trePhut <= $config['MucTre1']){
        $cong = $config['CongTre1'];
        $kyHieu = 'X';
    }
    elseif($trePhut <= $config['MucTre2']){
        $cong = $config['CongTre2'];
        $kyHieu = 'M';
    }
    elseif($trePhut <= $config['MucTre3']){
        $cong = $config['CongTre3'];
        $kyHieu = 'M';
    }
    else{
        $cong = $config['CongQuaTre'];
        $kyHieu = 'V';
    }

    $data[$pb][$ma]['Ngay'][$d] = $kyHieu;
    $data[$pb][$ma]['TongCong'] += $cong;
}
           elseif($row['TrangThai']=='Nghi phep'){
    $data[$pb][$ma]['Ngay'][$d]='P';
    // KHÔNG cộng công  
}
        }
    }

    foreach($data as $pb=>$ds){
        $data[$pb] = array_values($ds);
    }

    return $data;
}
/* ================== LƯU NHANH 1 Ô CHẤM CÔNG ================== */
/* =====================================================
   LƯU CHẤM NHANH (UPSERT)
   ===================================================== */
public function saveChamCongNhanh($maNV, $ngay, $trangThai, $kyHieu) {
    // Thiết lập giờ
    $gioVao = ($kyHieu == 'X') ? '08:00:00' : (($kyHieu == 'M') ? '09:00:00' : null);
    $gioRa  = ($kyHieu == 'X' || $kyHieu == 'M') ? '17:00:00' : null;

    // Kiểm tra tồn tại
    $check = $this->conn->prepare("SELECT MaCC FROM chamcong WHERE MaNV = ? AND Ngay = ?");
    $check->bind_param("is", $maNV, $ngay);
    $check->execute();
    $res = $check->get_result();

    if ($row = $res->fetch_assoc()) {
        // Cập nhật
        $stmt = $this->conn->prepare("UPDATE chamcong SET TrangThai=?, GioVao=?, GioRa=? WHERE MaCC=?");
        $stmt->bind_param("sssi", $trangThai, $gioVao, $gioRa, $row['MaCC']);
    } else {
        // Thêm mới
        $stmt = $this->conn->prepare("INSERT INTO chamcong (MaNV, Ngay, TrangThai, GioVao, GioRa) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $maNV, $ngay, $trangThai, $gioVao, $gioRa);
    }
    return $stmt->execute();
}
public function getCauHinhChamCong()
{
    $sql = "SELECT * FROM cauhinh_chamcong LIMIT 1";
    $rs = $this->conn->query($sql);
    return $rs->fetch_assoc();
}
}
?>