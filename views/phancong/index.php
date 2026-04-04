<?php include 'views/layout/header.php'; ?>
<?php include 'views/layout/sidebar.php'; ?>
  <!-- MAIN -->
  <main class="main-content">
    <header>
      <h1>📌 Phân công nhân viên</h1>
    </header>

    <div class="actions">
      <?php if(in_array('them_phancong', $quyen)): ?>
      <a href="index.php?controller=phancong&action=add" class="btn add">
        ➕ Phân công mới
      </a>
      <?php endif; ?>
      
    </div>

    <!-- TABLE -->
    <table class="table">
      <thead>
        <tr>
          <th>Mã NV</th>
          <th>Họ tên</th>
          <th>Phòng ban</th>
          <th>Chức vụ</th>
          <th>Từ ngày</th>
          <th>Đến ngày</th>
          <th>Loại</th>
          <th>Thao tác</th>
        </tr>
      </thead>

      <tbody>
        <?php if ($phancongs && mysqli_num_rows($phancongs) > 0): ?>
          <?php while ($row = mysqli_fetch_assoc($phancongs)): ?>
            <tr>
              <td><?= $row['MaNV'] ?></td>
              <td><?= htmlspecialchars($row['HoTen']) ?></td>
              <td><?= htmlspecialchars($row['TenPB']) ?></td>
              <td><?= htmlspecialchars($row['TenCV']) ?></td>
              <td><?= $row['NgayBatDau'] ?></td>
              <td><?= $row['NgayKetThuc'] ?: 'Hiện tại' ?></td>
              <td><?= htmlspecialchars($row['LoaiDieuChuyen']) ?></td>
              <td>
                <div class="table-actions">
                <?php if(in_array('sua_phancong', $quyen)): ?>
                <a class="btn edit"
                   href="index.php?controller=phancong&action=edit&id=<?= $row['MaQT'] ?>"
                   title="Chỉnh sửa">✏️</a>
                <?php endif; ?>
                <?php if(in_array('xoa_phancong', $quyen)): ?>
                <a class="btn delete"
                   title="Xóa"
                   onclick="return confirm('Xóa phân công này?')"
                   href="index.php?controller=phancong&action=delete&id=<?= $row['MaQT'] ?>">🗑️</a>
                <?php endif; ?>
                <?php if(in_array('xem_lichsu_phancong', $quyen)): ?>
                 <a class="btn search"
                   href="index.php?controller=phancong&action=history&manv=<?= $row['MaNV'] ?>"
                   title="Lịch sử">📈</a>
                <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr>
            <td colspan="7" style="text-align:center">
              Chưa có dữ liệu phân công
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>

  </main>
<?php include 'views/layout/footer.php'; ?>
