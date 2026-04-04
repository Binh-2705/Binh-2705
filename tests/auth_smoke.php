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

function hasColumn(mysqli $conn, string $table, string $column): bool {
    $table = preg_replace('/[^A-Za-z0-9_]/', '', $table);
    $columnEscaped = mysqli_real_escape_string($conn, $column);
    $sql = "SHOW COLUMNS FROM `{$table}` LIKE '{$columnEscaped}'";
    $result = $conn->query($sql);
    $ok = $result && $result->num_rows > 0;
    return $ok;
}

$model = new TaiKhoanModel($conn);

$requiredColumns = ['MaNVRef', 'BuocDoiMatKhau', 'NgayCapMatKhauTam'];
foreach ($requiredColumns as $column) {
    if (!hasColumn($conn, 'taikhoan', $column)) {
        fail("Missing column taikhoan.$column");
    }
}
pass('Required taikhoan columns exist');

$accountResult = $conn->query("SELECT MaTK, MatKhau, BuocDoiMatKhau FROM taikhoan ORDER BY MaTK ASC LIMIT 1");
if (!$accountResult || $accountResult->num_rows === 0) {
    fail('No account found to run smoke checks');
}
$account = $accountResult->fetch_assoc();
$maTK = (int)$account['MaTK'];
$oldHash = (string)$account['MatKhau'];
$oldFlag = (int)$account['BuocDoiMatKhau'];

$conn->begin_transaction();

$tempHash = password_hash('TempSmoke123!', PASSWORD_DEFAULT);
if (!$model->updatePasswordByMaTK($maTK, $tempHash, true)) {
    $conn->rollback();
    fail('Cannot set temporary password flag');
}

if (!$model->isPasswordChangeRequired($maTK)) {
    $conn->rollback();
    fail('Temporary password flag was not enabled');
}
pass('Temporary password flag is enabled correctly');

if (!$model->updatePasswordByMaTK($maTK, $oldHash, false)) {
    $conn->rollback();
    fail('Cannot clear temporary password flag');
}

if ($model->isPasswordChangeRequired($maTK)) {
    $conn->rollback();
    fail('Temporary password flag was not cleared');
}

$restoreStmt = $conn->prepare('UPDATE taikhoan SET BuocDoiMatKhau = ? WHERE MaTK = ?');
if ($restoreStmt) {
    $restoreStmt->bind_param('ii', $oldFlag, $maTK);
    $restoreStmt->execute();
    $restoreStmt->close();
}

$conn->rollback();
pass('Temporary password flag can be toggled and reverted safely (transaction rollback)');

fwrite(STDOUT, PHP_EOL . 'Auth smoke tests completed successfully.' . PHP_EOL);
