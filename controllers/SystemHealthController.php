<?php
require_once 'core/AuthMiddleware.php';
require_once 'core/AppLogger.php';
require_once 'models/TaiKhoanModel.php';

class SystemHealthController {
    private $conn;
    private $accountModel;

    public function __construct($conn) {
        $this->conn = $conn;
        $this->accountModel = new TaiKhoanModel($conn);
    }

    public function index() {
        $this->requireAdminOnly();

        $schemaChecks = [
            'taikhoan.MaNVRef' => $this->columnExists('taikhoan', 'MaNVRef'),
            'taikhoan.BuocDoiMatKhau' => $this->columnExists('taikhoan', 'BuocDoiMatKhau'),
            'taikhoan.NgayCapMatKhauTam' => $this->columnExists('taikhoan', 'NgayCapMatKhauTam'),
            'password_reset_tokens.token_hash' => $this->columnExists('password_reset_tokens', 'token_hash'),
        ];

        $migrationStatus = $this->getMigrationStatus();
        $authStats = $this->getAuthStats();
        $lastErrors = $this->getRecentErrors(8);
        $healthCheckReport = $_SESSION['health_check_report'] ?? null;
        unset($_SESSION['health_check_report']);

        include 'views/systemhealth/index.php';
    }

    public function runChecks() {
        $this->requireAdminOnly();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = 'Yêu cầu không hợp lệ.';
            header('Location: index.php?controller=systemhealth');
            exit;
        }

        $startedAt = microtime(true);
        $checks = [];

        $checks[] = [
            'name' => 'Schema auth bắt buộc',
            'ok' => $this->columnExists('taikhoan', 'MaNVRef')
                && $this->columnExists('taikhoan', 'BuocDoiMatKhau')
                && $this->columnExists('taikhoan', 'NgayCapMatKhauTam')
                && $this->columnExists('password_reset_tokens', 'token_hash'),
            'detail' => 'Kiểm tra các cột cốt lõi cho auth nội bộ và mật khẩu tạm.',
        ];

        $migrationStatus = $this->getMigrationStatus();
        $checks[] = [
            'name' => 'Migration đồng bộ',
            'ok' => (int)$migrationStatus['pending_count'] === 0,
            'detail' => (int)$migrationStatus['pending_count'] === 0
                ? 'Không có migration chờ áp dụng.'
                : 'Còn ' . (int)$migrationStatus['pending_count'] . ' migration chờ áp dụng.',
        ];

        $checks[] = $this->runTemporaryPasswordToggleCheck();
        $checks[] = $this->runInternalRecoveryFlowCheck();

        $passed = 0;
        foreach ($checks as $check) {
            if (!empty($check['ok'])) {
                $passed++;
            }
        }

        $durationMs = (int)round((microtime(true) - $startedAt) * 1000);
        $report = [
            'executed_at' => date('d/m/Y H:i:s'),
            'duration_ms' => $durationMs,
            'total' => count($checks),
            'passed' => $passed,
            'failed' => count($checks) - $passed,
            'checks' => $checks,
        ];

        $_SESSION['health_check_report'] = $report;

        if ($report['failed'] > 0) {
            $_SESSION['error'] = 'Health check hoàn tất với ' . $report['failed'] . ' lỗi.';
            AppLogger::warning('System health check found failures', ['report' => $report]);
        } else {
            $_SESSION['success'] = 'Health check hoàn tất: tất cả kiểm tra đều đạt.';
        }

