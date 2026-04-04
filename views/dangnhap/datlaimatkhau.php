<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt lại mật khẩu</title>
    <link rel="stylesheet" href="public/style1.css?v=20260404-2">
</head>
<body>

<div class="login-shell">
<div class="login-container login-compact">
    <div class="login-right login-center">
        <h2>Đặt lại mật khẩu 🔒</h2>
        <p>Nhập mật khẩu mới cho tài khoản của bạn</p>

        <form method="post" class="auth-form">
            <input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars($_SESSION['_csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars((string)($_GET['token'] ?? $_POST['token'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">

            <label>Mật khẩu mới</label>
            <input type="password" name="MatKhauMoi" minlength="8" required>

            <label>Xác nhận mật khẩu</label>
            <input type="password" name="XacNhanMatKhau" minlength="8" required>

            <button type="submit">CẬP NHẬT MẬT KHẨU</button>
        </form>

        <?php if (isset($thongbao)): ?>
            <p class="auth-alert auth-alert-success"><?php echo htmlspecialchars($thongbao, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>
        <?php if (isset($loi)): ?>
            <p class="auth-alert auth-alert-error"><?php echo htmlspecialchars($loi, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>

        <a class="back-link" href="index.php?controller=dangnhap&action=login">← Quay lại đăng nhập</a>
    </div>
</div>
</div>

</body>
</html>
