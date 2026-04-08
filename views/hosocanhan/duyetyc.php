<?php include 'views/layout/header.php'; ?>
<?php include 'views/layout/sidebar.php'; ?>
<?php
$flashSuccess = $_SESSION['success'] ?? '';
$flashError = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);
?>

<main class="main-content">
    <section class="hoso-page hoso-review-page">
        <div class="hoso-hero hoso-hero-review">
            <div>
                <p class="hoso-kicker">Kiem duyet</p>
                <h1>Duyet yeu cau sua ho so</h1>
                <p class="hoso-subtext">Kiem tra thong tin nhan vien de nghi cap nhat va ra quyet dinh nhanh.</p>
            </div>
            <div class="hoso-status-card">
                <span class="hoso-status-title">Hang doi</span>
                <span class="hoso-badge hoso-badge-pending">Pending review</span>
                <p>Moi phieu can ghi chu ly do khi duyet hoac tu choi de truy vet sau nay.</p>
            </div>
        </div>

        <?php if ($flashSuccess !== ''): ?>
            <p class="auth-alert auth-alert-success\"><?= htmlspecialchars($flashSuccess, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>

        <?php if ($flashError !== ''): ?>
            <p class="auth-alert auth-alert-error\"><?= htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>

        <div class="hoso-table-wrap">
            <table class="table hoso-review-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nhan vien</th>
                        <th>Thong tin de nghi</th>
                        <th>Ghi chu</th>
                        <th>Xu ly</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($requests && mysqli_num_rows($requests) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($requests)): ?>
                            <?php $payload = json_decode((string)($row['payload_json'] ?? '{}'), true); ?>
                            <?php if (!is_array($payload)) { $payload = []; } ?>
                            <tr>
                                <td>
                                    <span class="hoso-id-chip">#<?= (int)$row['id']; ?></span>
                                </td>
                                <td class="hoso-employee-cell">
                                    <strong><?= htmlspecialchars((string)($row['HoTen'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></strong><br>
                                    MaNV: <?= (int)($row['MaNV'] ?? 0); ?><br>
                                    Dien thoai: <?= htmlspecialchars((string)($row['DienThoai'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                                </td>
                                <td class="hoso-request-cell">
                                    <div><span>CCCD:</span> <?= htmlspecialchars((string)($payload['CCCD'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div><span>Dia chi:</span> <?= htmlspecialchars((string)($payload['DiaChi'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div><span>Trinh do:</span> <?= htmlspecialchars((string)($payload['TrinhDo'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div><span>Chuyen mon:</span> <?= htmlspecialchars((string)($payload['ChuyenMon'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
                                </td>
                                <td><?= htmlspecialchars((string)($row['note'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <form method="POST" action="index.php?controller=hosocanhan&action=xulyyeucau" class="hoso-review-form">
                                        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($_SESSION['_csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                        <input type="hidden" name="request_id" value="<?= (int)$row['id']; ?>">
                                        <textarea name="review_note" rows="2" placeholder="Ghi chu khi duyet/tu choi"></textarea>
                                        <div class="hoso-review-actions">
                                            <button type="submit" name="decision" value="approve" class="btn add">Duyet</button>
                                            <button type="submit" name="decision" value="reject" class="btn delete">Tu choi</button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align:center;">Khong co yeu cau dang cho duyet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

<?php include 'views/layout/footer.php'; ?>
