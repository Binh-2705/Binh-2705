<?php include 'views/layout/header.php'; ?>
<?php include 'views/layout/sidebar.php'; ?>

<!-- MAIN CONTENT -->
<div class="main-content">

<header>
    <h1>📊 Lịch sử thay đổi lương</h1>
</header>

<!-- THÔNG TIN HỢP ĐỒNG -->
<div class="form-nv" style="max-width:100%; margin-bottom:25px;">

    <div class="form-group">
        <label>Số hợp đồng</label>
        <input type="text" value="<?= htmlspecialchars($hopDong['SoHopDong']) ?>" disabled>
    </div>

    <div class="form-group">
        <label>Nhân viên</label>
        <input type="text" value="<?= htmlspecialchars($hopDong['HoTen']) ?>" disabled>
    </div>

    <div class="form-group">
        <label>Bậc hiện tại</label>
        <input type="text" value="<?= htmlspecialchars($hopDong['TenBac']) ?>" disabled>
    </div>

    <div class="form-group">
        <label>Lương hiện tại</label>
        <input type="text"
               value="<?= number_format($hopDong['LuongThucTe'],0,',','.') ?> VNĐ"
               disabled>
    </div>

</div>

<!-- BẢNG LỊCH SỬ -->
<table class="table">
<thead>
<tr>
    <th>Ngày áp dụng</th>
    <th>Lương cũ</th>
    <th>Lương mới</th>
    <th>Chênh lệch</th>
    <th>Lý do</th>
</tr>
</thead>

<tbody>

<?php if (mysqli_num_rows($lichSu) == 0): ?>
<tr>
    <td colspan="5">Chưa có thay đổi lương</td>
</tr>
<?php else: ?>
<?php while ($row = mysqli_fetch_assoc($lichSu)): ?>
<tr>
    <td><?= date('d/m/Y', strtotime($row['NgayApDung'])) ?></td>

    <td style="color:#ef4444;font-weight:600">
        <?= number_format($row['LuongCu'],0,',','.') ?> VNĐ
    </td>

    <td style="color:#16a34a;font-weight:600">
        <?= number_format($row['LuongMoi'],0,',','.') ?> VNĐ
    </td>

    <td>
        <?= number_format($row['LuongMoi'] - $row['LuongCu'],0,',','.') ?> VNĐ
    </td>

    <td><?= htmlspecialchars($row['LyDo']) ?></td>
</tr>
<?php endwhile; ?>
<?php endif; ?>

</tbody>
</table>

<br>
<a href="index.php?controller=hopdong&action=index" class="btn cancel">
⬅ Quay lại danh sách hợp đồng
</a>

</div>
</div>
    <?php include 'views/layout/footer.php'; ?>


