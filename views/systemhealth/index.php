<?php include 'views/layout/header.php'; ?>
<?php include 'views/layout/sidebar.php'; ?>

<div class="main-content">
    <header>
        <h1>SỨC KHỎE HỆ THỐNG</h1>
    </header>

    <div class="health-actions">
        <form method="post" action="?controller=systemhealth&action=runChecks" onsubmit="return confirm('Chạy health check ngay bây giờ?')">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($_SESSION['_csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            <button class="btn search" type="submit">▶ Chạy Health Check</button>
        </form>
    </div>

    <?php if (!empty($healthCheckReport)): ?>
    <section class="health-report-card">
        <div class="health-report-head">
            <h3>Kết quả lần chạy gần nhất</h3>
            <span class="status-badge <?= ((int)$healthCheckReport['failed'] > 0) ? 'danger' : 'success' ?>">
                <?= (int)$healthCheckReport['passed'] ?>/<?= (int)$healthCheckReport['total'] ?> đạt
            </span>
        </div>
        <p class="health-meta">
            Thời điểm: <?= htmlspecialchars((string)$healthCheckReport['executed_at'], ENT_QUOTES, 'UTF-8') ?> |
            Thời gian chạy: <?= (int)$healthCheckReport['duration_ms'] ?> ms
        </p>
        <table class="table">
            <thead>
                <tr>
                    <th>Check</th>
                    <th>Trạng thái</th>
                    <th>Chi tiết</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (($healthCheckReport['checks'] ?? []) as $item): ?>
                <tr>
                    <td><?= htmlspecialchars((string)($item['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                    <td>
                        <?php if (!empty($item['ok'])): ?>
                            <span class="status-badge success">PASS</span>
                        <?php else: ?>
                            <span class="status-badge danger">FAIL</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars((string)($item['detail'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>
    <?php endif; ?>

    <div class="stat-row">
        <div class="stat-box stat-info">
            <h3>Tổng migration</h3>
            <p><?= (int)($migrationStatus['available_count'] ?? 0) ?></p>
        </div>
        <div class="stat-box stat-success">
            <h3>Đã áp dụng</h3>
            <p><?= (int)($migrationStatus['applied_count'] ?? 0) ?></p>
        </div>
        <div class="stat-box <?= ((int)($migrationStatus['pending_count'] ?? 0) > 0) ? 'stat-warning' : 'stat-success' ?>">
            <h3>Chờ áp dụng</h3>
            <p><?= (int)($migrationStatus['pending_count'] ?? 0) ?></p>
        </div>
    </div>

    <div class="card" style="margin-bottom:16px; text-align:left;">
        <h3 style="margin-bottom:10px;">Kiểm tra schema auth</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>Thành phần</th>
                    <th>Trạng thái</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($schemaChecks as $name => $ok): ?>
                <tr>
                    <td><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></td>
                    <td>
                        <?php if ($ok): ?>
                            <span class="status-badge success">OK</span>
                        <?php else: ?>
                            <span class="status-badge danger">Thiếu</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="card" style="margin-bottom:16px; text-align:left;">
        <h3 style="margin-bottom:10px;">Chỉ số auth</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>Chỉ số</th>
                    <th>Giá trị</th>
                </tr>
            </thead>
            <tbody>
                <tr><td>Tổng tài khoản</td><td><?= (int)($authStats['accounts_total'] ?? 0) ?></td></tr>
                <tr><td>Tài khoản đang dùng mật khẩu tạm</td><td><?= (int)($authStats['accounts_temporary_password'] ?? 0) ?></td></tr>
                <tr><td>Tài khoản đã có MaNVRef</td><td><?= (int)($authStats['accounts_with_manvref'] ?? 0) ?></td></tr>
                <tr><td>Tài khoản chưa có MaNVRef</td><td><?= (int)($authStats['accounts_without_manvref'] ?? 0) ?></td></tr>
                <tr><td>Token reset còn hiệu lực</td><td><?= (int)($authStats['reset_tokens_active'] ?? 0) ?></td></tr>
            </tbody>
        </table>
    </div>

    <div class="card" style="margin-bottom:16px; text-align:left;">
        <h3 style="margin-bottom:10px;">Migration đang chờ</h3>
        <?php if (!empty($migrationStatus['pending_files'])): ?>
            <ul style="margin-left:18px; line-height:1.8;">
                <?php foreach ($migrationStatus['pending_files'] as $file): ?>
                    <li><?= htmlspecialchars($file, ENT_QUOTES, 'UTF-8') ?></li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>Không có migration chờ. Hệ thống đang đồng bộ.</p>
        <?php endif; ?>
    </div>

    <div class="card" style="text-align:left;">
        <h3 style="margin-bottom:10px;">Lỗi gần đây từ app.log</h3>
        <?php if (!empty($lastErrors)): ?>
            <div style="max-height:280px; overflow:auto; background:#0f172a; color:#e2e8f0; padding:12px; border-radius:8px;">
                <?php foreach ($lastErrors as $line): ?>
                    <div style="font-family:Consolas,monospace; font-size:13px; margin-bottom:6px;">
                        <?= htmlspecialchars($line, ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>Không có dòng lỗi nào trong log gần đây.</p>
        <?php endif; ?>
    </div>
</div>

<?php include 'views/layout/footer.php'; ?>
