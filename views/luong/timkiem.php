<?php include 'views/layout/header.php'; ?>
<?php include 'views/layout/sidebar.php'; ?>
    <main class="main-content">
        <header><h1>🔍 Tìm kiếm Lương Nhân Viên</h1></header>

        <div class="actions">
            <a href="index.php?controller=luong&action=index" class="btn add">↩️ Quay lại danh sách</a>
            <form method="GET" action="index.php" style="display:inline;">
                <input type="hidden" name="controller" value="luong">
                <input type="hidden" name="action" value="timkiem">
                <input type="text" name="keyword" placeholder="Nhập Mã NV, Họ tên hoặc Tháng..." class="search-box" 
                       value="<?= htmlspecialchars($_GET['keyword'] ?? '') ?>" required>
                <button type="submit" class="btn search">Tìm</button>
            </form>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>Mã lương</th>
                    <th>Mã NV</th>
                    <th>Họ tên</th>
                    <th>Tháng</th>
                    <th>Lương cơ bản</th>
                    <th>Phụ cấp</th>
                    <th>Thưởng</th>
                    <th>Khấu trừ</th>
                    <th>Tổng lương</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!empty($luong)): ?>
                <?php foreach ($luong as $row): ?>
                    <?php $tong = $row['LuongCB'] + $row['PhuCap'] + $row['Thuong'] - $row['KhauTru']; ?>
                    <tr>
                        <td><?= $row['MaLuong'] ?></td>
                        <td><?= $row['MaNV'] ?></td>
                        <td><?= $row['HoTen'] ?></td>
                        <td><?= $row['Thang'] ?></td>
                        <td><?= number_format($row['LuongCB'],0,',','.') ?></td>
                        <td><?= number_format($row['PhuCap'],0,',','.') ?></td>
                        <td><?= number_format($row['Thuong'],0,',','.') ?></td>
                        <td><?= number_format($row['KhauTru'],0,',','.') ?></td>
                        <td><b><?= number_format($tong,0,',','.') ?></b></td>
                        <td>
                            <a href="index.php?controller=luong&action=sua&maluong=<?= $row['MaLuong'] ?>" class="btn edit">✏️ Sửa</a>
                            <a href="index.php?controller=luong&action=delete&maluong=<?= $row['MaLuong'] ?>" 
                               class="btn delete" onclick="return confirm('Xóa bản lương này?');">🗑️ Xóa</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="10">Không tìm thấy kết quả nào.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </main>
<?php include 'views/layout/footer.php'; ?>