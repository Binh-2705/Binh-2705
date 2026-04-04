<?php
class DashboardModel{

    private $conn;

    public function __construct($conn){
        $this->conn = $conn;
        $this->ensureNotificationReadTable();
    }

    private function ensureNotificationReadTable(){
        $sql = "CREATE TABLE IF NOT EXISTS thongbao_daxem (
            MaTK INT NOT NULL PRIMARY KEY,
            DaXemNghiPhep INT NOT NULL DEFAULT 0,
            DaXemHopDong INT NOT NULL DEFAULT 0,
            DaXemUngVien INT NOT NULL DEFAULT 0,
            UpdatedAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

        $this->conn->query($sql);
    }

    public function getDashboardNotificationSnapshot($maTK){
        $totals = $this->getNotificationTotals();
        $latest = $this->getLatestNotificationDates();
        $seen = $this->getReadStateByAccount((int)$maTK);

        return [
            'totals' => $totals,
            'latest' => $latest,
            'seen' => $seen,
            'unread' => [
                'leave' => max(0, (int)$totals['leave'] - (int)$seen['leave']),
                'contract' => max(0, (int)$totals['contract'] - (int)$seen['contract']),
                'candidate' => max(0, (int)$totals['candidate'] - (int)$seen['candidate']),
            ]
        ];
    }

    public function markNotificationsRead($maTK, $type = 'all'){
        $maTK = (int)$maTK;
        if ($maTK <= 0) {
            return false;
        }

        $totals = $this->getNotificationTotals();
        $seen = $this->getReadStateByAccount($maTK);

        if ($type === 'leave') {
            $seen['leave'] = (int)$totals['leave'];
        } elseif ($type === 'contract') {
            $seen['contract'] = (int)$totals['contract'];
        } elseif ($type === 'candidate') {
            $seen['candidate'] = (int)$totals['candidate'];
        } else {
            $seen['leave'] = (int)$totals['leave'];
            $seen['contract'] = (int)$totals['contract'];
            $seen['candidate'] = (int)$totals['candidate'];
        }

        return $this->saveReadStateByAccount($maTK, $seen['leave'], $seen['contract'], $seen['candidate']);
    }

    private function getNotificationTotals(){
        return [
            'leave' => (int)$this->thongBaoNghiPhep(),
            'contract' => (int)$this->hopDongSapHetHan(),
            'candidate' => (int)$this->ungVienMoi(),
        ];
    }

    private function getLatestNotificationDates(){
        $leaveDate = null;
        $contractDate = null;
        $candidateDate = null;

        $sqlLeave = "SELECT MAX(NgayNopDon) AS latest_date FROM nghiphep WHERE TrangThai='Chờ duyệt'";
        if ($result = $this->conn->query($sqlLeave)) {
            $row = $result->fetch_assoc();
            $leaveDate = $row['latest_date'] ?? null;
        }

        $sqlContract = "SELECT MAX(NgayKetThuc) AS latest_date FROM hopdong WHERE NgayKetThuc IS NOT NULL AND NgayKetThuc <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
        if ($result = $this->conn->query($sqlContract)) {
            $row = $result->fetch_assoc();
            $contractDate = $row['latest_date'] ?? null;
        }

        $sqlCandidate = "SELECT MAX(NgayNop) AS latest_date FROM hosoungtuyen WHERE NgayNop >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
        if ($result = $this->conn->query($sqlCandidate)) {
            $row = $result->fetch_assoc();
            $candidateDate = $row['latest_date'] ?? null;
        }

        return [
            'leave' => $leaveDate,
            'contract' => $contractDate,
            'candidate' => $candidateDate,
        ];
    }

    private function getReadStateByAccount($maTK){
        $maTK = (int)$maTK;
        if ($maTK <= 0) {
            return ['leave' => 0, 'contract' => 0, 'candidate' => 0];
        }

        $sql = "SELECT DaXemNghiPhep, DaXemHopDong, DaXemUngVien FROM thongbao_daxem WHERE MaTK = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return ['leave' => 0, 'contract' => 0, 'candidate' => 0];
        }

        $stmt->bind_param('i', $maTK);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result ? $result->fetch_assoc() : null;
        $stmt->close();

        if (!$row) {
            return ['leave' => 0, 'contract' => 0, 'candidate' => 0];
        }

