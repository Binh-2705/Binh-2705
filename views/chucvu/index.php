<?php include 'views/layout/header.php'; ?>
<?php include 'views/layout/sidebar.php'; ?>
<main class="main-content">
<header>
    <h1>🙍‍♂️ Quản lý Chức vụ</h1>
</header>

<?php
$quyen = $quyen ?? [];
$canThemChucVu = in_array('them_chucvu', $quyen, true);
$canXuatExcelChucVu = in_array('xuat_excel_chucvu', $quyen, true);
$canSuaChucVu = in_array('sua_chucvu', $quyen, true);
$canXoaChucVu = in_array('xoa_chucvu', $quyen, true);
$showActionColumn = $canSuaChucVu || $canXoaChucVu;
?>

<?php
$keyword = isset($keyword) ? $keyword : '';
$danhSachChucVu = isset($danhSachChucVu) ? $danhSachChucVu : [];

if (isset($_GET['msg'])) {
    echo '<p style="color: green; font-weight: bold; margin-bottom: 15px;">'
        . htmlspecialchars($_GET['msg']) .
        '</p>';
}
?>

<div class="actions">
    <?php if ($canThemChucVu || $canXuatExcelChucVu): ?>
    <div class="btn-group">
        <?php if ($canThemChucVu): ?>
        <a href="index.php?controller=chucvu&action=add" class="btn add">➕ Thêm chức vụ</a>
        <?php endif; ?>
        <?php if ($canXuatExcelChucVu): ?>
        <a href="index.php?controller=chucvu&action=exportExcel" class="btn export">⬇️ Xuất Excel</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <form action="index.php" method="GET" style="display:flex; gap:10px;">
        <input type="hidden" name="controller" value="chucvu">
        <input type="hidden" name="action" value="index">
        <input type="text"
               name="search"
               class="search-box"
               placeholder="🔍 Tìm theo mã / tên chức vụ..."
               value="<?php echo htmlspecialchars($keyword); ?>">
        <button type="submit" class="btn search">Tìm</button>
    </form>
</div>

<table class="table">
<thead>
<tr>
    <th>Mã CV</th>
    <th>Tên chức vụ</th>
    <th>Hệ số</th>
    <th>Phụ cấp</th>
    <?php if ($showActionColumn): ?>
    <th>Thao tác</th>
    <?php endif; ?>
</tr>
</thead>

<tbody>
<?php if (empty($danhSachChucVu)) { ?>
    <tr>
        <td colspan="<?= $showActionColumn ? 5 : 4 ?>" style="text-align:center;">Không có dữ liệu chức vụ.</td>
    </tr>
<?php } else { ?>
    <?php foreach ($danhSachChucVu as $cv) { ?>
        <tr>
            <td><?php echo $cv['MaCV']; ?></td>
            <td><?php echo htmlspecialchars($cv['TenCV']); ?></td>
            <td><?php echo $cv['HeSoChucVu']; ?></td>
            <td><?php echo number_format($cv['PhuCap'], 0, ',', '.'); ?> đ</td>
            <?php if ($showActionColumn): ?>
            <td>
                <div class="table-actions">
                    <?php if ($canSuaChucVu): ?>
                    <a href="index.php?controller=chucvu&action=edit&id=<?php echo $cv['MaCV']; ?>" class="btn edit" title="Chỉnh sửa">✏️</a>
                    <?php endif; ?>
                    <?php if ($canXoaChucVu): ?>
                    <a href="index.php?controller=chucvu&action=delete&id=<?php echo $cv['MaCV']; ?>"
                       class="btn delete"
                       title="Xóa"
                       onclick="return confirm('Xác nhận xóa chức vụ mã <?php echo $cv['MaCV']; ?>?');">🗑️</a>
                    <?php endif; ?>
                </div>
            </td>
            <?php endif; ?>
        </tr>
    <?php } ?>
<?php } ?>
</tbody>
</table>

</main>
<?php include 'views/layout/footer.php'; ?>
