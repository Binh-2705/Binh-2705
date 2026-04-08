<?php
class TaiKhoanModel {
    private $conn;
    private $hasVaiTroColumn = null;

    public function __construct($conn){
        $this->conn = $conn;
        $this->ensurePasswordResetTable();
        $this->ensureAccountSecurityColumns();
        $this->ensureSessionAuditTable();
    }

    private function ensurePasswordResetTable() {
        $sql = "CREATE TABLE IF NOT EXISTS password_reset_tokens (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            MaTK INT NOT NULL,
            token_hash VARCHAR(255) NOT NULL,
            expires_at DATETIME NOT NULL,
            used_at DATETIME DEFAULT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_matk (MaTK),
            INDEX idx_expires (expires_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

        @$this->conn->query($sql);
    }

    private function ensureAccountSecurityColumns(): void {
        if (!$this->columnExists('taikhoan', 'MaNVRef')) {
            @$this->conn->query("ALTER TABLE taikhoan ADD COLUMN MaNVRef INT(11) NULL AFTER MaNV");
        }

        if (!$this->columnExists('taikhoan', 'BuocDoiMatKhau')) {
            @$this->conn->query("ALTER TABLE taikhoan ADD COLUMN BuocDoiMatKhau TINYINT(1) NOT NULL DEFAULT 0 AFTER TrangThai");
        }

        if (!$this->columnExists('taikhoan', 'NgayCapMatKhauTam')) {
            @$this->conn->query("ALTER TABLE taikhoan ADD COLUMN NgayCapMatKhauTam DATETIME DEFAULT NULL AFTER BuocDoiMatKhau");
        }

        $this->syncEmployeeReferenceCodes();
    }

    private function ensureSessionAuditTable(): void {
        $sql = "CREATE TABLE IF NOT EXISTS session_audit (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            MaTK INT NOT NULL,
            session_marker CHAR(64) NOT NULL,
            user_agent VARCHAR(255) DEFAULT NULL,
            ip_address VARCHAR(45) DEFAULT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            last_activity DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            revoked_at DATETIME DEFAULT NULL,
            UNIQUE KEY uniq_user_marker (MaTK, session_marker),
            INDEX idx_user_activity (MaTK, last_activity),
            INDEX idx_revoked (revoked_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

        @$this->conn->query($sql);
    }

    private function syncEmployeeReferenceCodes(): void {
        if (!$this->columnExists('taikhoan', 'MaNVRef')) {
            return;
        }

        $result = @$this->conn->query("SELECT MaTK, MaNV, MaNVRef FROM taikhoan");
        if (!$result) {
            return;
        }

        while ($row = $result->fetch_assoc()) {
            $maTK = (int)($row['MaTK'] ?? 0);
            if ($maTK <= 0) {
                continue;
            }

            $currentRef = isset($row['MaNVRef']) ? (int)$row['MaNVRef'] : 0;
            $calculatedRef = $this->extractEmployeeNumericCode((string)($row['MaNV'] ?? ''));

            if (($calculatedRef ?? 0) === $currentRef) {
                continue;
            }

            if ($calculatedRef === null) {
                $stmt = $this->conn->prepare("UPDATE taikhoan SET MaNVRef = NULL WHERE MaTK = ?");
                if (!$stmt) {
                    continue;
                }

                $stmt->bind_param("i", $maTK);
                $stmt->execute();
                $stmt->close();
                continue;
            }

            $stmt = $this->conn->prepare("UPDATE taikhoan SET MaNVRef = ? WHERE MaTK = ?");
            if (!$stmt) {
                continue;
            }

            $stmt->bind_param("ii", $calculatedRef, $maTK);
            $stmt->execute();
            $stmt->close();
        }
    }

    private function columnExists(string $table, string $column): bool {
        if (!preg_match('/^[A-Za-z0-9_]+$/', $table) || !preg_match('/^[A-Za-z0-9_]+$/', $column)) {
            return false;
        }

        $escapedColumn = $this->conn->real_escape_string($column);
        $result = @$this->conn->query("SHOW COLUMNS FROM `{$table}` LIKE '{$escapedColumn}'");
        $exists = $result && $result->num_rows > 0;

        return $exists;
    }

    public function dangNhap($tenDangNhap, $matKhau){
    if ($this->hasVaiTroColumn()) {
        $sql = "SELECT * FROM taikhoan WHERE TenDangNhap = ? LIMIT 1";
    } else {
        $sql = "SELECT tk.*, COALESCE(vt.TenVaiTro, 'NhanVien') AS VaiTro
                FROM taikhoan tk
                LEFT JOIN taikhoanvaitro tkvt ON tk.MaTK = tkvt.MaTK
                LEFT JOIN vaitro vt ON tkvt.MaVaiTro = vt.MaVaiTro
                WHERE tk.TenDangNhap = ?
                LIMIT 1";
    }

    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("s", $tenDangNhap);
    $stmt->execute();

    $tk = $stmt->get_result()->fetch_assoc();

    if ($tk && password_verify($matKhau, $tk['MatKhau'])) {
        return $tk;
    }
    return false;
}


    // LẤY DANH SÁCH + TÌM KIẾM
    public function getAll($key = ''){
        if ($this->hasVaiTroColumn()) {
            $sql = "SELECT * FROM taikhoan 
                WHERE TenDangNhap LIKE ?";
        } else {
            $sql = "SELECT tk.*, COALESCE(vt.TenVaiTro, 'NhanVien') AS VaiTro
                FROM taikhoan tk
                LEFT JOIN taikhoanvaitro tkvt ON tk.MaTK = tkvt.MaTK
                LEFT JOIN vaitro vt ON tkvt.MaVaiTro = vt.MaVaiTro
                WHERE tk.TenDangNhap LIKE ?";
        }

        $stmt = $this->conn->prepare($sql);
        $k = "%$key%";
        $stmt->bind_param("s", $k);
        $stmt->execute();
        return $stmt->get_result();
    }

    public function getById($id){
        if ($this->hasVaiTroColumn()) {
            $sql = "SELECT * FROM taikhoan WHERE MaTK = ? LIMIT 1";
        } else {
            $sql = "SELECT tk.*, COALESCE(vt.TenVaiTro, 'NhanVien') AS VaiTro
                    FROM taikhoan tk
                    LEFT JOIN taikhoanvaitro tkvt ON tk.MaTK = tkvt.MaTK
                    LEFT JOIN vaitro vt ON tkvt.MaVaiTro = vt.MaVaiTro
                    WHERE tk.MaTK = ?
                    LIMIT 1";
        }

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return null;
        }

        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result ? $result->fetch_assoc() : null;
        $stmt->close();
        return $row ?: null;
    }

    // THÊM
    public function insert($user, $pass, $vaitro, $manv){
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        $maNVRef = $this->extractEmployeeNumericCode((string)$manv);

        if ($this->hasVaiTroColumn()) {
            if ($maNVRef === null) {
                $sql = "INSERT INTO taikhoan 
                        (TenDangNhap, MatKhau, VaiTro, MaNV, MaNVRef)
                        VALUES (?,?,?, ?, NULL)";
                $stmt = $this->conn->prepare($sql);
                if (!$stmt) {
                    return false;
                }

                $stmt->bind_param("ssss", $user, $hash, $vaitro, $manv);
                return $stmt->execute();
            }

            $sql = "INSERT INTO taikhoan 
                    (TenDangNhap, MatKhau, VaiTro, MaNV, MaNVRef)
                    VALUES (?,?,?,?,?)";
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                return false;
            }

            $stmt->bind_param("ssssi", $user, $hash, $vaitro, $manv, $maNVRef);
            return $stmt->execute();
        }

        if ($maNVRef === null) {
            $sql = "INSERT INTO taikhoan (TenDangNhap, MatKhau, MaNV, MaNVRef) VALUES (?,?,?,NULL)";
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                return false;
            }

            $stmt->bind_param("sss", $user, $hash, $manv);
        } else {
            $sql = "INSERT INTO taikhoan (TenDangNhap, MatKhau, MaNV, MaNVRef) VALUES (?,?,?,?)";
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                return false;
            }

