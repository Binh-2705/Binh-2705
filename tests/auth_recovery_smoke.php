<?php
require_once __DIR__ . '/../ketnoi.php';
require_once __DIR__ . '/../models/TaiKhoanModel.php';

function fail(string $message): void {
    fwrite(STDERR, "[FAIL] " . $message . PHP_EOL);
    exit(1);
}

function pass(string $message): void {
    fwrite(STDOUT, "[PASS] " . $message . PHP_EOL);
}

$model = new TaiKhoanModel($conn);

$now = time();
$testMaNV = 900000 + ($now % 10000);
$testUser = 'smoke_user_' . $now;
$testPass = password_hash('SmokeOriginal123!', PASSWORD_DEFAULT);
$testPhone = '09011234';
$testPhoneSuffix = '1234';
$testDob = '1999-12-31';

$conn->begin_transaction();

try {
    $stmtNv = $conn->prepare('INSERT INTO nhanvien (MaNV, HoTen, GioiTinh, NgaySinh, Email, DienThoai, TrangThai, MaBac, MaHS) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
    if (!$stmtNv) {
        throw new RuntimeException('Cannot prepare insert nhanvien: ' . $conn->error);
    }

    $hoTen = 'Smoke Test User';
    $gioiTinh = 'Nam';
    $email = $testUser . '@example.com';
    $trangThai = 'Đang làm';
    $maBac = 8;
    $maHS = null;

    $stmtNv->bind_param('issssssii', $testMaNV, $hoTen, $gioiTinh, $testDob, $email, $testPhone, $trangThai, $maBac, $maHS);
    if (!$stmtNv->execute()) {
        throw new RuntimeException('Cannot insert nhanvien test row: ' . $stmtNv->error);
    }
    $stmtNv->close();

    $stmtTk = $conn->prepare('INSERT INTO taikhoan (TenDangNhap, MatKhau, MaNV, MaNVRef, TrangThai, BuocDoiMatKhau, NgayCapMatKhauTam) VALUES (?, ?, ?, ?, ?, 0, NULL)');
    if (!$stmtTk) {
        throw new RuntimeException('Cannot prepare insert taikhoan: ' . $conn->error);
    }

    $maNVCode = 'L' . $testMaNV;
    $tkStatus = 'Hoạt động';
    $stmtTk->bind_param('sssis', $testUser, $testPass, $maNVCode, $testMaNV, $tkStatus);
    if (!$stmtTk->execute()) {
        throw new RuntimeException('Cannot insert taikhoan test row: ' . $stmtTk->error);
    }
    $maTK = (int)$stmtTk->insert_id;
    $stmtTk->close();

    $match = $model->findAccountForInternalRecovery($testUser, (string)$testMaNV, $testDob, $testPhoneSuffix);
    if (!$match || (int)($match['account']['MaTK'] ?? 0) !== $maTK) {
        throw new RuntimeException('Internal recovery should pass with correct data.');
    }
    pass('Internal recovery passes with valid identity factors');

    $matchPrefixCode = $model->findAccountForInternalRecovery($testUser, $maNVCode, $testDob, $testPhoneSuffix);
    if (!$matchPrefixCode) {
        throw new RuntimeException('Internal recovery should accept prefixed employee code (e.g., L900001).');
    }
    pass('Internal recovery accepts prefixed employee code format');

    $badPhone = $model->findAccountForInternalRecovery($testUser, (string)$testMaNV, $testDob, '9999');
    if ($badPhone !== null) {
        throw new RuntimeException('Internal recovery should fail when phone suffix does not match.');
    }
    pass('Internal recovery rejects invalid phone suffix');

    $badDob = $model->findAccountForInternalRecovery($testUser, (string)$testMaNV, '2000-01-01', $testPhoneSuffix);
    if ($badDob !== null) {
        throw new RuntimeException('Internal recovery should fail when date of birth does not match.');
    }
    pass('Internal recovery rejects invalid date of birth');

    $newHash = password_hash('SmokeNewPass123!', PASSWORD_DEFAULT);
    if (!$model->updatePasswordByMaTK($maTK, $newHash, false)) {
        throw new RuntimeException('Failed to update password through model.');
    }

    $checkStmt = $conn->prepare('SELECT MatKhau, BuocDoiMatKhau FROM taikhoan WHERE MaTK = ?');
    if (!$checkStmt) {
        throw new RuntimeException('Cannot prepare verification query: ' . $conn->error);
    }
    $checkStmt->bind_param('i', $maTK);
    $checkStmt->execute();
    $updated = $checkStmt->get_result()->fetch_assoc();
    $checkStmt->close();

    if (!$updated || !password_verify('SmokeNewPass123!', (string)$updated['MatKhau'])) {
        throw new RuntimeException('Updated password hash does not verify.');
    }
    if ((int)$updated['BuocDoiMatKhau'] !== 0) {
        throw new RuntimeException('BuocDoiMatKhau should remain 0 after normal recovery reset.');
    }
    pass('Password update via internal recovery flow is persisted as expected');

    $conn->rollback();
    fwrite(STDOUT, PHP_EOL . 'Auth recovery smoke tests completed successfully.' . PHP_EOL);
} catch (Throwable $e) {
    $conn->rollback();
    fail($e->getMessage());
}
