<?php include 'views/layout/header.php'; ?>
<?php include 'views/layout/sidebar.php'; ?>
<?php
$flashSuccess = $_SESSION['success'] ?? '';
$flashError = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);
$roleName = strtolower(trim((string)($_SESSION['taikhoan']['VaiTro'] ?? 'nhanvien')));
$isAdminOrManager = in_array($roleName, ['admin', 'quanly'], true);
$hasExistingProfile = !empty($hoso);
$requiresApprovalRequest = $hasExistingProfile && !$isAdminOrManager;
?>

<main class="main-content">
    <section class="hoso-page hoso-quick-page">
        <div class="hoso-hero">
            <div>
                <p class="hoso-kicker">Ho so ca nhan</p>
                <h1>Nhap nhanh thong tin ho so</h1>
                <p class="hoso-subtext">Muc tieu la nhap nhanh, dung quy trinh va de duyet khi can sua.</p>
            </div>
            <div class="hoso-status-card">
                <span class="hoso-status-title">Trang thai xu ly</span>
                <?php if (empty($hoso)): ?>
                    <span class="hoso-badge hoso-badge-ok">Luu truc tiep</span>
                    <p>Day la lan nhap dau tien nen du lieu duoc cap nhat ngay.</p>
                <?php elseif ($isAdminOrManager): ?>
                    <span class="hoso-badge hoso-badge-admin">Quyen quan tri</span>
                    <p>Admin/Quan ly duoc cap nhat truc tiep khong can cho duyet.</p>
                <?php else: ?>
                    <span class="hoso-badge hoso-badge-pending">Can duyet</span>
                    <p>Ho so da ton tai. Ban gui yeu cau, Admin/Quan ly se duyet.</p>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($flashSuccess !== ''): ?>
            <p class="auth-alert auth-alert-success"><?= htmlspecialchars($flashSuccess, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>

        <?php if ($flashError !== ''): ?>
            <p class="auth-alert auth-alert-error"><?= htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>

        <div class="hoso-rule-box">
            <strong>Quy tac:</strong>
            <?php if (empty($hoso)): ?>
                Lan dau nhap thong tin se duoc cap nhat ngay.
            <?php elseif ($isAdminOrManager): ?>
                Ban la Admin/Quan ly nen duoc cap nhat truc tiep.
            <?php else: ?>
                Ho so da ton tai. Moi thay doi se tao yeu cau cho Admin/Quan ly duyet.
            <?php endif; ?>
        </div>

        <form method="POST" action="index.php?controller=hosocanhan&action=luunhapnhanh" class="hoso-form-card">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($_SESSION['_csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">

            <div class="form-grid hoso-form-grid">
                <div class="form-group">
                    <label>Ma nhan vien</label>
                    <input type="text" value="<?= htmlspecialchars((string)($hoso['MaNV'] ?? $nvInfo['MaNV'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" readonly>
                </div>

                <div class="form-group">
                    <label>Ho ten</label>
                    <input type="text" value="<?= htmlspecialchars((string)($hoso['HoTen'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" readonly>
                </div>

                <div class="form-group">
                    <label>Phong ban</label>
                    <input type="text" value="<?= htmlspecialchars((string)($hoso['TenPB'] ?? $nvInfo['TenPB'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" readonly>
                </div>

                <div class="form-group">
                    <label>Chuc vu</label>
                    <input type="text" value="<?= htmlspecialchars((string)($hoso['TenCV'] ?? $nvInfo['TenCV'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" readonly>
                </div>

                <div class="form-group">
                    <label>CCCD (12 so)</label>
                    <input type="text" name="CCCD" maxlength="12" required value="<?= htmlspecialchars((string)($hoso['CCCD'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                </div>

                <div class="form-group">
                    <label>Ngay cap</label>
                    <input type="date" name="NgayCap" value="<?= htmlspecialchars((string)($hoso['NgayCap'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                </div>

                <div class="form-group">
                    <label>Noi cap</label>
                    <input type="text" name="NoiCap" value="<?= htmlspecialchars((string)($hoso['NoiCap'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                </div>

                <div class="form-group full-width">
                    <label>Dia chi</label>
                    <textarea name="DiaChi" rows="2"><?= htmlspecialchars((string)($hoso['DiaChi'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
                </div>

                <div class="form-group">
                    <label>Dan toc</label>
                    <input type="text" name="DanToc" value="<?= htmlspecialchars((string)($hoso['DanToc'] ?? 'Kinh'), ENT_QUOTES, 'UTF-8'); ?>">
                </div>

                <div class="form-group">
                    <label>Ton giao</label>
                    <input type="text" name="TonGiao" value="<?= htmlspecialchars((string)($hoso['TonGiao'] ?? 'Khong'), ENT_QUOTES, 'UTF-8'); ?>">
                </div>

                <div class="form-group">
                    <label>Trinh do</label>
                    <input type="text" name="TrinhDo" value="<?= htmlspecialchars((string)($hoso['TrinhDo'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                </div>

                <div class="form-group">
                    <label>Chuyen mon</label>
                    <input type="text" name="ChuyenMon" value="<?= htmlspecialchars((string)($hoso['ChuyenMon'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                </div>

                <div class="form-group">
                    <label>Trang thai hon nhan</label>
                    <select name="TrangThaiHonNhan">
                        <option value="Độc thân" <?= (($hoso['TrangThaiHonNhan'] ?? '') === 'Độc thân') ? 'selected' : ''; ?>>Doc than</option>
                        <option value="Đã kết hôn" <?= (($hoso['TrangThaiHonNhan'] ?? '') === 'Đã kết hôn') ? 'selected' : ''; ?>>Da ket hon</option>
                    </select>
                </div>

                <?php if ($requiresApprovalRequest): ?>
                <div class="form-group full-width">
                    <label>Yeu cau sua (gui cho Admin/Quan ly)</label>
                    <textarea name="YeuCauGhiChu" rows="2" placeholder="Mo ta noi dung can sua de duoc duyet nhanh..." required></textarea>
                </div>
                <?php endif; ?>
            </div>

            <div class="form-actions hoso-form-actions">
                <button type="submit" class="btn add"><?= $requiresApprovalRequest ? 'Gui yeu cau sua' : 'Luu thong tin'; ?></button>
                <?php if ($isAdminOrManager): ?>
                    <a href="index.php?controller=hosocanhan&action=duyetyeucau" class="btn search">Duyet yeu cau sua</a>
                <?php endif; ?>
            </div>
        </form>
    </section>
</main>

<?php include 'views/layout/footer.php'; ?>
