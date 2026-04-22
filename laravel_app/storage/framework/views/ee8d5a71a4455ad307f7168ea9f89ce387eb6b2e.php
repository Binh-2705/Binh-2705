<?php $title = 'Cài đặt tài khoản' ?>
<?php $subtitle = 'Cập nhật tên đăng nhập, mật khẩu và quản lý phiên đăng nhập' ?>


<?php $__env->startSection('content'); ?>
    <section class="panel">
        <div class="settings-grid">
            <article class="detail-card settings-card">
                <span class="eyebrow">Tên đăng nhập</span>
                <strong><?php echo e($account['TenDangNhap']); ?></strong>
            </article>
            <article class="detail-card settings-card">
                <span class="eyebrow">Vai trò</span>
                <strong><?php echo e($account['VaiTro'] ?? 'NhanVien'); ?></strong>
            </article>
            <article class="detail-card settings-card">
                <span class="eyebrow">Mã nhân viên</span>
                <strong><?php echo e($account['MaNV'] ?: 'Chưa gán'); ?></strong>
            </article>
        </div>
    </section>

    <div class="settings-grid">
    <section class="panel settings-card">
        <h2 class="no-top-margin">Đổi tên đăng nhập</h2>
        <p class="settings-note">Đổi tên đăng nhập và xác nhận bằng mật khẩu hiện tại để giữ nguyên cơ chế bảo mật như hệ thống cũ.</p>
        <form method="post" action="<?php echo e(route('settings.username')); ?>" class="settings-form-stack">
            <?php echo csrf_field(); ?>
            <div class="field-grid">
                <div class="settings-control-row">
                    <label for="TenDangNhapMoi">Tên đăng nhập mới</label>
                    <input id="TenDangNhapMoi" name="TenDangNhapMoi" value="<?php echo e(old('TenDangNhapMoi')); ?>" required>
                </div>
                <div class="settings-control-row">
                    <label for="MatKhauXacNhan">Mật khẩu xác nhận</label>
                    <input id="MatKhauXacNhan" name="MatKhauXacNhan" type="password" required>
                </div>
            </div>
            <div class="form-actions-bar"><button class="btn" type="submit">Cập nhật tên đăng nhập</button></div>
        </form>
    </section>

    <section class="panel settings-card">
        <h2 class="no-top-margin">Đổi mật khẩu</h2>
        <p class="settings-note">Mật khẩu mới được áp dụng ngay cho phiên hiện tại và các phiên khác có thể bị thu hồi nếu cần.</p>
        <form method="post" action="<?php echo e(route('settings.password')); ?>" class="settings-form-stack">
            <?php echo csrf_field(); ?>
            <div class="field-grid">
                <div class="settings-control-row">
                    <label for="MatKhauHienTai">Mật khẩu hiện tại</label>
                    <input id="MatKhauHienTai" name="MatKhauHienTai" type="password" required>
                </div>
                <div class="settings-control-row">
                    <label for="MatKhauMoi">Mật khẩu mới</label>
                    <input id="MatKhauMoi" name="MatKhauMoi" type="password" required>
                </div>
                <div class="settings-control-row">
                    <label for="XacNhanMatKhauMoi">Xác nhận mật khẩu mới</label>
                    <input id="XacNhanMatKhauMoi" name="XacNhanMatKhauMoi" type="password" required>
                </div>
            </div>
            <div class="form-actions-bar"><button class="btn" type="submit">Cập nhật mật khẩu</button></div>
        </form>
    </section>
    </div>

    <section class="panel settings-card">
        <h2 class="no-top-margin">Quản lý phiên đăng nhập</h2>
        <p class="settings-note">Theo dõi các phiên đăng nhập hoạt động và thu hồi phiên khác ngay trong hệ thống mà không cần quay lại runtime cũ.</p>
        <div class="settings-row"><strong>Session hiện tại:</strong> <span class="settings-session-id"><?php echo e($sessionInfo['session_marker']); ?></span></div>
        <div class="split-actions top-gap-lg">
            <form method="post" action="<?php echo e(route('settings.refresh-session')); ?>" class="inline-form"><?php echo csrf_field(); ?><button class="btn btn-secondary" type="submit">Làm mới phiên hiện tại</button></form>
            <form method="post" action="<?php echo e(route('settings.revoke-other-sessions')); ?>" class="inline-form"><?php echo csrf_field(); ?><button class="btn btn-danger" type="submit">Đăng xuất các phiên khác</button></form>
        </div>
        <div class="table-shell">
            <table class="data-table table-compact">
                <thead>
                    <tr>
                        <th>Session</th>
                        <th>IP</th>
                        <th>Lần hoạt động cuối</th>
                        <th>Trạng thái</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $recentSessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $session): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($session['session_marker']); ?><?php if($session['is_current']): ?> <strong>(hiện tại)</strong><?php endif; ?></td>
                            <td><?php echo e($session['ip_address'] ?: 'không rõ'); ?></td>
                            <td><?php echo e($session['last_activity']); ?></td>
                            <td>
                                <span class="status-badge <?php echo e($session['revoked_at'] ? 'danger' : ($session['is_current'] ? 'info' : 'success')); ?>">
                                    <?php echo e($session['revoked_at'] ? 'Đã thu hồi' : ($session['is_current'] ? 'Hiện tại' : 'Đang hoạt động')); ?>

                                </span>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    </section>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\du_an2\laravel_app\resources\views/account/settings.blade.php ENDPATH**/ ?>