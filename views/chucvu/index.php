<?php include 'views/layout/header.php'; ?>
<?php include 'views/layout/sidebar.php'; ?>
<main class="main-content">
<header>
    <h1>🙍‍♂️ Quản lý Chức vụ</h1>
</header>

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
    <div class="btn-group">
        <a href="index.php?controller=chucvu&action=add" class="btn add">➕ Thêm chức vụ</a>
        <a href="index.php?controller=chucvu&action=exportExcel" class="btn export">⬇️ Xuất Excel</a>
    </div>

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
    <th>Thao tác</th>
</tr>
</thead>

<tbody>
<?php if (empty($danhSachChucVu)) { ?>
    <tr>
        <td colspan="5" style="text-align:center;">Không có dữ liệu chức vụ.</td>
    </tr>
<?php } else { ?>
    <?php foreach ($danhSachChucVu as $cv) { ?>
        <tr>
            <td><?php echo $cv['MaCV']; ?></td>
            <td><?php echo htmlspecialchars($cv['TenCV']); ?></td>
            <td><?php echo $cv['HeSoChucVu']; ?></td>
            <td><?php echo number_format($cv['PhuCap'], 0, ',', '.'); ?> đ</td>
            <td>
                <div class="table-actions">
                    <a href="index.php?controller=chucvu&action=edit&id=<?php echo $cv['MaCV']; ?>" class="btn edit" title="Chỉnh sửa">✏️</a>
                    <a href="index.php?controller=chucvu&action=delete&id=<?php echo $cv['MaCV']; ?>"
                       class="btn delete"
                       title="Xóa"
                       onclick="return confirm('Xác nhận xóa chức vụ mã <?php echo $cv['MaCV']; ?>?');">🗑️</a>
                </div>
            </td>
        </tr>
    <?php } ?>
<?php } ?>
</tbody>
</table>

</main>
<?php include 'views/layout/footer.php'; ?>
