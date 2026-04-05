<?php include 'views/layout/header.php'; ?>
<?php include 'views/layout/sidebar.php'; ?>

    <main class="main-content">
        <header>
            <h1 data-i18n="employee_page.title">👥 Quản lý Nhân viên</h1>
        </header>

        <div class="actions">
            <div class="btn-group">
                <?php if(in_array('them_nhanvien', $quyen)): ?>
                <a href="index.php?controller=nhanvien&action=them" class="btn add" data-i18n="employee_page.add">➕ Thêm nhân viên</a>
                <?php endif; ?>
                <!-- exportExcel nếu CHƯA làm thì nên ẩn -->
                <!-- <a href="index.php?controller=nhanvien&action=exportExcel" class="btn export">📥 Xuất Excel</a> -->
            </div>

            <!-- search -->
            <form method="GET" action="index.php" class="search-form">
                <input type="hidden" name="controller" value="nhanvien">
                <input type="hidden" name="action" value="timkiem">
                <input type="text" name="keyword" placeholder="🔍 Nhập tên nhân viên..." data-i18n-placeholder="employee_page.search_placeholder" class="search-box" required>
                <button type="submit" class="btn search" data-i18n="common.search">Tìm</button>
            </form>
            
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th width="50px" data-i18n="common.stt">STT</th>
                    <th data-i18n="common.employee_code">Mã NV</th>
                    <th data-i18n="common.full_name">Họ tên</th>
                    <th data-i18n="common.gender">Giới tính</th>
                    <th data-i18n="common.date_of_birth">Ngày sinh</th>
                    <th data-i18n="common.email">Email</th>
                    <th data-i18n="common.phone">Điện thoại</th>
                    <th data-i18n="common.salary_step">Bậc lương</th>
                    <th data-i18n="common.status">Trạng thái</th>
                    <th data-i18n="common.actions">Thao tác</th>
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
                        <td>
                            <?php if (!empty($row['NgaySinh']) && $row['NgaySinh'] != '0000-00-00'): ?>
                                <?= date('d/m/Y', strtotime($row['NgaySinh'])) ?>
                            <?php else: ?>
                                <span data-i18n="common.not_entered">Chưa nhập</span>
                            <?php endif; ?>
                        </td>
                        <td><?= $row['Email'] ?></td>
                        <td><?= $row['DienThoai'] ?></td>
                        <td>
                            <?php if (!empty($row['TenBac'])): ?>
                                <?= $row['TenBac'] ?>
                            <?php else: ?>
                                <span data-i18n="common.not_available">Chưa có</span>
                            <?php endif; ?>
                        </td>
                        <td><?= $row['TrangThai'] ?></td>
                       <td>
<div class="table-actions">
<?php if(in_array('sua_nhanvien', $quyen)): ?>
<a href="index.php?controller=nhanvien&action=sua&manv=<?= $row['MaNV'] ?>"
   class="btn edit"
    title="Chỉnh sửa nhân viên"
    data-i18n-title="employee_page.edit_title">✏️</a>
<?php endif; ?>

<?php if(in_array('xoa_nhanvien', $quyen)): ?>
<a href="index.php?controller=nhanvien&action=xoa&manv=<?= $row['MaNV'] ?>"
   class="btn delete"
   title="Xóa nhân viên"
    data-i18n-title="employee_page.delete_title"
    onclick="return confirm((window.HRMSettings && window.HRMSettings.get().language === 'en') ? 'Are you sure you want to delete this employee?' : 'Bạn có chắc muốn xóa nhân viên này?')">🗑️</a>
<?php endif; ?>
</div>
</td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="10" data-i18n="employee_page.empty">Không có nhân viên</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>

        <?php if (($totalPages ?? 1) > 1): ?>
        <div class="pagination-wrap">
            <?php $currentPage = (int)($page ?? 1); ?>
            <a class="page-link <?= $currentPage <= 1 ? 'disabled' : '' ?>" href="index.php?controller=nhanvien&action=index&page=<?= max(1, $currentPage - 1) ?>" data-i18n="common.prev">← Trước</a>
            <span class="page-indicator"><span data-i18n="common.page">Trang</span> <?= $currentPage ?> / <?= (int)$totalPages ?></span>
            <a class="page-link <?= $currentPage >= (int)$totalPages ? 'disabled' : '' ?>" href="index.php?controller=nhanvien&action=index&page=<?= min((int)$totalPages, $currentPage + 1) ?>" data-i18n="common.next">Sau →</a>
        </div>
        <?php endif; ?>

    </main>
<?php include 'views/layout/footer.php'; ?>