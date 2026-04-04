<?php include 'views/layout/header.php'; ?>
<?php include 'views/layout/sidebar.php'; ?>
    <!-- MAIN CONTENT -->
    <main class="main-content">

        <header>
            <h1>📚 Quản lý Đào tạo</h1>
        </header>

        <div class="actions">
            <div class="btn-group">
                <a href="index.php?controller=daotao&action=themKhoa" class="btn add">
                    ➕ Thêm khóa đào tạo
                </a>
            </div>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>Mã</th>
                    <th>Tên khóa</th>
                    <th>Từ ngày</th>
                    <th>Đến ngày</th>
                    <th>Đơn vị tổ chức</th>
                    <th>Trạng thái</th>
                    <th>Thao tác</th>
                </tr>
            </thead>

            <tbody>
            <?php if ($khoa && mysqli_num_rows($khoa) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($khoa)): ?>
                    <tr>
                        <td><?= $row['MaKDT'] ?></td>
                        <td><?= $row['TenKhoaDaoTao'] ?></td>
                        <td><?= date('d/m/Y', strtotime($row['TuNgay'])) ?></td>
                        <td><?= date('d/m/Y', strtotime($row['DenNgay'])) ?></td>
                        <td><?= $row['DonViToChuc'] ?></td>
                        <td><?= $row['TrangThaiTuDong'] ?></td>
                        <td>
                            <div class="table-actions">
                            <a href="index.php?controller=daotao&action=thamGia&id=<?= $row['MaKDT'] ?>"
                               class="btn edit"
                               title="Xem thành viên">👥</a>

                            <a href="index.php?controller=daotao&action=xoaKhoa&id=<?= $row['MaKDT'] ?>"
                               class="btn delete"
                               title="Xóa"
                               onclick="return confirm('Bạn có chắc muốn xóa khóa đào tạo này?')">🗑️</a>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7">Không có khóa đào tạo</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>

    </main>
<?php include 'views/layout/footer.php'; ?>