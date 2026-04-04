<?php include 'views/layout/header.php'; ?>
<?php include 'views/layout/sidebar.php'; ?>
    <main class="main-content">
        <header><h1>🏅 Quản lý Khen thưởng - Kỷ luật</h1></header>

        <div class="actions">
            <a href="index.php?controller=khenthuong&action=them" class="btn add">➕ Thêm Quyết định</a>
            
            <form action="index.php" method="GET" class="filter-form">
                <input type="hidden" name="controller" value="khenthuong">
                <input type="hidden" name="action" value="index">
                <input type="text" name="search" class="search-box"
                    placeholder="🔍 Tên NV, hình thức..."
                    value="<?= htmlspecialchars($_GET['search'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                <select name="loai">
                    <option value="">-- Tất cả loại --</option>
                    <option value="Khen thưởng" <?= ($_GET['loai'] ?? '')=='Khen thưởng'?'selected':'' ?>>Khen thưởng</option>
                    <option value="Kỷ luật" <?= ($_GET['loai'] ?? '')=='Kỷ luật'?'selected':'' ?>>Kỷ luật</option>
                </select>
                <input type="month" name="thang" value="<?= htmlspecialchars($_GET['thang'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                <button type="submit" class="btn search">Lọc</button>
            </form>
        </div>
        <div class="stat-row">
            <div class="stat-box stat-success">
                <h3>💰 Tổng Khen thưởng</h3>
                <p><?= number_format($tong['TongThuong'] ?? 0, 0, ',', '.') ?> VNĐ</p>
            </div>
            <div class="stat-box stat-danger">
                <h3>⚠ Tổng Kỷ luật</h3>
                <p><?= number_format($tong['TongPhat'] ?? 0, 0, ',', '.') ?> VNĐ</p>
            </div>
            <div class="stat-box stat-info">
                <h3>📊 Chênh lệch</h3>
                <p><?= number_format(($tong['TongThuong'] ?? 0) - ($tong['TongPhat'] ?? 0), 0, ',', '.') ?> VNĐ</p>
            </div>
        </div>

        <table class="table">
    <thead>
        <tr>
            <th>Mã</th>
            <th>Mã NV</th>
            <th>Họ tên NV</th>
            <th>Loại</th>
            <th>Tên loại</th>
            <th>Ngày quyết định</th>
            <th>Hình thức</th>
            <th>Số tiền (VNĐ)</th>
            <th>Thao tác</th>
        </tr>
    </thead>
    <tbody>
        <?php
        if (isset($result) && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {

                $loai_class = ($row['Loai'] == 'Khen thưởng') ? 'success' : 'danger';

                echo "<tr>
                        <td>{$row['MaKTKL']}</td>
                        <td>{$row['MaNV']}</td>
                        <td>{$row['HoTen']}</td>
                        <td><span class='btn {$loai_class}'>{$row['Loai']}</span></td>
                        <td>{$row['TenLoai']}</td>
                        <td>" . date('d/m/Y', strtotime($row['NgayQuyetDinh'])) . "</td>
                        <td>{$row['HinhThuc']}</td>
                        <td>" . number_format($row['SoTien'], 0, ',', '.') . "</td>
                        <td>
                            <a href='index.php?controller=khenthuong&action=sua&id={$row['MaKTKL']}' 
                               class='btn edit'>✏️ Sửa</a>

                            <a href='index.php?controller=khenthuong&action=xoa&id={$row['MaKTKL']}' 
                               class='btn delete'
                               onclick='return confirm(\"Xóa quyết định này?\");'>
                               🗑️ Xóa
                            </a>
                        </td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='9'>Không có dữ liệu.</td></tr>";
        }
        ?>
    </tbody>
</table>
    </main>
<?php include 'views/layout/footer.php'; ?>