<?php include 'views/layout/header.php'; ?>
<?php include 'views/layout/sidebar.php'; ?>

<main class="main-content">
  <header>
    <h1 data-i18n="settings.title">Cài đặt hệ thống</h1>
  </header>

  <div class="settings-grid">
    <section class="form-box settings-card">
      <h3 data-i18n="settings.appearance">Giao diện</h3>
      <p class="settings-note" data-i18n="settings.appearance_note">Chọn chế độ sáng/tối cho toàn bộ hệ thống. Tùy chọn được lưu tự động cho các lần truy cập sau.</p>

      <div class="settings-row">
        <strong data-i18n="settings.current_theme">Chế độ hiện tại:</strong>
        <span id="settingsThemeStatus" class="status-badge info" data-i18n="theme.light">Sáng</span>
      </div>

      <div class="settings-row">
        <button type="button" class="btn add" id="settingsThemeToggle" data-i18n="theme.enable_dark">Bật chế độ tối</button>
      </div>
    </section>

    <section class="form-box settings-card">
      <h3 data-i18n="settings.density">Mật độ hiển thị</h3>
      <p class="settings-note" data-i18n="settings.density_note">Điều chỉnh khoảng cách trên bảng dữ liệu ở toàn bộ hệ thống.</p>

      <div class="settings-row settings-control-row">
        <label for="settingsDensity"><strong data-i18n="settings.table_density">Mật độ bảng:</strong></label>
        <select id="settingsDensity">
          <option value="comfortable" data-i18n="density.comfortable">Thoải mái</option>
          <option value="compact" data-i18n="density.compact">Gọn</option>
        </select>
      </div>
    </section>

    <section class="form-box settings-card">
      <h3 data-i18n="settings.notifications">Thông báo</h3>
      <p class="settings-note" data-i18n="settings.notifications_note">Bật/tắt hiển thị thông báo nổi và badge thông báo.</p>

      <div class="settings-row settings-control-row">
        <label for="settingsNotifications"><strong data-i18n="settings.show_notifications">Hiển thị thông báo:</strong></label>
        <select id="settingsNotifications">
          <option value="on" data-i18n="common.on">Bật</option>
          <option value="off" data-i18n="common.off">Tắt</option>
        </select>
      </div>
    </section>

    <section class="form-box settings-card">
      <h3 data-i18n="settings.language">Ngôn ngữ</h3>
      <p class="settings-note" data-i18n="settings.language_note">Lưu tùy chọn ngôn ngữ giao diện để đồng bộ cho các trang. (Bản dịch chi tiết sẽ mở rộng dần)</p>

      <div class="settings-row settings-control-row">
        <label for="settingsLanguage"><strong data-i18n="settings.language_label">Ngôn ngữ:</strong></label>
        <select id="settingsLanguage">
          <option value="vi" data-i18n="language.vi">Tiếng Việt</option>
          <option value="en" data-i18n="language.en">English</option>
        </select>
      </div>
    </section>

    <section class="form-box settings-card settings-security-card">
      <h3>Bảo mật tài khoản</h3>
      <p class="settings-note">Đổi mật khẩu của tài khoản đang đăng nhập để tăng mức an toàn.</p>

      <form method="post" action="?controller=home&action=capNhatTaiKhoan" class="settings-form-stack">
        <div class="settings-control-row">
          <label for="MatKhauHienTai"><strong>Mật khẩu hiện tại:</strong></label>
          <input id="MatKhauHienTai" type="password" name="MatKhauHienTai" required minlength="6" maxlength="100" placeholder="Nhập mật khẩu hiện tại">
        </div>

        <div class="settings-control-row">
          <label for="MatKhauMoi"><strong>Mật khẩu mới:</strong></label>
          <input id="MatKhauMoi" type="password" name="MatKhauMoi" required minlength="8" maxlength="100" placeholder="Tối thiểu 8 ký tự">
        </div>

        <div class="settings-control-row">
          <label for="XacNhanMatKhauMoi"><strong>Xác nhận mật khẩu mới:</strong></label>
          <input id="XacNhanMatKhauMoi" type="password" name="XacNhanMatKhauMoi" required minlength="8" maxlength="100" placeholder="Nhập lại mật khẩu mới">
        </div>

        <div class="settings-row">
          <button type="submit" class="btn add">Cập nhật mật khẩu</button>
        </div>
      </form>
    </section>

    <section class="form-box settings-card settings-security-card">
      <h3>Tên đăng nhập</h3>
      <p class="settings-note">Đổi tên đăng nhập tài khoản hiện tại. Hệ thống yêu cầu nhập lại mật khẩu để xác nhận.</p>

      <div class="settings-row"><strong>Tên đăng nhập hiện tại:</strong> <span><?= htmlspecialchars((string)($sessionInfo['username'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span></div>

      <form method="post" action="?controller=home&action=capNhatTenDangNhap" class="settings-form-stack">
        <div class="settings-control-row">
          <label for="TenDangNhapMoi"><strong>Tên đăng nhập mới:</strong></label>
          <input id="TenDangNhapMoi" type="text" name="TenDangNhapMoi" required minlength="4" maxlength="50" pattern="[A-Za-z0-9_.]{4,50}" placeholder="Ví dụ: nguyenvana">
        </div>

        <div class="settings-control-row">
          <label for="MatKhauXacNhan"><strong>Mật khẩu xác nhận:</strong></label>
          <input id="MatKhauXacNhan" type="password" name="MatKhauXacNhan" required minlength="6" maxlength="100" placeholder="Nhập mật khẩu hiện tại để xác nhận">
        </div>

        <div class="settings-row">
          <button type="submit" class="btn search">Cập nhật tên đăng nhập</button>
        </div>
      </form>
    </section>

    <section class="form-box settings-card">
      <h3>Phiên đăng nhập</h3>
      <p class="settings-note">Theo dõi nhanh thông tin phiên hiện tại và làm mới session khi cần.</p>

      <div class="settings-row"><strong>Tài khoản:</strong> <span><?= htmlspecialchars((string)($sessionInfo['username'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span></div>
      <div class="settings-row"><strong>Mã nhân sự:</strong> <span><?= htmlspecialchars((string)(($sessionInfo['employee_code'] ?? '') !== '' ? $sessionInfo['employee_code'] : 'Chưa gán'), ENT_QUOTES, 'UTF-8') ?></span></div>
      <div class="settings-row"><strong>Vai trò:</strong> <span><?= htmlspecialchars((string)($sessionInfo['role'] ?? 'Không xác định'), ENT_QUOTES, 'UTF-8') ?></span></div>
      <div class="settings-row"><strong>Session ID:</strong> <span class="settings-session-id"><?= htmlspecialchars((string)($sessionInfo['session_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span></div>
      <div class="settings-row"><strong>Session Marker:</strong> <span class="settings-session-id"><?= htmlspecialchars((string)($sessionInfo['session_marker'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span></div>
      <div class="settings-row">
        <strong>Trạng thái mật khẩu:</strong>
        <?php if (!empty($sessionInfo['must_change_password'])): ?>
          <span class="status-badge warning">Đang dùng mật khẩu tạm</span>
        <?php else: ?>
          <span class="status-badge success">Ổn định</span>
        <?php endif; ?>
      </div>

      <form method="post" action="?controller=home&action=lamMoiPhien" class="settings-row">
        <button type="submit" class="btn search">Làm mới phiên</button>
      </form>

      <form method="post" action="?controller=home&action=dangXuatPhienKhac" class="settings-row" onsubmit="return confirm('Đăng xuất tất cả phiên khác của tài khoản này?')">
        <button type="submit" class="btn delete">Đăng xuất phiên khác</button>
      </form>

      <div class="settings-session-list">
        <h4>Thiết bị đăng nhập gần đây</h4>
        <?php if (!empty($recentSessions)): ?>
          <?php foreach ($recentSessions as $s): ?>
            <div class="settings-session-item">
              <div class="settings-session-head">
                <strong><?= htmlspecialchars((string)($s['ip_address'] ?? 'N/A'), ENT_QUOTES, 'UTF-8') ?></strong>
                <?php if (!empty($s['is_current'])): ?>
                  <span class="status-badge info">Phiên hiện tại</span>
                <?php elseif (!empty($s['revoked_at'])): ?>
                  <span class="status-badge danger">Đã đăng xuất</span>
                <?php else: ?>
                  <span class="status-badge success">Đang hoạt động</span>
                <?php endif; ?>
              </div>
              <div class="settings-session-meta">UA: <?= htmlspecialchars((string)($s['user_agent'] ?? 'Không rõ'), ENT_QUOTES, 'UTF-8') ?></div>
              <div class="settings-session-meta">Tạo: <?= htmlspecialchars((string)($s['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?> | Hoạt động gần nhất: <?= htmlspecialchars((string)($s['last_activity'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p class="settings-note">Chưa có dữ liệu phiên đăng nhập.</p>
        <?php endif; ?>
      </div>
    </section>

    <?php if (!empty($isAdmin)): ?>
    <section class="form-box settings-card">
      <h3>Công cụ nhanh cho Admin</h3>
      <p class="settings-note">Truy cập nhanh các chức năng vận hành và giám sát hệ thống.</p>

      <div class="settings-admin-links">
        <a class="btn search" href="?controller=systemhealth">Sức khỏe hệ thống</a>
        <a class="btn add" href="?controller=auditlog">Nhật ký hệ thống</a>
        <a class="btn edit" href="?controller=phanquyen">Phân quyền</a>
      </div>
    </section>
    <?php endif; ?>
  </div>
</main>

<?php include 'views/layout/footer.php'; ?>
