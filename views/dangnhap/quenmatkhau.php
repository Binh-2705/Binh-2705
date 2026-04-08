<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên mật khẩu</title>
    <link rel="stylesheet" href="public/style1.css?v=20260404-2">
</head>
<body>

<div class="login-shell">
<div class="login-container login-compact">
    <div class="login-right login-center">
        <h2>Quên mật khẩu 🔐</h2>
        <p>Xác thực bằng thông tin nội bộ để đặt lại mật khẩu mà không cần email.</p>

        <?php if (isset($thongbao)): ?>
            <p class="auth-alert auth-alert-success"><?php echo htmlspecialchars($thongbao, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>
        <?php if (isset($loi)): ?>
            <p class="auth-alert auth-alert-error"><?php echo htmlspecialchars($loi, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>

        <form method="post" class="auth-form">
            <input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars($_SESSION['_csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            <div class="auth-grid-2">
                <div>
                    <label for="TenDangNhap">Tên đăng nhập hoặc email tài khoản</label>
                    <input id="TenDangNhap" type="text" name="TenDangNhap" required maxlength="100" value="<?php echo htmlspecialchars($formData['TenDangNhap'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Ví dụ: binh192k5@gmail.com hoặc nb001">
                </div>
                <div>
                    <label for="MaNhanVien">Mã nhân sự</label>
                    <input id="MaNhanVien" type="text" name="MaNhanVien" required maxlength="20" value="<?php echo htmlspecialchars($formData['MaNhanVien'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Ví dụ: L001 hoặc 1">
                </div>
                <div>
                    <label for="NgaySinh">Ngày sinh</label>
                    <input id="NgaySinh" type="date" name="NgaySinh" required value="<?php echo htmlspecialchars($formData['NgaySinh'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                <div>
                    <label for="SoDienThoai4So">4 số cuối điện thoại</label>
                    <input id="SoDienThoai4So" type="text" name="SoDienThoai4So" required maxlength="4" pattern="[0-9]{4}" value="<?php echo htmlspecialchars($formData['SoDienThoai4So'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Ví dụ: 5601">
                </div>
            </div>

            <label for="MatKhauMoi">Mật khẩu mới</label>
            <input id="MatKhauMoi" type="password" name="MatKhauMoi" required minlength="8" maxlength="100" placeholder="Ít nhất 8 ký tự">

            <label for="XacNhanMatKhau">Xác nhận mật khẩu mới</label>
            <input id="XacNhanMatKhau" type="password" name="XacNhanMatKhau" required minlength="8" maxlength="100" placeholder="Nhập lại mật khẩu mới">

            <button type="submit">ĐẶT LẠI MẬT KHẨU</button>
        </form>

        <div class="auth-inline-note">
            Nếu một nhân viên có nhiều tài khoản, hãy nhập đúng tên đăng nhập của tài khoản cần khôi phục. Nếu hồ sơ nhân sự chưa có ngày sinh hoặc số điện thoại chính xác, hãy liên hệ quản trị viên để được cấp mật khẩu tạm.
        </div>

        <a class="back-link" href="index.php?controller=dangnhap&action=login">← Quay lại đăng nhập</a>
    </div>
</div>
</div>

</body>
</html>
