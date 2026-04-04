<?php include 'views/layout/header.php'; ?>
<?php include 'views/layout/sidebar.php'; ?>

  <!-- ===== MAIN ===== -->
  <main class="main-content">
    <header>
      <h1>🏢 Quản lý Phòng ban</h1>
    </header>

    <!-- ===== ACTIONS ===== -->
    <div class="actions">
      <div class="btn-group">
        <a href="index.php?controller=phongban&action=them" class="btn add">➕ Thêm phòng ban</a>
        <a href="index.php?controller=phongban&action=import" class="btn export">📂 Nhập CSV</a>
        <a href="index.php?controller=phongban&action=exportExcel" class="btn export">📥 Xuất Excel</a>
      </div>

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
    </div>

    <!-- ===== TABLE ===== -->
    <table class="table">
      <thead>
        <tr>
          <th>Mã PB</th>
          <th>Tên phòng ban</th>
          <th>Mô tả</th>
          <th>Thao tác</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($phongbans && $phongbans->num_rows > 0): ?>
          <?php while ($row = $phongbans->fetch_assoc()): ?>
            <tr>
              <td><?= $row['MaPB'] ?></td>
              <td><?= htmlspecialchars($row['TenPB']) ?></td>
              <td><?= htmlspecialchars($row['MoTa']) ?></td>
              <td>
                <div class="table-actions">
                <a href="index.php?controller=phongban&action=sua&mapb=<?= $row['MaPB'] ?>"
                   class="btn edit"
                   title="Chỉnh sửa">✏️</a>
                <a href="index.php?controller=phongban&action=xoa&mapb=<?= $row['MaPB'] ?>"
                   class="btn delete"
                   title="Xóa"
                   onclick="return confirm('Bạn có chắc muốn xóa phòng ban này?')">🗑️</a>
                </div>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr>
            <td colspan="4">Không có phòng ban</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>

  </main>
<?php include 'views/layout/footer.php'; ?>