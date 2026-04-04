<?php include 'views/layout/header.php'; ?>
<?php include 'views/layout/sidebar.php'; ?>

<div class="main-content">
    <header>
        <h1>📈 Quá trình công tác nhân viên</h1>
    </header>

    <div class="actions">
        <div class="btn-group">
            <a href="javascript:history.back()" class="btn cancel">Quay lại</a>
        </div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Phòng ban</th>
                <th>Chức vụ</th>
                <th>Từ ngày</th>
                <th>Đến ngày</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($lichsu && mysqli_num_rows($lichsu) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($lichsu)): ?>
                <tr>
                    <td><?= htmlspecialchars($row['TenPB']) ?></td>
                    <td><?= htmlspecialchars($row['TenCV']) ?></td>
                    <td><?= date('d/m/Y', strtotime($row['NgayBatDau'])) ?></td>
                    <td>
                        <?= $row['NgayKetThuc'] ? date('d/m/Y', strtotime($row['NgayKetThuc'])) : '<span class="btn edit" style="padding: 2px 8px; font-size: 11px;">Hiện tại</span>' ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4">Chưa có dữ liệu quá trình công tác.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php include 'views/layout/footer.php'; ?>