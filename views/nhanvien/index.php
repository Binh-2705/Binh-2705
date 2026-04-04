<?php include 'views/layout/header.php'; ?>
<?php include 'views/layout/sidebar.php'; ?>

    <main class="main-content">
        <header>
            <h1>👥 Quản lý Nhân viên</h1>
        </header>

        <div class="actions">
            <div class="btn-group">
                <?php if(in_array('them_nhanvien', $quyen)): ?>
                <a href="index.php?controller=nhanvien&action=them" class="btn add">➕ Thêm nhân viên</a>
                <?php endif; ?>
                <!-- exportExcel nếu CHƯA làm thì nên ẩn -->
                <!-- <a href="index.php?controller=nhanvien&action=exportExcel" class="btn export">📥 Xuất Excel</a> -->
            </div>

            <!-- search -->
            <form method="GET" action="index.php" class="search-form">
                <input type="hidden" name="controller" value="nhanvien">
                <input type="hidden" name="action" value="timkiem">
                <input type="text" name="keyword" placeholder="🔍 Nhập tên nhân viên..." class="search-box" required>
                <button type="submit" class="btn search">Tìm</button>
            </form>
            
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th width="50px">STT</th>
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
                <?php 
                    $stt = $sttStart ?? 1;
                    while ($row = mysqli_fetch_assoc($nhanviens)): ?>
                    <tr>
                        <td style="text-align: center; font-weight: bold;"><?= $stt++ ?></td>
                        <td><?= $row['MaNV'] ?></td>
                        <td><?= $row['HoTen'] ?></td>
                        <td><?= $row['GioiTinh'] ?></td>
                        <td><?= (!empty($row['NgaySinh']) && $row['NgaySinh'] != '0000-00-00') ? date('d/m/Y', strtotime($row['NgaySinh'])) : 'Chưa nhập' ?></td>
                        <td><?= $row['Email'] ?></td>
                        <td><?= $row['DienThoai'] ?></td>
                        <td><?= $row['TenBac'] ?? 'Chưa có' ?></td>
                        <td><?= $row['TrangThai'] ?></td>
                       <td>
<div class="table-actions">
<?php if(in_array('sua_nhanvien', $quyen)): ?>
<a href="index.php?controller=nhanvien&action=sua&manv=<?= $row['MaNV'] ?>"
   class="btn edit"
   title="Chỉnh sửa nhân viên">✏️</a>
<?php endif; ?>

<?php if(in_array('xoa_nhanvien', $quyen)): ?>
<a href="index.php?controller=nhanvien&action=xoa&manv=<?= $row['MaNV'] ?>"
   class="btn delete"
   title="Xóa nhân viên"
   onclick="return confirm('Bạn có chắc muốn xóa nhân viên này?')">🗑️</a>
<?php endif; ?>
</div>
</td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9">Không có nhân viên</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>

        <?php if (($totalPages ?? 1) > 1): ?>
        <div class="pagination-wrap">
            <?php $currentPage = (int)($page ?? 1); ?>
            <a class="page-link <?= $currentPage <= 1 ? 'disabled' : '' ?>" href="index.php?controller=nhanvien&action=index&page=<?= max(1, $currentPage - 1) ?>">← Trước</a>
            <span class="page-indicator">Trang <?= $currentPage ?> / <?= (int)$totalPages ?></span>
            <a class="page-link <?= $currentPage >= (int)$totalPages ? 'disabled' : '' ?>" href="index.php?controller=nhanvien&action=index&page=<?= min((int)$totalPages, $currentPage + 1) ?>">Sau →</a>
        </div>
        <?php endif; ?>

    </main>
<?php include 'views/layout/footer.php'; ?>