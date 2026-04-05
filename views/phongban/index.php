<?php include 'views/layout/header.php'; ?>
<?php include 'views/layout/sidebar.php'; ?>
<?php
$quyen = $quyen ?? [];
$canThemPhongBan = in_array('them_phongban', $quyen, true);
$canImportPhongBan = in_array('import_csv_phongban', $quyen, true);
$canXuatExcelPhongBan = in_array('xuat_excel_phongban', $quyen, true);
$canTimKiemPhongBan = in_array('timkiem_phongban', $quyen, true);
$canSuaPhongBan = in_array('sua_phongban', $quyen, true);
$canXoaPhongBan = in_array('xoa_phongban', $quyen, true);
$showActionColumn = $canSuaPhongBan || $canXoaPhongBan;
?>

  <!-- ===== MAIN ===== -->
  <main class="main-content">
    <header>
      <h1>🏢 Quản lý Phòng ban</h1>
    </header>

    <!-- ===== ACTIONS ===== -->
    <div class="actions">
      <?php if ($canThemPhongBan || $canImportPhongBan || $canXuatExcelPhongBan): ?>
      <div class="btn-group">
        <?php if ($canThemPhongBan): ?>
        <a href="index.php?controller=phongban&action=them" class="btn add">➕ Thêm phòng ban</a>
        <?php endif; ?>
        <?php if ($canImportPhongBan): ?>
        <a href="index.php?controller=phongban&action=import" class="btn export">📂 Nhập CSV</a>
        <?php endif; ?>
        <?php if ($canXuatExcelPhongBan): ?>
        <a href="index.php?controller=phongban&action=exportExcel" class="btn export">📥 Xuất Excel</a>
        <?php endif; ?>
      </div>
      <?php endif; ?>

      <?php if ($canTimKiemPhongBan): ?>
      <form method="GET" action="index.php" class="search-form">
        <input type="hidden" name="controller" value="phongban">
        <input type="hidden" name="action" value="timkiem">
        <input type="text"
               name="keyword"
               class="search-box"
               placeholder="🔍 Tìm tên phòng ban..."
               value="<?= isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : '' ?>">
        <button type="submit" class="btn search">Tìm</button>
      </form>
      <?php endif; ?>
    </div>

    <!-- ===== TABLE ===== -->
    <table class="table">
      <thead>
        <tr>
          <th>Mã PB</th>
          <th>Tên phòng ban</th>
          <th>Mô tả</th>
          <?php if ($showActionColumn): ?>
          <th>Thao tác</th>
          <?php endif; ?>
        </tr>
      </thead>
      <tbody>
        <?php if ($phongbans && $phongbans->num_rows > 0): ?>
          <?php while ($row = $phongbans->fetch_assoc()): ?>
            <tr>
              <td><?= $row['MaPB'] ?></td>
              <td><?= htmlspecialchars($row['TenPB']) ?></td>
              <td><?= htmlspecialchars($row['MoTa']) ?></td>
              <?php if ($showActionColumn): ?>
              <td>
                <div class="table-actions">
                <?php if ($canSuaPhongBan): ?>
                <a href="index.php?controller=phongban&action=sua&mapb=<?= $row['MaPB'] ?>"
                   class="btn edit"
                   title="Chỉnh sửa">✏️</a>
                <?php endif; ?>
                <?php if ($canXoaPhongBan): ?>
                <a href="index.php?controller=phongban&action=xoa&mapb=<?= $row['MaPB'] ?>"
                   class="btn delete"
                   title="Xóa"
                   onclick="return confirm('Bạn có chắc muốn xóa phòng ban này?')">🗑️</a>
                <?php endif; ?>
                </div>
              </td>
              <?php endif; ?>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr>
            <td colspan="<?= $showActionColumn ? 4 : 3 ?>">Không có phòng ban</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>

  </main>
<?php include 'views/layout/footer.php'; ?>