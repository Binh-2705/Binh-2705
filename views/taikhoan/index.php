<?php include 'views/layout/header.php'; ?>
<?php include 'views/layout/sidebar.php'; ?>

    <!-- MAIN -->
    <div class="main-content">
        <header>
            <h1>QUẢN LÝ TÀI KHOẢN</h1>
        </header>

        <?php if (!empty($tempPasswordNotice)): ?>
        <section class="security-notice-panel">
            <div class="security-notice-header">
                <div>
                    <h2>Mật khẩu tạm vừa được cấp</h2>
                    <p>Chỉ hiển thị một lần. Hãy chuyển cho người dùng qua kênh nội bộ an toàn và yêu cầu đổi ngay sau khi đăng nhập.</p>
                </div>
                <span class="status-badge warning">Bắt buộc đổi mật khẩu</span>
            </div>

            <div class="security-notice-grid">
                <div>
                    <span class="security-notice-label">Tài khoản</span>
                    <strong><?= htmlspecialchars($tempPasswordNotice['username'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong>
                </div>
                <div>
                    <span class="security-notice-label">Mã nhân sự</span>
                    <strong><?= htmlspecialchars(($tempPasswordNotice['employee_code'] ?? '') !== '' ? $tempPasswordNotice['employee_code'] : 'Chưa gán', ENT_QUOTES, 'UTF-8') ?></strong>
                </div>
                <div>
                    <span class="security-notice-label">Mật khẩu tạm</span>
                    <code class="security-secret"><?= htmlspecialchars($tempPasswordNotice['password'] ?? '', ENT_QUOTES, 'UTF-8') ?></code>
                </div>
                <div>
                    <span class="security-notice-label">Thời điểm cấp</span>
                    <strong><?= htmlspecialchars($tempPasswordNotice['issued_at'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <div class="actions">
            <form>
                <input type="hidden" name="controller" value="taikhoan">
                <input class="search-box" name="key" placeholder="Tìm tài khoản">
                <button class="btn search">Tìm</button>
            </form>

            <a href="?controller=taikhoan&action=them" class="btn add">➕ Thêm</a>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên đăng nhập</th>
                    <th>Vai trò</th>
                    <th>Mã NV</th>
                    <th>Bảo mật</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php while($r = mysqli_fetch_assoc($data)): ?>
                <tr>
                    <td><?= $r['MaTK'] ?></td>
                    <td><?= $r['TenDangNhap'] ?></td>
                    <td><?= $r['VaiTro'] ?></td>
                    <td><?= $r['MaNV'] ?></td>
                    <td>
                        <?php if (!empty($r['BuocDoiMatKhau'])): ?>
                            <span class="status-badge warning">Đang dùng mật khẩu tạm</span>
                        <?php else: ?>
                            <span class="status-badge success">Ổn định</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="table-actions">
                        <a class="btn edit" href="?controller=taikhoan&action=sua&id=<?= $r['MaTK'] ?>" title="Chỉnh sửa">✏️</a>
                                <form method="post" action="?controller=taikhoan&action=resetTamThoi&id=<?= $r['MaTK'] ?>" class="inline-action-form" onsubmit="return confirm('Cấp lại mật khẩu tạm cho tài khoản này?')">
                                    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($_SESSION['_csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                    <button class="btn search" type="submit" title="Cấp mật khẩu tạm">🔐</button>
                                </form>
                        <a class="btn delete" 
                           title="Xóa"
                           onclick="return confirm('Xóa tài khoản?')"
                           href="?controller=taikhoan&action=xoa&id=<?= $r['MaTK'] ?>">🗑️</a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

    </div>
</div>

<?php include 'views/layout/footer.php'; ?>