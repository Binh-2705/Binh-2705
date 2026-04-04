<?php include 'views/layout/header.php'; ?>
<?php include 'views/layout/sidebar.php'; ?>

    <!-- MAIN -->
    <main class="main-content">

        <header>
            <h1>🛡️ Quản lý Bảo hiểm</h1>
        </header>

        <!-- ACTION -->
        <div class="actions">
            <div class="btn-group">
                <a href="index.php?controller=baohiem&action=them" class="btn add">
                    ➕ Thêm bảo hiểm
                </a>
            </div>

            <!-- SEARCH -->
            <form method="GET" action="index.php" class="search-form">
                <input type="hidden" name="controller" value="baohiem">
                <input type="hidden" name="action" value="timkiem">
                <input type="text" name="keyword" placeholder="🔍 Nhập tên nhân viên..." class="search-box" required>
                <button type="submit" class="btn search">Tìm</button>
            </form>
        </div>

        <!-- TABLE -->
        <table class="table">
            <thead>
                <tr>
                    <th>Nhân viên</th>
                    <th>Số BHXH</th>
                    <th>Loại</th>
                    <th>Mức đóng</th>
                    <th>Công ty đóng</th>
                    <th>Nhân viên đóng</th>
                    <th>Ngày tham gia</th>
                    <th>Trạng thái</th>
                    <th>Thao tác</th>
                </tr>
            </thead>

            <tbody>
                <?php if ($ds && mysqli_num_rows($ds) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($ds)): ?>
                        <tr>
                            <td><?= $row['HoTen'] ?></td>
                            <td><?= $row['SoBHXH'] ?></td>
                            <td><?= $row['LoaiBaoHiem'] ?></td>

                            <td><?= number_format($row['MucDong']) ?> đ</td>
                            <td><?= number_format($row['CongTyDong']) ?> đ</td>
                            <td><?= number_format($row['NhanVienDong']) ?> đ</td>

                            <td>
                                <?= (!empty($row['NgayThamGia'])) 
                                    ? date('d/m/Y', strtotime($row['NgayThamGia'])) 
                                    : 'Chưa có' ?>
                            </td>

                            <td><?= $row['TrangThai'] ?></td>

                            <td>
                                <div class="table-actions">
                                <!-- SỬA -->
                                <a href="index.php?controller=baohiem&action=sua&id=<?= $row['MaBH'] ?>"
                                   class="btn edit"
                                   title="Chỉnh sửa">✏️</a>

                                <!-- NGỪNG -->
                                <a href="index.php?controller=baohiem&action=dung&id=<?= $row['MaBH'] ?>"
                                   class="btn delete"
                                   title="Ngừng"
                                   onclick="return confirm('Ngừng bảo hiểm này?')">⛔</a>

                                <!-- XÓA -->
                                <a href="index.php?controller=baohiem&action=xoa&id=<?= $row['MaBH'] ?>"
                                   class="btn delete"
                                   title="Xóa"
                                   onclick="return confirm('Xóa bảo hiểm này?')">🗑️</a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9">Không có dữ liệu bảo hiểm</td>
                    </tr>
                <?php endif; ?>
            </tbody>

        </table>

    </main>
<?php include 'views/layout/footer.php'; ?>