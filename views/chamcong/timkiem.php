<?php include 'views/layout/header.php'; ?>
<?php include 'views/layout/sidebar.php'; ?>

  <main class="main-content">
    <header>
      <h1>🔍 Kết quả tìm kiếm: "<?= htmlspecialchars($keyword) ?>"</h1>
    </header>

    <div class="actions">
      <a href="index.php?controller=chamcong&action=index" class="btn cancel">
        ↩️ Quay lại danh sách
      </a>
    </div>

    <table class="table">
      <thead>
        <tr>
          <th>Mã CC</th>
          <th>Mã NV</th>
          <th>Họ tên</th>
          <th>Ngày làm</th>
          <th>Giờ vào</th>
          <th>Giờ ra</th>
          <th>Số giờ làm</th>
          <th>Trạng thái</th>
          <th>Thao tác</th>
        </tr>
      </thead>

      <tbody>
        <?php if ($data && $data->num_rows > 0): ?>
          <?php while ($row = $data->fetch_assoc()): ?>
            <tr>
              <td><?= $row['MaCC'] ?></td>
              <td><?= $row['MaNV'] ?></td>
              <td><?= htmlspecialchars($row['HoTen']) ?></td>
              <td><?= date('d/m/Y', strtotime($row['NgayLamViec'])) ?></td>
              <td><?= $row['GioVao'] ?? '-' ?></td>
              <td><?= $row['GioRa'] ?? '-' ?></td>
              <td><?= $row['SoGioLam'] ?? '0' ?></td>
              <td><?= $row['TrangThai'] ?></td>
              <td>
                <a href="index.php?controller=chamcong&action=sua&macc=<?= $row['MaCC'] ?>"
                   class="btn edit">✏️ Sửa</a>

                <a href="index.php?controller=chamcong&action=xoa&macc=<?= $row['MaCC'] ?>"
                   class="btn delete"
                   onclick="return confirm('Bạn có chắc muốn xóa bản ghi này không?');">
                   🗑️ Xóa
                </a>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr>
            <td colspan="9">❌ Không tìm thấy bản ghi nào.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </main>
<?php include 'views/layout/footer.php'; ?>
