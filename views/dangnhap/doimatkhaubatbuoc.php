<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đổi mật khẩu bắt buộc</title>
    <link rel="stylesheet" href="public/style1.css?v=20260404-2">
</head>
<body>

<div class="login-shell">
<div class="login-container login-compact">
    <div class="login-right login-center">
        <h2>Đổi mật khẩu ngay</h2>
        <p>Tài khoản của bạn vừa được cấp mật khẩu tạm. Bạn cần tạo mật khẩu mới trước khi vào hệ thống.</p>

        <?php if (isset($loi)): ?>
            <p class="auth-alert auth-alert-error"><?php echo htmlspecialchars($loi, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>

        <form method="post" class="auth-form">
            <input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars($_SESSION['_csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            <label for="MatKhauMoi">Mật khẩu mới</label>
            <input id="MatKhauMoi" type="password" name="MatKhauMoi" required minlength="8" maxlength="100" placeholder="Ít nhất 8 ký tự">

            <label for="XacNhanMatKhau">Xác nhận mật khẩu mới</label>
            <input id="XacNhanMatKhau" type="password" name="XacNhanMatKhau" required minlength="8" maxlength="100" placeholder="Nhập lại mật khẩu mới">

            <button type="submit">CẬP NHẬT MẬT KHẨU</button>
        </form>

        <div class="auth-inline-note">
            Mật khẩu mới không được trùng mật khẩu tạm vừa cấp. Nếu bạn không thực hiện yêu cầu này, hãy đăng xuất và báo cho quản trị viên.
        </div>

        <a class="back-link" href="index.php?controller=dangnhap&action=dangxuat">← Đăng xuất</a>
    </div>
</div>
</div>

</body>
</html>