<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Đăng nhập - Hệ thống nhân sự</title>
    <link rel="stylesheet" href="<?php echo e(asset('public/style1.css')); ?>?v=20260420-2">
    <link rel="stylesheet" href="<?php echo e(asset('public/css/legacy-bridge.css')); ?>?v=20260410-1">
</head>
<body>
    <main class="login-shell">
    <section class="login-container">
        <div class="login-left login-left-art">
            <div class="login-left-overlay"></div>
            <div class="login-left-content">
                <span class="brand-pill">HR Workspace</span>
                <h2>Đăng nhập vào trung tâm vận hành nhân sự</h2>
                <p>Toàn bộ hồ sơ, chấm công, lương và quy trình nội bộ giờ chạy trên cùng một giao diện thống nhất.</p>
                <ul class="login-feature-list">
                    <li><strong>24h</strong><span>Theo dõi trạng thái hệ thống và tác vụ gần đây theo thời gian thực</span></li>
                    <li><strong>HR</strong><span>Truy cập nhanh nhân sự, hợp đồng, tuyển dụng và cấu hình tài khoản</span></li>
                    <li><strong>AI</strong><span>Sẵn sàng dùng chatbot nội bộ và quy trình phê duyệt ngay sau khi đăng nhập</span></li>
                </ul>
            </div>
        </div>

        <div class="login-right">
            <span class="auth-title-badge">Đăng nhập bảo mật</span>
            <h2>Xin chào trở lại</h2>
            <p>Sử dụng tài khoản nội bộ để tiếp tục làm việc trên hệ thống nhân sự mới.</p>

            <?php if($errors->any()): ?>
                <div class="auth-alert auth-alert-error"><?php echo e($errors->first()); ?></div>
            <?php endif; ?>

            <?php if(session('success')): ?>
                <div class="auth-alert auth-alert-success"><?php echo e(session('success')); ?></div>
            <?php endif; ?>

            <form method="post" action="<?php echo e(route('login.submit')); ?>" class="auth-form">
                <?php echo csrf_field(); ?>
                <div>
                    <label for="TenDangNhap">Tên đăng nhập</label>
                    <input id="TenDangNhap" name="TenDangNhap" type="text" value="<?php echo e(old('TenDangNhap')); ?>" required>
                </div>

                <div>
                    <label for="MatKhau">Mật khẩu</label>
                    <input id="MatKhau" name="MatKhau" type="password" required>
                </div>

                <div class="options">
                    <label><input type="checkbox" checked disabled><span>Phiên đăng nhập được bảo vệ</span></label>
                    <a href="<?php echo e(route('password.forgot')); ?>">Quên mật khẩu?</a>
                </div>

                <button type="submit">Đăng nhập</button>
                <span class="auth-meta-note">Nếu đây là lần đầu đăng nhập sau khi được cấp tài khoản, hệ thống có thể yêu cầu đổi mật khẩu để tiếp tục.</span>
            </form>
        </div>
    </section>
    </main>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\du_an2\laravel_app\resources\views/auth/login.blade.php ENDPATH**/ ?>