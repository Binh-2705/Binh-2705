<?php include 'views/layout/header.php'; ?>
<?php include 'views/layout/sidebar.php'; ?>

    <!-- CONTENT -->
    <main class="main-content hoso-page">
        <header class="hoso-page-header">
            <h1 data-i18n="profile_page.title">👥 Danh sách hồ sơ nhân viên</h1>
            <div class="hoso-page-tools">
            <form method="GET" action="index.php" class="search-form hoso-search-form">
                <input type="hidden" name="controller" value="hosocanhan">
                <input type="hidden" name="action" value="index">
                <input
                    type="text"
                    name="keyword"
                    class="search-box"
                    placeholder="🔍 Tìm theo mã NV, tên, phòng ban..."
                    data-i18n-placeholder="profile_page.search_placeholder"
                    value="<?= htmlspecialchars((string)($keyword ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                >
                <button type="submit" class="btn search" data-i18n="common.search">Tìm kiếm</button>
                <?php if (!empty($keyword)): ?>
                    <a href="index.php?controller=hosocanhan&action=index" class="btn hoso-reset-btn" data-i18n="common.refresh">Làm mới</a>
                <?php endif; ?>
            </form>
            <?php if(in_array('them_hoso', $quyen)): ?>
            <a href="index.php?controller=hosocanhan&action=them" class="btn add" data-i18n="profile_page.add">➕ Thêm hồ sơ</a>
            <?php endif; ?>
            </div>
        </header>

        <table class="table">
            <thead>
                <tr>
                    <th data-i18n="common.employee_code">Mã NV</th>
                    <th data-i18n="common.employee">Nhân viên</th> <th data-i18n="common.department">Phòng Ban</th>
                    <th data-i18n="common.position">Chức Vụ</th>
                    <th style="text-align:center;" data-i18n="common.actions">Thao tác</th>
                </tr>
            </thead>

            <tbody>
                <?php if($hosos && mysqli_num_rows($hosos) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($hosos)): ?>
                        <tr>
                            <td><strong><?= $row['MaNV'] ?></strong></td>
                            
                            <td style="display: flex; align-items: center; gap: 10px;">
                                <?php if(!empty($row['Anh'])): ?>
                                    <img src="uploads/<?= $row['Anh'] ?>" width="35" height="35" style="border-radius:50%; object-fit: cover;">
                                <?php else: ?>
                                    <div style="width:35px; height:35px; border-radius:50%; background:#ccc; display:inline-block;"></div>
                                <?php endif; ?>
                                <span><?= $row['HoTen'] ?></span>
                            </td>

                            <td><?= htmlspecialchars($row['TenPB'] ?? '---') ?></td>
                            <td><?= htmlspecialchars($row['TenCV'] ?? '---') ?></td>

                            <td style="text-align:center;">
                                <div class="table-actions">
                                <?php if(in_array('xem_hoso', $quyen)): ?>
                                    <a href="index.php?controller=hosocanhan&action=xem&id=<?= $row['MaHoSo'] ?>" 
                                                    class="btn search" title="Xem chi tiết" data-i18n-title="profile_page.view_title">👁️</a>
                                <?php endif; ?>

                                <?php if(in_array('sua_hoso', $quyen)): ?>
                                    <a href="index.php?controller=hosocanhan&action=sua&id=<?= $row['MaHoSo'] ?>" 
                                                    class="btn edit" title="Chỉnh sửa" data-i18n-title="profile_page.edit_title">✏️</a>
                                <?php endif; ?>

                                <?php if(in_array('xoa_hoso', $quyen)): ?>
                                    <a href="index.php?controller=hosocanhan&action=xoa&id=<?= $row['MaHoSo'] ?>" 
                                       class="btn delete" title="Xóa"
                                                    data-i18n-title="profile_page.delete_title"
                                                    onclick="return confirm((window.HRMSettings && window.HRMSettings.get().language === 'en') ? 'Are you sure you want to delete this profile?' : 'Bạn có chắc muốn xóa hồ sơ của <?= $row['HoTen'] ?>?')">🗑️</a>
                                <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align:center; padding: 20px;" data-i18n="profile_page.empty">Không có dữ liệu hồ sơ nào.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <?php if (($totalPages ?? 1) > 1): ?>
        <div class="pagination-wrap">
            <?php $currentPage = (int)($page ?? 1); ?>
            <?php $keywordQuery = !empty($keyword) ? '&keyword=' . urlencode((string)$keyword) : ''; ?>
            <a class="page-link <?= $currentPage <= 1 ? 'disabled' : '' ?>" href="index.php?controller=hosocanhan&action=index&page=<?= max(1, $currentPage - 1) ?><?= $keywordQuery ?>" data-i18n="common.prev">← Trước</a>
            <span class="page-indicator"><span data-i18n="common.page">Trang</span> <?= $currentPage ?> / <?= (int)$totalPages ?></span>
            <a class="page-link <?= $currentPage >= (int)$totalPages ? 'disabled' : '' ?>" href="index.php?controller=hosocanhan&action=index&page=<?= min((int)$totalPages, $currentPage + 1) ?><?= $keywordQuery ?>" data-i18n="common.next">Sau →</a>
        </div>
        <?php endif; ?>
    </main>
  <?php include 'views/layout/footer.php'; ?>