        return [
            'leave' => (int)$row['DaXemNghiPhep'],
            'contract' => (int)$row['DaXemHopDong'],
            'candidate' => (int)$row['DaXemUngVien'],
        ];
    }

    private function saveReadStateByAccount($maTK, $seenLeave, $seenContract, $seenCandidate){
        $sql = "INSERT INTO thongbao_daxem (MaTK, DaXemNghiPhep, DaXemHopDong, DaXemUngVien)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    DaXemNghiPhep = VALUES(DaXemNghiPhep),
                    DaXemHopDong = VALUES(DaXemHopDong),
                    DaXemUngVien = VALUES(DaXemUngVien),
                    UpdatedAt = CURRENT_TIMESTAMP";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('iiii', $maTK, $seenLeave, $seenContract, $seenCandidate);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public function tongNhanVien(){
        $sql = "SELECT COUNT(*) as tong FROM nhanvien";
        $result = $this->conn->query($sql);
        return $result->fetch_assoc()['tong'];
    }

    public function tongPhongBan(){
        $sql = "SELECT COUNT(*) as tong FROM phongban";
        $result = $this->conn->query($sql);
        return $result->fetch_assoc()['tong'];
    }

    public function donNghiChoDuyet(){
        $sql = "SELECT COUNT(*) as tong FROM nghiphep WHERE TrangThai='Chờ duyệt'";
        $result = $this->conn->query($sql);
        return $result->fetch_assoc()['tong'];
    }

    public function tongUngVien(){
        $sql = "SELECT COUNT(*) as tong FROM ungvien";
        $result = $this->conn->query($sql);
        return $result->fetch_assoc()['tong'];
    }

   public function nhanVienTheoPhongBan(){

    $sql = "SELECT pb.TenPB, COUNT(pc.MaNV) as SoLuong
            FROM phongban pb
            LEFT JOIN phancong pc ON pb.MaPB = pc.MaPB
            GROUP BY pb.MaPB";

    $result = $this->conn->query($sql);

    if(!$result){
        die("Lỗi SQL: " . $this->conn->error);
    }

    $data = [];

    while($row = $result->fetch_assoc()){
        $data[] = $row;
    }

    return $data;
}
public function thongBaoNghiPhep(){

$sql = "SELECT COUNT(*) as tong 
        FROM nghiphep 
        WHERE TrangThai='Chờ duyệt'";

$result = $this->conn->query($sql);
$row = $result->fetch_assoc();

return $row['tong'];

}


public function hopDongSapHetHan(){

$sql = "SELECT COUNT(*) as tong
        FROM hopdong
        WHERE NgayKetThuc <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)";

$result = $this->conn->query($sql);
$row = $result->fetch_assoc();

return $row['tong'];

}


public function ungVienMoi(){

$sql = "SELECT COUNT(*) as tong
        FROM hosoungtuyen
        WHERE NgayNop >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";

$result = $this->conn->query($sql);

if(!$result){
    die("Lỗi SQL: " . $this->conn->error);
}

$row = $result->fetch_assoc();

return $row['tong'];

}
public function tongNam(){

$sql = "SELECT COUNT(*) as total FROM nhanvien WHERE GioiTinh='Nam'";
$result = $this->conn->query($sql);
$row = $result->fetch_assoc();

return $row['total'];

}

public function tongNu(){

$sql = "SELECT COUNT(*) as total FROM nhanvien WHERE GioiTinh='Nữ'";
$result = $this->conn->query($sql);
$row = $result->fetch_assoc();

return $row['total'];

}
public function luongTrungBinhPhongBan(){

    $sql = "SELECT pb.TenPB, IFNULL(AVG(bl.TongLuong),0) as LuongTB
            FROM phongban pb
            LEFT JOIN phancong pc ON pb.MaPB = pc.MaPB
            LEFT JOIN nhanvien nv ON nv.MaNV = pc.MaNV
            LEFT JOIN bangluong bl ON bl.MaNV = nv.MaNV
            GROUP BY pb.MaPB";

    $result = $this->conn->query($sql);

    if(!$result){
        die("Lỗi SQL: " . $this->conn->error);
    }

    $data = [];

    while($row = $result->fetch_assoc()){
        $data[] = $row;
    }

    return $data;
}
}