        header('Location: index.php?controller=systemhealth');
        exit;
    }

    private function columnExists(string $table, string $column): bool {
        $table = preg_replace('/[^A-Za-z0-9_]/', '', $table);
        $columnEscaped = mysqli_real_escape_string($this->conn, $column);
        $sql = "SHOW COLUMNS FROM `{$table}` LIKE '{$columnEscaped}'";
        $result = $this->conn->query($sql);
        return (bool)($result && $result->num_rows > 0);
    }

    private function tableExists(string $table): bool {
        $tableEscaped = mysqli_real_escape_string($this->conn, $table);
        $sql = "SHOW TABLES LIKE '{$tableEscaped}'";
        $result = $this->conn->query($sql);
        return (bool)($result && $result->num_rows > 0);
    }

    private function getMigrationStatus(): array {
        $migrationsDir = __DIR__ . '/../migrations';
        $files = glob($migrationsDir . '/*.sql') ?: [];
        sort($files, SORT_NATURAL);

        $available = array_map('basename', $files);

        $applied = [];
        if ($this->tableExists('schema_migrations')) {
            $result = $this->conn->query('SELECT filename FROM schema_migrations ORDER BY executed_at DESC');
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $applied[] = (string)$row['filename'];
                }
            }
        }

        $pending = array_values(array_diff($available, $applied));

        return [
            'available_count' => count($available),
            'applied_count' => count($applied),
            'pending_count' => count($pending),
            'pending_files' => $pending,
        ];
    }

    private function getAuthStats(): array {
        $stats = [
            'accounts_total' => 0,
            'accounts_temporary_password' => 0,
            'accounts_with_manvref' => 0,
            'accounts_without_manvref' => 0,
            'reset_tokens_active' => 0,
        ];

        $result = $this->conn->query("SELECT
            COUNT(*) AS total,
            SUM(CASE WHEN BuocDoiMatKhau = 1 THEN 1 ELSE 0 END) AS temp_pw,
            SUM(CASE WHEN MaNVRef IS NOT NULL AND MaNVRef > 0 THEN 1 ELSE 0 END) AS has_ref,
            SUM(CASE WHEN MaNVRef IS NULL OR MaNVRef <= 0 THEN 1 ELSE 0 END) AS no_ref
            FROM taikhoan");

        if ($result) {
            $row = $result->fetch_assoc();
            $stats['accounts_total'] = (int)($row['total'] ?? 0);
            $stats['accounts_temporary_password'] = (int)($row['temp_pw'] ?? 0);
            $stats['accounts_with_manvref'] = (int)($row['has_ref'] ?? 0);
            $stats['accounts_without_manvref'] = (int)($row['no_ref'] ?? 0);
        }

        if ($this->tableExists('password_reset_tokens')) {
            $resToken = $this->conn->query("SELECT COUNT(*) AS c FROM password_reset_tokens WHERE used_at IS NULL AND expires_at > NOW()");
            if ($resToken) {
                $tokenRow = $resToken->fetch_assoc();
                $stats['reset_tokens_active'] = (int)($tokenRow['c'] ?? 0);
            }
        }

        return $stats;
    }

    private function getRecentErrors(int $limit): array {
        $path = __DIR__ . '/../logs/app.log';
        if (!is_readable($path)) {
            return [];
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return [];
        }

        $errors = [];
        for ($i = count($lines) - 1; $i >= 0; $i--) {
            if (stripos($lines[$i], '[ERROR]') !== false) {
                $errors[] = $lines[$i];
            }
            if (count($errors) >= $limit) {
                break;
            }
        }

        return $errors;
    }

    private function runTemporaryPasswordToggleCheck(): array {
        $result = $this->conn->query('SELECT MaTK, MatKhau, BuocDoiMatKhau FROM taikhoan ORDER BY MaTK ASC LIMIT 1');
        if (!$result || $result->num_rows === 0) {
            return [
                'name' => 'Toggle mật khẩu tạm',
                'ok' => false,
                'detail' => 'Không có tài khoản để kiểm tra.',
            ];
        }

        $row = $result->fetch_assoc();
        $maTK = (int)($row['MaTK'] ?? 0);
        $oldHash = (string)($row['MatKhau'] ?? '');
        $oldFlag = (int)($row['BuocDoiMatKhau'] ?? 0);

        $this->conn->begin_transaction();
        try {
            $tempHash = password_hash('HealthCheckTemp123!', PASSWORD_DEFAULT);
            if (!$this->accountModel->updatePasswordByMaTK($maTK, $tempHash, true)) {
                throw new RuntimeException('Không thể bật cờ mật khẩu tạm.');
            }

            if (!$this->accountModel->isPasswordChangeRequired($maTK)) {
                throw new RuntimeException('Cờ mật khẩu tạm không được bật như mong đợi.');
            }

            if (!$this->accountModel->updatePasswordByMaTK($maTK, $oldHash, false)) {
                throw new RuntimeException('Không thể khôi phục mật khẩu ban đầu.');
            }

            $stmt = $this->conn->prepare('UPDATE taikhoan SET BuocDoiMatKhau = ? WHERE MaTK = ?');
            if ($stmt) {
                $stmt->bind_param('ii', $oldFlag, $maTK);
                $stmt->execute();
                $stmt->close();
            }

            $this->conn->rollback();
            return [
                'name' => 'Toggle mật khẩu tạm',
                'ok' => true,
                'detail' => 'Bật/tắt cờ BuocDoiMatKhau hoạt động đúng và rollback thành công.',
            ];
        } catch (Throwable $e) {
            $this->conn->rollback();
            return [
                'name' => 'Toggle mật khẩu tạm',
                'ok' => false,
                'detail' => $e->getMessage(),
            ];
        }
    }

    private function runInternalRecoveryFlowCheck(): array {
        $requiredTables = ['taikhoan', 'nhanvien'];
        foreach ($requiredTables as $table) {
            if (!$this->tableExists($table)) {
                return [
                    'name' => 'Khôi phục nội bộ 4 yếu tố',
                    'ok' => false,
                    'detail' => 'Thiếu bảng ' . $table . ' để chạy kiểm tra.',
                ];
            }
        }

        $seed = time();
        $testMaNV = 910000 + ($seed % 10000);
        $username = 'health_user_' . $seed;
        $phone = '09011234';
        $dob = '1999-12-31';
        $phoneSuffix = '1234';

        $this->conn->begin_transaction();
        try {
            $stmtNv = $this->conn->prepare('INSERT INTO nhanvien (MaNV, HoTen, GioiTinh, NgaySinh, Email, DienThoai, TrangThai, MaBac, MaHS) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
            if (!$stmtNv) {
                throw new RuntimeException('Không thể tạo bản ghi nhân viên test.');
            }

            $hoTen = 'Health Check User';
            $gioiTinh = 'Nam';
            $email = $username . '@example.com';
            $trangThai = 'Đang làm';
            $maBac = 8;
            $maHS = null;

            $stmtNv->bind_param('issssssii', $testMaNV, $hoTen, $gioiTinh, $dob, $email, $phone, $trangThai, $maBac, $maHS);
            if (!$stmtNv->execute()) {
                throw new RuntimeException('Không thể insert nhân viên test: ' . $stmtNv->error);
            }
            $stmtNv->close();

            $hash = password_hash('HealthOrigin123!', PASSWORD_DEFAULT);
            $tkStatus = 'Hoạt động';
            $maNVCode = 'L' . $testMaNV;

            if ($this->columnExists('taikhoan', 'VaiTro')) {
                $stmtTk = $this->conn->prepare('INSERT INTO taikhoan (TenDangNhap, MatKhau, VaiTro, MaNV, MaNVRef, TrangThai, BuocDoiMatKhau, NgayCapMatKhauTam) VALUES (?, ?, ?, ?, ?, ?, 0, NULL)');
                if (!$stmtTk) {
                    throw new RuntimeException('Không thể tạo tài khoản test (schema VaiTro).');
                }

                $vaiTro = 'NhanVien';
                $stmtTk->bind_param('ssssis', $username, $hash, $vaiTro, $maNVCode, $testMaNV, $tkStatus);
            } else {
                $stmtTk = $this->conn->prepare('INSERT INTO taikhoan (TenDangNhap, MatKhau, MaNV, MaNVRef, TrangThai, BuocDoiMatKhau, NgayCapMatKhauTam) VALUES (?, ?, ?, ?, ?, 0, NULL)');
                if (!$stmtTk) {
                    throw new RuntimeException('Không thể tạo tài khoản test (schema mapping role).');
                }

                $stmtTk->bind_param('sssis', $username, $hash, $maNVCode, $testMaNV, $tkStatus);
            }

            if (!$stmtTk->execute()) {
                throw new RuntimeException('Không thể insert tài khoản test: ' . $stmtTk->error);
            }
            $maTK = (int)$stmtTk->insert_id;
            $stmtTk->close();

            $match = $this->accountModel->findAccountForInternalRecovery($username, (string)$testMaNV, $dob, $phoneSuffix);
            if (!$match || (int)($match['account']['MaTK'] ?? 0) !== $maTK) {
                throw new RuntimeException('Không khớp xác thực 4 yếu tố với dữ liệu đúng.');
            }

            $badMatch = $this->accountModel->findAccountForInternalRecovery($username, (string)$testMaNV, $dob, '9999');
            if ($badMatch !== null) {
                throw new RuntimeException('Xác thực không chặn được dữ liệu sai (4 số cuối điện thoại).');
            }

            $this->conn->rollback();
            return [
                'name' => 'Khôi phục nội bộ 4 yếu tố',
                'ok' => true,
                'detail' => 'Xác thực đúng với dữ liệu chuẩn và chặn được trường hợp sai.',
            ];
        } catch (Throwable $e) {
            $this->conn->rollback();
            return [
                'name' => 'Khôi phục nội bộ 4 yếu tố',
                'ok' => false,
                'detail' => $e->getMessage(),
            ];
        }
    }

    private function requireAdminOnly(): void {
        if (!AuthMiddleware::isAdmin($this->conn)) {
            AppLogger::security('System health access denied (admin only)', [
                'MaTK' => (int)($_SESSION['MaTK'] ?? 0),
                'uri' => $_SERVER['REQUEST_URI'] ?? '',
            ]);

            http_response_code(403);
            $_SESSION['error'] = 'Chức năng này chỉ dành cho tài khoản Admin.';
            include __DIR__ . '/../views/errors/403.php';
            exit;
        }
    }
}
