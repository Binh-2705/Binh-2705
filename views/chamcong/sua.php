<?php include 'views/layout/header.php'; ?>
<?php include 'views/layout/sidebar.php'; ?>

  <main class="main-content">
    <header>
      <h1>✏️ Sửa Chấm Công</h1>
    </header>

    <form method="POST"
          action="index.php?controller=chamcong&action=luuSua"
          class="form-nv">

      <!-- MaCC -->
      <input type="hidden" name="MaCC" value="<?= $row['MaCC'] ?>">

      <!-- Nhân viên -->
      <div class="form-group">
        <label>Nhân viên:</label>
        <select name="MaNV" required>
          <option value="">-- Chọn nhân viên --</option>
          <?php while ($r = $nhanvien->fetch_assoc()): ?>
            <option value="<?= $r['MaNV'] ?>"
              <?= ($r['MaNV'] == $row['MaNV']) ? 'selected' : '' ?>>
              <?= $r['MaNV'] . ' - ' . $r['HoTen'] ?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>

      <!-- Ngày làm việc -->
      <div class="form-group">
        <label>Ngày làm việc:</label>
        <input type="date"
               name="NgayLamViec"
               value="<?= $row['NgayLamViec'] ?>"
               required>
      </div>

      <!-- Giờ vào -->
      <div class="form-group">
        <label>Giờ vào:</label>
        <input type="time"
               name="GioVao"
               value="<?= $row['GioVao'] ?>">
      </div>

      <!-- Giờ ra -->
      <div class="form-group">
        <label>Giờ ra:</label>
        <input type="time"
               name="GioRa"
               value="<?= $row['GioRa'] ?>">
      </div>

      <!-- Trạng thái -->
      <div class="form-group">
        <label>Trạng thái:</label>
        <select name="TrangThai" required>
          <option value="Đi làm" <?= ($row['TrangThai']=='Đi làm')?'selected':'' ?>>Đi làm</option>
          <option value="Nghỉ phép" <?= ($row['TrangThai']=='Nghỉ phép')?'selected':'' ?>>Nghỉ phép</option>
          <option value="Nghỉ không phép" <?= ($row['TrangThai']=='Nghỉ không phép')?'selected':'' ?>>Nghỉ không phép</option>
        </select>
      </div>

      <div class="form-buttons">
        <button type="submit" class="btn add">💾 Lưu</button>
        <a href="index.php?controller=chamcong&action=index" class="btn cancel">↩️ Quay lại</a>
      </div>
    </form>
  </main>
<?php include 'views/layout/footer.php'; ?>
