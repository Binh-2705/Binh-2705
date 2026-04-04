<?php include 'views/layout/header.php'; ?>
<?php include 'views/layout/sidebar.php'; ?>
  <main class="main-content">
    <header>
      <h1>➕ Thêm Chấm Công</h1>
    </header>

    <form method="POST"
          action="index.php?controller=chamcong&action=luu"
          class="form-nv">

      <!-- Nhân viên -->
      <div class="form-group">
        <label>Nhân viên:</label>
        <select name="MaNV" required>
          <option value="">-- Chọn nhân viên --</option>
          <?php while ($r = $nhanvien->fetch_assoc()): ?>
            <option value="<?= $r['MaNV'] ?>">
              <?= $r['MaNV'] . ' - ' . $r['HoTen'] ?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>

      <!-- Ngày làm việc -->
      <div class="form-group">
  <label>Ngày làm việc:</label>
  <input type="date" name="NgayLamViec" id="NgayLamViec" required>
  <small id="thuLabel" style="color:#666"></small>
</div>

      <!-- Giờ vào -->
      <div class="form-group">
        <label>Giờ vào:</label>
        <input type="time" name="GioVao" id="GioVao">
      </div>

      <!-- Giờ ra -->
      <div class="form-group">
        <label>Giờ ra:</label>
        <input type="time" name="GioRa" id="GioRa">
      </div>

      <!-- Trạng thái -->
      <div class="form-group">
        <label>Trạng thái:</label>
       <select name="TrangThai" id="TrangThai" required>
          <option value="Đi làm">Đi làm</option>
          <option value="Nghỉ phép">Nghỉ phép</option>
          <option value="Nghỉ không phép">Nghỉ không phép</option>
        </select>
      </div>

      <div class="form-buttons">
        <button type="submit" class="btn add">💾 Lưu</button>
        <a href="index.php?controller=chamcong&action=index" class="btn cancel">↩️ Quay lại</a>
      </div>
    </form>
  </main>
<?php include 'views/layout/footer.php'; ?>