<?php include 'views/layout/header.php'; ?>
<?php include 'views/layout/sidebar.php'; ?>

    <!-- CONTENT -->
    <main class="main-content">
        <header style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 20px;">
            <h1>👥 Danh sách hồ sơ nhân viên</h1>
            <?php if(in_array('them_hoso', $quyen)): ?>
            <a href="index.php?controller=hosocanhan&action=them" class="btn add">➕ Thêm hồ sơ</a>
            <?php endif; ?>
        </header>

        <table class="table">
            <thead>
                <tr>
                    <th>Mã NV</th>
                    <th>Nhân viên</th> <th>Phòng Ban</th>
                    <th>Chức Vụ</th>
                    <th style="text-align:center;">Thao tác</th>
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
                                       class="btn search" title="Xem chi tiết">👁️</a>
                                <?php endif; ?>

                                <?php if(in_array('sua_hoso', $quyen)): ?>
                                    <a href="index.php?controller=hosocanhan&action=sua&id=<?= $row['MaHoSo'] ?>" 
                                       class="btn edit" title="Chỉnh sửa">✏️</a>
                                <?php endif; ?>

                                <?php if(in_array('xoa_hoso', $quyen)): ?>
                                    <a href="index.php?controller=hosocanhan&action=xoa&id=<?= $row['MaHoSo'] ?>" 
                                       class="btn delete" title="Xóa"
                                       onclick="return confirm('Bạn có chắc muốn xóa hồ sơ của <?= $row['HoTen'] ?>?')">🗑️</a>
                                <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align:center; padding: 20px;">Không có dữ liệu hồ sơ nào.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <?php if (($totalPages ?? 1) > 1): ?>
        <div class="pagination-wrap">
            <?php $currentPage = (int)($page ?? 1); ?>
            <a class="page-link <?= $currentPage <= 1 ? 'disabled' : '' ?>" href="index.php?controller=hosocanhan&action=index&page=<?= max(1, $currentPage - 1) ?>">← Trước</a>
            <span class="page-indicator">Trang <?= $currentPage ?> / <?= (int)$totalPages ?></span>
            <a class="page-link <?= $currentPage >= (int)$totalPages ? 'disabled' : '' ?>" href="index.php?controller=hosocanhan&action=index&page=<?= min((int)$totalPages, $currentPage + 1) ?>">Sau →</a>
        </div>
        <?php endif; ?>
    </main>
  <?php include 'views/layout/footer.php'; ?>