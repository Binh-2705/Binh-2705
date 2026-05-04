<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quên mật khẩu</title>
    <link rel="stylesheet" href="<?php echo e(asset('assets/css/style1.css')); ?>?v=20260410-1">
    <link rel="stylesheet" href="<?php echo e(asset('assets/css/legacy-bridge.css')); ?>?v=20260410-1">
</head>
<body>
    <main class="login-shell">
    <section class="login-container login-compact">
        <div class="login-center">
            <span class="auth-title-badge">Khôi phục truy cập</span>
            <h2>Khôi phục mật khẩu</h2>
            <p>Xác minh thông tin nội bộ để đặt lại mật khẩu mà không cần quay về hệ thống cũ.</p>
            <?php if($errors->any()): ?><div class="auth-alert auth-alert-error"><?php echo e($errors->first()); ?></div><?php endif; ?>
            <form method="post" action="<?php echo e(route('password.forgot.submit')); ?>" class="auth-form">
                <?php echo csrf_field(); ?>
                <div class="auth-grid-2">
                    <div><label for="TenDangNhap">Tên đăng nhập</label><input id="TenDangNhap" name="TenDangNhap" type="text" value="<?php echo e(old('TenDangNhap')); ?>" required></div>
                    <div><label for="MaNhanVien">Mã nhân viên</label><input id="MaNhanVien" name="MaNhanVien" type="text" value="<?php echo e(old('MaNhanVien')); ?>" required></div>
                    <div><label for="NgaySinh">Ngày sinh</label><input id="NgaySinh" name="NgaySinh" type="date" value="<?php echo e(old('NgaySinh')); ?>" required></div>
                    <div><label for="SoDienThoai4So">4 số cuối điện thoại</label><input id="SoDienThoai4So" name="SoDienThoai4So" type="text" value="<?php echo e(old('SoDienThoai4So')); ?>" required></div>
                    <div><label for="MatKhauMoi">Mật khẩu mới</label><input id="MatKhauMoi" name="MatKhauMoi" type="password" required></div>
                    <div><label for="XacNhanMatKhau">Xác nhận mật khẩu</label><input id="XacNhanMatKhau" name="XacNhanMatKhau" type="password" required></div>
                </div>
                <div class="auth-actions-row">
                    <button class="btn" type="submit">Đặt lại mật khẩu</button>
                    <a href="<?php echo e(route('login.form')); ?>" class="back-link">Quay lại đăng nhập</a>
                </div>
            </form>
            <div class="auth-inline-note">Mật khẩu mới nên đủ mạnh và khác với mật khẩu tạm đã cấp trước đó.</div>
        </div>
    </section>
    </main>
</body>
</html><?php /**PATH C:\xampp\htdocs\du_an2\laravel_app\resources\views/auth/forgot-password.blade.php ENDPATH**/ ?>