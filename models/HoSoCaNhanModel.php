<?php
class HoSoCaNhanModel {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
        $this->ensureProfileUpdateRequestTable();
}

private function ensureProfileUpdateRequestTable(): void {
    $sql = "CREATE TABLE IF NOT EXISTS hoso_update_requests (
        id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        MaNV INT NOT NULL,
        requested_by INT NOT NULL,
        requested_role VARCHAR(50) DEFAULT NULL,
        request_type ENUM('edit') NOT NULL DEFAULT 'edit',
        status_name ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
        payload_json MEDIUMTEXT NOT NULL,
        note TEXT DEFAULT NULL,
        review_note TEXT DEFAULT NULL,
        reviewed_by INT DEFAULT NULL,
        reviewed_at DATETIME DEFAULT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_hoso_update_requests_status (status_name),
        INDEX idx_hoso_update_requests_manv (MaNV),
        INDEX idx_hoso_update_requests_requested_by (requested_by)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

    @$this->conn->query($sql);
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

public function getByMaNV(int $maNV): ?array {
    $sql = "SELECT hs.*, nv.HoTen, pb.TenPB, cv.TenCV
            FROM hosonhanvien hs
            LEFT JOIN nhanvien nv ON hs.MaNV = nv.MaNV
            LEFT JOIN phongban pb ON hs.MaPB = pb.MaPB
            LEFT JOIN chucvu cv ON hs.MaCV = cv.MaCV
            WHERE hs.MaNV = ?
            LIMIT 1";

    $stmt = $this->conn->prepare($sql);
    if (!$stmt) {
        return null;
    }
    $stmt->bind_param("i", $maNV);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ?: null;
}

public function themHoSoChoNhanVien(int $maNV, array $data): bool {
    $sql = "INSERT INTO hosonhanvien(
                MaNV, CCCD, NoiCap, NgayCap, DiaChi,
                DanToc, TonGiao, TrinhDo, ChuyenMon,
                NgayVaoLam, MaPB, MaCV, TrangThaiHonNhan, Anh
            ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

    $stmt = $this->conn->prepare($sql);
    if (!$stmt) {
        return false;
    }

    $ngayVaoLam = $data['NgayVaoLam'] ?? date('Y-m-d');
    $maPB = isset($data['MaPB']) ? (int)$data['MaPB'] : 0;
    $maCV = isset($data['MaCV']) ? (int)$data['MaCV'] : 0;
    $anh = (string)($data['Anh'] ?? '');

    $stmt->bind_param(
        "issssssssiisss",
        $maNV,
        $data['CCCD'],
        $data['NoiCap'],
        $data['NgayCap'],
        $data['DiaChi'],
        $data['DanToc'],
        $data['TonGiao'],
        $data['TrinhDo'],
        $data['ChuyenMon'],
        $ngayVaoLam,
        $maPB,
        $maCV,
        $data['TrangThaiHonNhan'],
        $anh
    );

    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

public function capNhatHoSoByMaNV(int $maNV, array $data): bool {
    $sql = "UPDATE hosonhanvien SET
                CCCD = ?,
                NoiCap = ?,
                NgayCap = ?,
                DiaChi = ?,
                DanToc = ?,
                TonGiao = ?,
                TrinhDo = ?,
                ChuyenMon = ?,
                TrangThaiHonNhan = ?
            WHERE MaNV = ?";

    $stmt = $this->conn->prepare($sql);
    if (!$stmt) {
        return false;
    }

    $stmt->bind_param(
        "sssssssssi",
        $data['CCCD'],
        $data['NoiCap'],
        $data['NgayCap'],
        $data['DiaChi'],
        $data['DanToc'],
        $data['TonGiao'],
        $data['TrinhDo'],
        $data['ChuyenMon'],
        $data['TrangThaiHonNhan'],
        $maNV
    );

    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

public function savePendingUpdateRequest(int $maNV, int $requestedBy, string $requestedRole, array $payload, string $note = ''): bool {
    $payloadJson = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($payloadJson === false) {
        return false;
    }

    $findSql = "SELECT id FROM hoso_update_requests WHERE MaNV = ? AND status_name = 'pending' LIMIT 1";
    $findStmt = $this->conn->prepare($findSql);
    if ($findStmt) {
        $findStmt->bind_param("i", $maNV);
        $findStmt->execute();
        $row = $findStmt->get_result()->fetch_assoc();
        $findStmt->close();

        if ($row) {
            $updateSql = "UPDATE hoso_update_requests
                          SET payload_json = ?, note = ?, requested_by = ?, requested_role = ?, updated_at = CURRENT_TIMESTAMP
                          WHERE id = ?";
            $updateStmt = $this->conn->prepare($updateSql);
            if (!$updateStmt) {
                return false;
            }
            $id = (int)$row['id'];
            $updateStmt->bind_param("ssisi", $payloadJson, $note, $requestedBy, $requestedRole, $id);
            $ok = $updateStmt->execute();
            $updateStmt->close();
            return $ok;
        }
    }

    $insertSql = "INSERT INTO hoso_update_requests
                  (MaNV, requested_by, requested_role, request_type, status_name, payload_json, note)
                  VALUES (?, ?, ?, 'edit', 'pending', ?, ?)";
    $stmt = $this->conn->prepare($insertSql);
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param("iisss", $maNV, $requestedBy, $requestedRole, $payloadJson, $note);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

public function getUpdateRequests(string $status = 'pending') {
    $sql = "SELECT r.*, nv.HoTen, nv.Email, nv.DienThoai
            FROM hoso_update_requests r
            LEFT JOIN nhanvien nv ON r.MaNV = nv.MaNV
            WHERE r.status_name = ?
            ORDER BY r.created_at DESC";

    $stmt = $this->conn->prepare($sql);
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param("s", $status);
    $stmt->execute();
    return $stmt->get_result();
}

public function getUpdateRequestById(int $id): ?array {
    $sql = "SELECT * FROM hoso_update_requests WHERE id = ? LIMIT 1";
    $stmt = $this->conn->prepare($sql);
    if (!$stmt) {
        return null;
    }
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ?: null;
}

public function resolveUpdateRequest(int $id, string $status, int $reviewedBy, string $reviewNote = ''): bool {
    $sql = "UPDATE hoso_update_requests
            SET status_name = ?, reviewed_by = ?, review_note = ?, reviewed_at = NOW(), updated_at = NOW()
            WHERE id = ? AND status_name = 'pending'";
    $stmt = $this->conn->prepare($sql);
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param("sisi", $status, $reviewedBy, $reviewNote, $id);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
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