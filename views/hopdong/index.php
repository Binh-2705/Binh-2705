<?php include 'views/layout/header.php'; ?>
<?php include 'views/layout/sidebar.php'; ?>
    <!-- MAIN -->
    <main class="main-content">

        <h1>📄 Quản lý Hợp đồng</h1>

        <!-- ACTION -->
        <div class="actions">
            <a href="index.php?controller=hopdong&action=them" class="btn add">➕ Thêm hợp đồng</a>

            <form method="get" class="filter-form">
                <input type="hidden" name="controller" value="hopdong">
                <input type="hidden" name="action" value="index">

                <input type="text" name="keyword"
                       placeholder="🔍 Số HĐ / Tên NV"
                       value="<?= htmlspecialchars($_GET['keyword'] ?? '') ?>">

                <select name="loaiHD">
                    <option value="">-- Loại HĐ --</option>
                    <option value="Xác định thời hạn" <?= (($_GET['loaiHD'] ?? '') === 'Xác định thời hạn') ? 'selected' : '' ?>>Xác định thời hạn</option>
                    <option value="Không xác định thời hạn" <?= (($_GET['loaiHD'] ?? '') === 'Không xác định thời hạn') ? 'selected' : '' ?>>Không xác định thời hạn</option>
                </select>

                <select name="trangThai">
                    <option value="">-- Trạng thái --</option>
                    <option value="con" <?= (($_GET['trangThai'] ?? '') === 'con') ? 'selected' : '' ?>>Còn hiệu lực</option>
                    <option value="het" <?= (($_GET['trangThai'] ?? '') === 'het') ? 'selected' : '' ?>>Hết hạn</option>
                    <option value="chamdut" <?= (($_GET['trangThai'] ?? '') === 'chamdut') ? 'selected' : '' ?>>Chấm dứt</option>
                </select>

                <input type="date" name="tuNgay" title="Từ ngày" value="<?= htmlspecialchars((string)($_GET['tuNgay'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                <input type="date" name="denNgay" title="Đến ngày" value="<?= htmlspecialchars((string)($_GET['denNgay'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">

                <button class="btn search">Lọc</button>
            </form>
        </div>

        <!-- TABLE -->
        <table class="table">
            <thead>
                <tr>
                    <th>Mã HĐ</th>
                    <th>Số HĐ</th>
                    <th>Nhân viên</th>
                    <th>Loại HĐ</th>
                    <th>Ngày bắt đầu</th>
                    <th>Ngày kết thúc</th>
                    <th>Bậc lương</th>
                    <th>Lương áp dụng</th>
                    <th>Trạng thái</th>
                    <th>Thao tác</th>
                </tr>
            </thead>

            <tbody>
            <?php if (!empty($result) && mysqli_num_rows($result) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($result)): 
    $today = strtotime(date('Y-m-d'));
    
    // Tính số ngày còn lại (Sử dụng cột SoNgayConLai từ SQL đã tính sẵn)
    $days = $row['SoNgayConLai']; 

    // ===== NGÀY KẾT THÚC =====
    $ngayKT = ($row['NgayKetThuc'] === null) ? 'Vô thời hạn' : date('d/m/Y', strtotime($row['NgayKetThuc']));

    // ===== TRẠNG THÁI HIỂN THỊ =====
   // ===== TRẠNG THÁI HIỂN THỊ (CHUẨN NGHIỆP VỤ) =====
if ($row['TrangThai'] === 'Hết hiệu lực') {
    $trangThai = '⛔ Đã chấm dứt';
    $class = 'danger';
} else { // Còn hiệu lực
    if ($row['NgayKetThuc'] === null) {
        $trangThai = 'Vô thời hạn';
        $class = 'success';
    }
    elseif ($days < 0) {
        $trangThai = 'Đã hết hạn';
        $class = 'warning';
    }
    elseif ($days <= 30) {
        $trangThai = '⚠️ Sắp hết hạn (' . $days . ' ngày)';
        $class = 'warning';
    }
    else {
        $trangThai = 'Còn hiệu lực';
        $class = 'success';
    }
}




?>
                    

                    <tr>
                        <td><?= $row['MaHopDong'] ?></td>
                        <td><?= htmlspecialchars($row['SoHopDong']) ?></td>
                        <td><?= htmlspecialchars($row['HoTen']) ?></td>
                        <td><?= htmlspecialchars($row['LoaiHopDong']) ?></td>
                        <td><?= date('d/m/Y', strtotime($row['NgayBatDau'])) ?></td>
                        <td><?= $ngayKT ?></td>
                        <td><?= htmlspecialchars($row['TenBac']) ?></td>
                        <td><?= number_format($row['LuongThucTe'], 0, ',', '.') ?> VNĐ</td>

                        <td>
                            <span class="btn <?= $class ?>">
                                <?= $trangThai ?>
                            </span>
                        </td>

                       <td>
<div class="table-actions">
<?php if ($row['TrangThai'] === 'Hết hiệu lực'): ?>
    <span class="muted-inline-note">Không khả dụng</span>
<?php else: ?>
    <a class="btn edit"
       href="index.php?controller=hopdong&action=giahan&MaHopDong=<?= $row['MaHopDong'] ?>"
       title="Gia hạn">🔁</a>

    <a class="btn delete"
       href="index.php?controller=hopdong&action=chamdut&MaHopDong=<?= $row['MaHopDong'] ?>"
       title="Chấm dứt"
       onclick="return confirm('⚠️ Chấm dứt hợp đồng này?')">⛔</a>

    <a href="index.php?controller=hopdong&action=lichsu_luong&MaHopDong=<?= $row['MaHopDong'] ?>" 
       class="btn search"
       title="Lịch sử lương">📊</a>
<?php endif; ?>
</div>
</td>



                    </tr>

                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="10" class="table-empty">📭 Chưa có hợp đồng</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>

        <?php if (($totalPages ?? 1) > 1): ?>
            <?php
            $query = [
                'controller' => 'hopdong',
                'action' => 'index',
                'keyword' => (string)($_GET['keyword'] ?? ''),
                'loaiHD' => (string)($_GET['loaiHD'] ?? ''),
                'trangThai' => (string)($_GET['trangThai'] ?? ''),
                'tuNgay' => (string)($_GET['tuNgay'] ?? ''),
                'denNgay' => (string)($_GET['denNgay'] ?? ''),
            ];
            $prev = $query;
            $prev['page'] = max(1, (int)$page - 1);
            $next = $query;
            $next['page'] = min((int)$totalPages, (int)$page + 1);
            ?>
            <div class="pagination-wrap">
                <a class="page-link <?= ((int)$page <= 1) ? 'disabled' : '' ?>" href="index.php?<?= htmlspecialchars(http_build_query($prev), ENT_QUOTES, 'UTF-8') ?>">← Trước</a>
                <span class="page-indicator">Trang <?= (int)$page ?> / <?= (int)$totalPages ?></span>
                <a class="page-link <?= ((int)$page >= (int)$totalPages) ? 'disabled' : '' ?>" href="index.php?<?= htmlspecialchars(http_build_query($next), ENT_QUOTES, 'UTF-8') ?>">Sau →</a>
            </div>
        <?php endif; ?>

    </main>
    <?php include 'views/layout/footer.php'; ?>

                