            $stmt->bind_param("sssi", $user, $hash, $manv, $maNVRef);
        }

        $ok = $stmt->execute();
        if (!$ok) {
            return false;
        }

        $newId = (int)$stmt->insert_id;
        $stmt->close();

        if ($newId <= 0) {
            return false;
        }

        return $this->assignRoleToAccount($newId, $vaitro);
    }

    // SỬA
    public function update($id, $vaitro, $manv){
        $maNVRef = $this->extractEmployeeNumericCode((string)$manv);

        if ($this->hasVaiTroColumn()) {
            if ($maNVRef === null) {
                $sql = "UPDATE taikhoan 
                        SET VaiTro=?, MaNV=?, MaNVRef=NULL
                        WHERE MaTK=?";
                $stmt = $this->conn->prepare($sql);
                if (!$stmt) {
                    return false;
                }

                $stmt->bind_param("ssi", $vaitro, $manv, $id);
                return $stmt->execute();
            }

            $sql = "UPDATE taikhoan 
                    SET VaiTro=?, MaNV=?, MaNVRef=?
                    WHERE MaTK=?";
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                return false;
            }

            $stmt->bind_param("ssii", $vaitro, $manv, $maNVRef, $id);
            return $stmt->execute();
        }

        if ($maNVRef === null) {
            $sql = "UPDATE taikhoan SET MaNV=?, MaNVRef=NULL WHERE MaTK=?";
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                return false;
            }

            $stmt->bind_param("si", $manv, $id);
        } else {
            $sql = "UPDATE taikhoan SET MaNV=?, MaNVRef=? WHERE MaTK=?";
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                return false;
            }

            $stmt->bind_param("sii", $manv, $maNVRef, $id);
        }

        $ok = $stmt->execute();
        $stmt->close();
        if (!$ok) {
            return false;
        }

        return $this->assignRoleToAccount((int)$id, $vaitro);
    }

    // XÓA
    public function delete($id){
        return $this->conn->query(
            "DELETE FROM taikhoan WHERE MaTK=$id"
        );
    }
    public function resetMatKhau($tenDangNhap, $matKhauMoi){
    $tenDangNhap = trim((string)$tenDangNhap);
    if ($tenDangNhap === '' || (string)$matKhauMoi === '') {
        return false;
    }

    $hash = password_hash((string)$matKhauMoi, PASSWORD_DEFAULT);
    $setParts = ["MatKhau = ?"];
    if ($this->columnExists('taikhoan', 'BuocDoiMatKhau')) {
        $setParts[] = "BuocDoiMatKhau = 0";
    }
    if ($this->columnExists('taikhoan', 'NgayCapMatKhauTam')) {
        $setParts[] = "NgayCapMatKhauTam = NULL";
    }

    $sql = "UPDATE taikhoan SET " . implode(", ", $setParts) . " WHERE TenDangNhap = ?";
    $stmt = $this->conn->prepare($sql);
    if (!$stmt) {
        return false;
    }

    $stmt->bind_param("ss", $hash, $tenDangNhap);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

public function checkTenDangNhap($tenDangNhap){
    $sql = "SELECT * FROM taikhoan WHERE TenDangNhap = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("s", $tenDangNhap);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

public function isUsernameAvailable(string $username, int $excludeMaTK = 0): bool {
    $username = trim($username);
    if ($username === '') {
        return false;
    }

    if ($excludeMaTK > 0) {
        $sql = "SELECT 1 FROM taikhoan WHERE TenDangNhap = ? AND MaTK <> ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("si", $username, $excludeMaTK);
    } else {
        $sql = "SELECT 1 FROM taikhoan WHERE TenDangNhap = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("s", $username);
    }

    $stmt->execute();
    $exists = $stmt->get_result()->num_rows > 0;
    $stmt->close();

    return !$exists;
}

public function updateUsernameByMaTK(int $maTK, string $newUsername): bool {
    $sql = "UPDATE taikhoan SET TenDangNhap = ? WHERE MaTK = ?";
    $stmt = $this->conn->prepare($sql);
    if (!$stmt) {
        return false;
    }

    $stmt->bind_param("si", $newUsername, $maTK);
    $ok = $stmt->execute();
    $stmt->close();

    return $ok;
}
public function getQuyenByTaiKhoan($maTK){
    $sql = "
        SELECT cn.TenChucNang
        FROM taikhoanvaitro tkvt
        JOIN vaitro vt ON tkvt.MaVaiTro = vt.MaVaiTro
        JOIN phanquyen pq ON vt.MaVaiTro = pq.MaVaiTro
        JOIN chucnang cn ON pq.MaCN = cn.MaCN
        WHERE tkvt.MaTK = ?
    ";

    $stmt = $this->conn->prepare($sql);

    if(!$stmt){
        die("SQL lỗi: " . $this->conn->error);
    }

    $stmt->bind_param("i", $maTK);
    $stmt->execute();

    $result = $stmt->get_result();

    $ds = [];
    while($row = $result->fetch_assoc()){
        $ds[] = $row['TenChucNang']; // ⚠️ đổi tên
    }

    return $ds;
}

public function getAccountByUsername(string $tenDangNhap){
    if ($this->hasVaiTroColumn()) {
        $sql = "SELECT * FROM taikhoan WHERE TenDangNhap = ? LIMIT 1";
    } else {
        $sql = "SELECT tk.*, COALESCE(vt.TenVaiTro, 'NhanVien') AS VaiTro
                FROM taikhoan tk
                LEFT JOIN taikhoanvaitro tkvt ON tk.MaTK = tkvt.MaTK
                LEFT JOIN vaitro vt ON tkvt.MaVaiTro = vt.MaVaiTro
                WHERE tk.TenDangNhap = ?
                LIMIT 1";
    }

    $stmt = $this->conn->prepare($sql);
    if (!$stmt) {
        return null;
    }

    $stmt->bind_param("s", $tenDangNhap);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stmt->close();
    return $row ?: null;
}

public function findAccountForResetIdentifier(string $identifier) {
    $identifier = trim($identifier);
    if ($identifier === '') {
        return null;
    }

    return $this->resolveRecoveryAccount($identifier);
}

public function findAccountForInternalRecovery(string $tenDangNhap, string $maNhanVien, string $ngaySinh, string $soDienThoai4So): ?array {
    $identifier = trim($tenDangNhap);
    $account = $this->resolveRecoveryAccount($identifier, $maNhanVien);
    if (!$account) {
        return null;
    }

    $employee = $this->findNhanVienForAccount($account);
    if (!$employee) {
        return null;
    }

    $allowedCodes = array_unique(array_merge(
        $this->employeeCodeVariants((string)($account['MaNV'] ?? '')),
        $this->employeeCodeVariants((string)($employee['MaNV'] ?? ''))
    ));

    $providedCodes = $this->employeeCodeVariants($maNhanVien);
    if (empty(array_intersect($allowedCodes, $providedCodes))) {
        return null;
    }

    $storedNgaySinh = substr((string)($employee['NgaySinh'] ?? ''), 0, 10);
    if ($storedNgaySinh === '' || $storedNgaySinh !== trim($ngaySinh)) {
        return null;
    }

    $phoneDigits = preg_replace('/\D+/', '', (string)($employee['DienThoai'] ?? ''));
    $storedPhoneSuffix = $phoneDigits !== '' ? substr($phoneDigits, -4) : '';
    $providedPhoneSuffix = substr(preg_replace('/\D+/', '', $soDienThoai4So), -4);
    if ($storedPhoneSuffix === '' || $storedPhoneSuffix !== $providedPhoneSuffix) {
        return null;
    }

    return [
        'account' => $account,
        'employee' => $employee,
    ];
}

private function resolveRecoveryAccount(string $identifier, string $maNhanVien = ''): ?array {
    $identifier = trim($identifier);
    if ($identifier === '') {
        return null;
    }

    $account = $this->getAccountByUsername($identifier);
    if ($account) {
        return $account;
    }

    if (!filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
        return null;
    }

    $employee = $this->findNhanVienByEmail($identifier);
    if (!$employee) {
        return null;
    }

    $maNV = (int)($employee['MaNV'] ?? 0);
    if ($maNV <= 0) {
        return null;
    }

    $accounts = $this->findAccountsForEmployee($maNV);
    if (count($accounts) === 1) {
        return $accounts[0];
    }

    if ($maNhanVien !== '') {
        $providedCodes = $this->employeeCodeVariants($maNhanVien);
        $filtered = array_values(array_filter($accounts, function (array $candidate) use ($providedCodes): bool {
            $candidateCodes = array_unique(array_merge(
                $this->employeeCodeVariants((string)($candidate['MaNV'] ?? '')),
                $this->employeeCodeVariants((string)($candidate['MaNVRef'] ?? ''))
            ));
            return !empty(array_intersect($candidateCodes, $providedCodes));
        }));

        if (count($filtered) === 1) {
            return $filtered[0];
        }
    }

    return null;
}

private function findAccountsForEmployee(int $maNV): array {
    if ($maNV <= 0) {
        return [];
    }

    $sql = "SELECT *
            FROM taikhoan
            WHERE MaNVRef = ? OR MaNV = ? OR MaNV = ?
            ORDER BY CASE WHEN TrangThai = 'Hoạt động' THEN 0 ELSE 1 END, MaTK ASC";
    $stmt = $this->conn->prepare($sql);
    if (!$stmt) {
        return [];
    }

    $maNVText = (string)$maNV;
    $prefixedCode = 'L' . str_pad((string)$maNV, 3, '0', STR_PAD_LEFT);
    $stmt->bind_param("iss", $maNV, $maNVText, $prefixedCode);
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();
    return $rows;
}

private function findNhanVienForAccount(array $account): ?array {
    $numericCode = isset($account['MaNVRef']) && (int)$account['MaNVRef'] > 0
        ? (int)$account['MaNVRef']
        : $this->extractEmployeeNumericCode((string)($account['MaNV'] ?? ''));
    if ($numericCode !== null) {
        $employee = $this->findNhanVienByMaNV($numericCode);
        if ($employee) {
            return $employee;
        }
    }

    $username = trim((string)($account['TenDangNhap'] ?? ''));
    if ($username !== '' && filter_var($username, FILTER_VALIDATE_EMAIL)) {
        $employee = $this->findNhanVienByEmail($username);
        if ($employee) {
            return $employee;
        }
    }

    return null;
}

private function findNhanVienByMaNV(int $maNV): ?array {
    $sql = "SELECT * FROM nhanvien WHERE MaNV = ? LIMIT 1";
    $stmt = $this->conn->prepare($sql);
    if (!$stmt) {
        return null;
    }

    $stmt->bind_param("i", $maNV);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    return $row ?: null;
}

private function findNhanVienByEmail(string $email): ?array {
    $sql = "SELECT * FROM nhanvien WHERE Email = ? LIMIT 1";
    $stmt = $this->conn->prepare($sql);
    if (!$stmt) {
        return null;
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    return $row ?: null;
}

private function extractEmployeeNumericCode(string $value): ?int {
    $digits = preg_replace('/\D+/', '', $value);
    if ($digits === '' || (int)$digits <= 0) {
        return null;
    }

    return (int)$digits;
}

private function normalizeEmployeeCode(string $value): string {
    return strtoupper(trim($value));
}

private function employeeCodeVariants(string $value): array {
    $variants = [];
    $normalized = $this->normalizeEmployeeCode($value);
    if ($normalized !== '') {
        $variants[] = $normalized;
    }

    $numericCode = $this->extractEmployeeNumericCode($value);
    if ($numericCode !== null) {
        $variants[] = (string)$numericCode;
    }

    return array_values(array_unique($variants));
}

public function getResetEmailForAccount(int $maTK): ?string {
    $sql = "SELECT * FROM taikhoan WHERE MaTK = ? LIMIT 1";

    $stmt = $this->conn->prepare($sql);
    if (!$stmt) {
        return null;
    }

    $stmt->bind_param("i", $maTK);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    if (!$row) {
        return null;
    }

    $employee = $this->findNhanVienForAccount($row);
    $emailNhanVien = trim((string)($employee['Email'] ?? ''));
    if ($emailNhanVien !== '' && filter_var($emailNhanVien, FILTER_VALIDATE_EMAIL)) {
        return $emailNhanVien;
    }

    $username = trim((string)($row['TenDangNhap'] ?? ''));
    if ($username !== '' && filter_var($username, FILTER_VALIDATE_EMAIL)) {
        return $username;
    }

    return null;
}

public function createPasswordResetToken(int $maTK, string $rawToken, string $expiresAt): bool {
    $hash = password_hash($rawToken, PASSWORD_DEFAULT);

    // Invalidate active old tokens
    $sqlExpireOld = "UPDATE password_reset_tokens SET used_at = NOW() WHERE MaTK = ? AND used_at IS NULL";
    $stmtOld = $this->conn->prepare($sqlExpireOld);
    if ($stmtOld) {
        $stmtOld->bind_param("i", $maTK);
        $stmtOld->execute();
        $stmtOld->close();
    }

    $sql = "INSERT INTO password_reset_tokens (MaTK, token_hash, expires_at) VALUES (?, ?, ?)";
    $stmt = $this->conn->prepare($sql);
    if (!$stmt) {
        return false;
    }

    $stmt->bind_param("iss", $maTK, $hash, $expiresAt);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

public function findValidResetToken(string $rawToken){
    $sql = "SELECT id, MaTK, token_hash, expires_at, used_at
            FROM password_reset_tokens
            WHERE used_at IS NULL
              AND expires_at > NOW()";
    $result = @$this->conn->query($sql);
    if (!$result) {
        return null;
    }

    while ($row = $result->fetch_assoc()) {
        if (password_verify($rawToken, $row['token_hash'])) {
            return $row;
        }
    }

    return null;
}

public function markResetTokenUsed(int $id): bool {
    $sql = "UPDATE password_reset_tokens SET used_at = NOW() WHERE id = ?";
    $stmt = $this->conn->prepare($sql);
    if (!$stmt) {
        return false;
    }

    $stmt->bind_param("i", $id);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

public function updatePasswordByMaTK(int $maTK, string $newHash, bool $forceChange = false): bool {
    $setParts = ["MatKhau = ?"];
    $hasForceColumn = $this->columnExists('taikhoan', 'BuocDoiMatKhau');
    $hasTempDateColumn = $this->columnExists('taikhoan', 'NgayCapMatKhauTam');

    if ($hasForceColumn) {
        $setParts[] = $forceChange ? "BuocDoiMatKhau = 1" : "BuocDoiMatKhau = 0";
    }

    if ($hasTempDateColumn) {
        $setParts[] = $forceChange ? "NgayCapMatKhauTam = NOW()" : "NgayCapMatKhauTam = NULL";
    }

    $sql = "UPDATE taikhoan SET " . implode(", ", $setParts) . " WHERE MaTK = ?";

    $stmt = $this->conn->prepare($sql);
    if (!$stmt) {
        return false;
    }

    $stmt->bind_param("si", $newHash, $maTK);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

public function isPasswordChangeRequired(int $maTK): bool {
    $sql = "SELECT BuocDoiMatKhau FROM taikhoan WHERE MaTK = ? LIMIT 1";
    $stmt = $this->conn->prepare($sql);
    if (!$stmt) {
        return false;
    }

    $stmt->bind_param("i", $maTK);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    return !empty($row['BuocDoiMatKhau']);
}

public function registerSessionAudit(int $maTK, string $sessionMarker, string $userAgent = '', string $ipAddress = ''): bool {
    if (!$this->tableExists('session_audit') || $maTK <= 0 || trim($sessionMarker) === '') {
        return false;
    }

    $sessionMarker = substr(trim($sessionMarker), 0, 64);
    $userAgent = substr(trim($userAgent), 0, 255);
    $ipAddress = substr(trim($ipAddress), 0, 45);

    $sql = "INSERT INTO session_audit (MaTK, session_marker, user_agent, ip_address, created_at, last_activity, revoked_at)
            VALUES (?, ?, ?, ?, NOW(), NOW(), NULL)
            ON DUPLICATE KEY UPDATE
                user_agent = VALUES(user_agent),
                ip_address = VALUES(ip_address),
                last_activity = NOW(),
                revoked_at = NULL";

    $stmt = $this->conn->prepare($sql);
    if (!$stmt) {
        return false;
    }

    $stmt->bind_param("isss", $maTK, $sessionMarker, $userAgent, $ipAddress);
    $ok = $stmt->execute();
    $stmt->close();

    return $ok;
}

public function touchSessionAudit(int $maTK, string $sessionMarker): bool {
    if (!$this->tableExists('session_audit') || $maTK <= 0 || trim($sessionMarker) === '') {
        return false;
    }

    $sql = "UPDATE session_audit SET last_activity = NOW() WHERE MaTK = ? AND session_marker = ? LIMIT 1";
    $stmt = $this->conn->prepare($sql);
    if (!$stmt) {
        return false;
    }

    $sessionMarker = substr(trim($sessionMarker), 0, 64);
    $stmt->bind_param("is", $maTK, $sessionMarker);
    $ok = $stmt->execute();
    $stmt->close();

    return $ok;
}

public function revokeOtherSessions(int $maTK, string $currentMarker): bool {
    if (!$this->tableExists('session_audit') || $maTK <= 0 || trim($currentMarker) === '') {
        return false;
    }

    $sql = "UPDATE session_audit
            SET revoked_at = NOW()
            WHERE MaTK = ?
              AND session_marker <> ?
              AND revoked_at IS NULL";

    $stmt = $this->conn->prepare($sql);
    if (!$stmt) {
        return false;
    }

    $currentMarker = substr(trim($currentMarker), 0, 64);
    $stmt->bind_param("is", $maTK, $currentMarker);
    $ok = $stmt->execute();
    $stmt->close();

    return $ok;
}

public function revokeCurrentSession(int $maTK, string $sessionMarker): bool {
    if (!$this->tableExists('session_audit') || $maTK <= 0 || trim($sessionMarker) === '') {
        return false;
    }

    $sql = "UPDATE session_audit
            SET revoked_at = NOW()
            WHERE MaTK = ?
              AND session_marker = ?
              AND revoked_at IS NULL
            LIMIT 1";

    $stmt = $this->conn->prepare($sql);
    if (!$stmt) {
        return false;
    }

    $sessionMarker = substr(trim($sessionMarker), 0, 64);
    $stmt->bind_param("is", $maTK, $sessionMarker);
    $ok = $stmt->execute();
    $stmt->close();

    return $ok;
}

public function isSessionRevoked(int $maTK, string $sessionMarker): bool {
    if (!$this->tableExists('session_audit') || $maTK <= 0 || trim($sessionMarker) === '') {
        return false;
    }

    $sql = "SELECT revoked_at
            FROM session_audit
            WHERE MaTK = ? AND session_marker = ?
            LIMIT 1";

    $stmt = $this->conn->prepare($sql);
    if (!$stmt) {
        return false;
    }

    $sessionMarker = substr(trim($sessionMarker), 0, 64);
    $stmt->bind_param("is", $maTK, $sessionMarker);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row) {
        return false;
    }

    return !empty($row['revoked_at']);
}

public function getRecentSessions(int $maTK, int $limit = 8): array {
    if (!$this->tableExists('session_audit') || $maTK <= 0) {
        return [];
    }

    $limit = max(1, min($limit, 20));
    $sql = "SELECT session_marker, user_agent, ip_address, created_at, last_activity, revoked_at
            FROM session_audit
            WHERE MaTK = ?
            ORDER BY last_activity DESC
            LIMIT ?";

    $stmt = $this->conn->prepare($sql);
    if (!$stmt) {
        return [];
    }

    $stmt->bind_param("ii", $maTK, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = [];
    while ($result && ($row = $result->fetch_assoc())) {
        $rows[] = $row;
    }
    $stmt->close();

    return $rows;
}

private function hasVaiTroColumn(): bool {
    if ($this->hasVaiTroColumn === null) {
        $this->hasVaiTroColumn = $this->columnExists('taikhoan', 'VaiTro');
    }

    return $this->hasVaiTroColumn;
}

private function assignRoleToAccount(int $maTK, string $roleName): bool {
    if ($maTK <= 0 || !$this->tableExists('taikhoanvaitro') || !$this->tableExists('vaitro')) {
        return true;
    }

    $maVaiTro = $this->findRoleIdByName($roleName);
    if ($maVaiTro <= 0) {
        return false;
    }

    $deleteStmt = $this->conn->prepare('DELETE FROM taikhoanvaitro WHERE MaTK = ?');
    if ($deleteStmt) {
        $deleteStmt->bind_param('i', $maTK);
        $deleteStmt->execute();
        $deleteStmt->close();
    }

    $insertStmt = $this->conn->prepare('INSERT INTO taikhoanvaitro (MaTK, MaVaiTro) VALUES (?, ?)');
    if (!$insertStmt) {
        return false;
    }

    $insertStmt->bind_param('ii', $maTK, $maVaiTro);
    $ok = $insertStmt->execute();
    $insertStmt->close();

    return $ok;
}

private function findRoleIdByName(string $roleName): int {
    $candidate = trim($roleName);
    if ($candidate === '') {
        $candidate = 'NhanVien';
    }

    $sql = 'SELECT MaVaiTro FROM vaitro WHERE LOWER(TenVaiTro) = LOWER(?) LIMIT 1';
    $stmt = $this->conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param('s', $candidate);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result ? $result->fetch_assoc() : null;
        $stmt->close();
        if (!empty($row['MaVaiTro'])) {
            return (int)$row['MaVaiTro'];
        }
    }

    $fallback = $this->conn->query("SELECT MaVaiTro FROM vaitro WHERE LOWER(TenVaiTro) = 'nhanvien' LIMIT 1");
    if ($fallback) {
        $row = $fallback->fetch_assoc();
        if (!empty($row['MaVaiTro'])) {
            return (int)$row['MaVaiTro'];
        }
    }

    return 0;
}

private function tableExists(string $table): bool {
    $tableEscaped = mysqli_real_escape_string($this->conn, $table);
    $result = $this->conn->query("SHOW TABLES LIKE '{$tableEscaped}'");
    return (bool)($result && $result->num_rows > 0);
}
}
