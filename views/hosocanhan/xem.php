
<?php include 'views/layout/header.php'; ?>
<?php include 'views/layout/sidebar.php'; ?>

<?php
$formatDate = static function ($value) {
    if (empty($value) || $value === '0000-00-00') {
        return '---';
    }

    try {
        return (new DateTime($value))->format('d/m/Y');
    } catch (Exception $e) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
};
?>

<!-- CONTENT -->
<main class="main-content">

    <header class="pv2-header">
        <div>
            <h1>Hồ Sơ Nhân Viên</h1>
            <p>Thông tin chi tiết và trạng thái hồ sơ nhân sự.</p>
        </div>
        <a href="index.php?controller=hosocanhan&action=index" class="btn cancel">Quay lại danh sách</a>
    </header>

    <section class="pv2-shell">
        <aside class="pv2-sidebar-card">
            <?php if (!empty($row['Anh'])): ?>
                <img
                    src="uploads/<?= htmlspecialchars($row['Anh'], ENT_QUOTES, 'UTF-8') ?>"
                    alt="Ảnh nhân viên"
                    class="pv2-avatar"
                >
            <?php else: ?>
                <img
                    src="https://via.placeholder.com/220x220?text=No+Photo"
                    alt="Chưa có ảnh"
                    class="pv2-avatar"
                >
            <?php endif; ?>

            <h2><?= htmlspecialchars($row['HoTen'] ?? 'Chưa cập nhật', ENT_QUOTES, 'UTF-8') ?></h2>
            <p class="pv2-role"><?= htmlspecialchars($row['TenCV'] ?? 'Chưa cập nhật chức vụ', ENT_QUOTES, 'UTF-8') ?></p>

            <div class="pv2-chip-wrap">
                <span class="pv2-chip">Mã NV: <?= htmlspecialchars($row['MaNV'] ?? '---', ENT_QUOTES, 'UTF-8') ?></span>
                <span class="pv2-chip">Mã hồ sơ: #<?= htmlspecialchars($row['MaHoSo'] ?? '---', ENT_QUOTES, 'UTF-8') ?></span>
                <span class="pv2-chip">Phòng ban: <?= htmlspecialchars($row['TenPB'] ?? '---', ENT_QUOTES, 'UTF-8') ?></span>
            </div>

            <div class="pv2-highlight">
                <div>
                    <span>Ngày vào làm</span>
                    <strong><?= $formatDate($row['NgayVaoLam'] ?? '') ?></strong>
                </div>
                <div>
                    <span>Hôn nhân</span>
                    <strong><?= htmlspecialchars($row['TrangThaiHonNhan'] ?? '---', ENT_QUOTES, 'UTF-8') ?></strong>
                </div>
            </div>
        </aside>

        <div class="pv2-content">
            <section class="pv2-panel">
                <h3>Giấy tờ tùy thân</h3>
                <div class="pv2-grid">
                    <div class="pv2-item"><label>CCCD</label><p><?= htmlspecialchars($row['CCCD'] ?? '---', ENT_QUOTES, 'UTF-8') ?></p></div>
                    <div class="pv2-item"><label>Nơi cấp</label><p><?= htmlspecialchars($row['NoiCap'] ?? '---', ENT_QUOTES, 'UTF-8') ?></p></div>
                    <div class="pv2-item"><label>Ngày cấp</label><p><?= $formatDate($row['NgayCap'] ?? '') ?></p></div>
                </div>
            </section>

            <section class="pv2-panel">
                <h3>Thông tin cá nhân</h3>
                <div class="pv2-grid">
                    <div class="pv2-item"><label>Địa chỉ</label><p><?= htmlspecialchars($row['DiaChi'] ?? '---', ENT_QUOTES, 'UTF-8') ?></p></div>
                    <div class="pv2-item"><label>Dân tộc</label><p><?= htmlspecialchars($row['DanToc'] ?? '---', ENT_QUOTES, 'UTF-8') ?></p></div>
                    <div class="pv2-item"><label>Tôn giáo</label><p><?= htmlspecialchars($row['TonGiao'] ?? '---', ENT_QUOTES, 'UTF-8') ?></p></div>
                    <div class="pv2-item"><label>Tình trạng hôn nhân</label><p><?= htmlspecialchars($row['TrangThaiHonNhan'] ?? '---', ENT_QUOTES, 'UTF-8') ?></p></div>
                </div>
            </section>

            <section class="pv2-panel">
                <h3>Thông tin công việc</h3>
                <div class="pv2-grid">
                    <div class="pv2-item"><label>Trình độ</label><p><?= htmlspecialchars($row['TrinhDo'] ?? '---', ENT_QUOTES, 'UTF-8') ?></p></div>
                    <div class="pv2-item"><label>Chuyên môn</label><p><?= htmlspecialchars($row['ChuyenMon'] ?? '---', ENT_QUOTES, 'UTF-8') ?></p></div>
                    <div class="pv2-item"><label>Ngày vào làm</label><p><?= $formatDate($row['NgayVaoLam'] ?? '') ?></p></div>
                    <div class="pv2-item"><label>Phòng ban</label><p><?= htmlspecialchars($row['TenPB'] ?? '---', ENT_QUOTES, 'UTF-8') ?></p></div>
                </div>
            </section>
        </div>
    </section>

</main>
<?php include 'views/layout/footer.php'; ?>
