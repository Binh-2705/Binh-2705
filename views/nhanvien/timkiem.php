<?php include 'views/layout/header.php'; ?>
<?php include 'views/layout/sidebar.php'; ?>
    <!-- MAIN -->
    <main class="main-content">
        <header>
            <h1>🔍 Kết quả tìm kiếm</h1>
            <p>
                Từ khóa:
                <strong>"<?= htmlspecialchars($keyword) ?>"</strong>
            </p>
        </header>

        <div class="actions">
            <a href="index.php?controller=nhanvien&action=index" class="btn cancel">↩️ Quay lại</a>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>Mã NV</th>
                    <th>Họ tên</th>
                    <th>Giới tính</th>
                    <th>Ngày sinh</th>
                    <th>Email</th>
                    <th>Điện thoại</th>
                    <th>Bậc lương</th>
                    <th>Trạng thái</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($nhanviens && mysqli_num_rows($nhanviens) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($nhanviens)): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['MaNV']) ?></td>
                        <td><?= htmlspecialchars($row['HoTen']) ?></td>
                        <td><?= htmlspecialchars($row['GioiTinh']) ?></td>
                        <td><?= date('d/m/Y', strtotime($row['NgaySinh'])) ?></td>
                        <td><?= htmlspecialchars($row['Email']) ?></td>
                        <td><?= htmlspecialchars($row['DienThoai']) ?></td>
                        <td><?= htmlspecialchars($row['TenBac'] ?? 'Chưa có') ?></td>
                        <td><?= htmlspecialchars($row['TrangThai']) ?></td>
                        <td>
                           <a href="index.php?controller=nhanvien&action=sua&manv=<?= $row['MaNV'] ?>"
   class="btn edit">✏️</a>

<a href="index.php?controller=nhanvien&action=xoa&manv=<?= $row['MaNV'] ?>"
   class="btn delete"
   onclick="return confirm('Bạn có chắc muốn xóa nhân viên này?')">
   🗑️
</a>
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9" style="text-align:center;">
                        ❌ Không tìm thấy nhân viên phù hợp
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </main>
<?php include 'views/layout/footer.php'; ?>