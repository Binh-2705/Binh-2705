<?php include 'views/layout/header.php'; ?>
<?php include 'views/layout/sidebar.php'; ?>

  <!-- ===== MAIN ===== -->
  <main class="main-content">

    <header>
      <h1>🔎 Kết quả tìm kiếm</h1>
      <p class="subtitle">
        Từ khóa: <strong>"<?= htmlspecialchars($keyword) ?>"</strong>
      </p>
    </header>

    <div class="actions">
      <a href="index.php?controller=phongban&action=index" class="btn cancel">↩️ Quay lại danh sách</a>
    </div>

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
        <?php if ($result && mysqli_num_rows($result) > 0): ?>
          <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <tr>
              <td><?= htmlspecialchars($row['MaPB']) ?></td>
              <td><?= htmlspecialchars($row['TenPB']) ?></td>
              <td><?= htmlspecialchars($row['MoTa']) ?></td>
              <td>
                <a href="index.php?controller=phongban&action=sua&mapb=<?= $row['MaPB'] ?>"
                   class="btn edit">✏️ Sửa</a>
                <a href="index.php?controller=phongban&action=xoa&mapb=<?= $row['MaPB'] ?>"
                   class="btn delete"
                   onclick="return confirm('Bạn có chắc muốn xóa phòng ban này không?');">
                   🗑️ Xóa
                </a>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr>
            <td colspan="4">❌ Không tìm thấy phòng ban phù hợp</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>

  </main>
<?php include 'views/layout/footer.php'; ?>
