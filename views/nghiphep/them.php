<?php include 'views/layout/header.php'; ?>
<?php include 'views/layout/sidebar.php'; ?>
<?php $isEmployeeScope = $isEmployeeScope ?? false; ?>

  <main class="main-content">
    <header>
      <h1>➕ Thêm Nghỉ phép</h1>
    </header>

    <form method="post"
          action="index.php?controller=nghiphep&action=luu"
          class="form-nv">

      <!-- NHÂN VIÊN -->
      <div class="form-group">
        <label>Nhân viên:</label>
        <?php if ($isEmployeeScope): ?>
        <?php $nv = $nhanvien ? $nhanvien->fetch_assoc() : null; ?>
        <input type="hidden" name="MaNV" value="<?= (int)($nv['MaNV'] ?? 0) ?>">
        <input type="text" value="<?= htmlspecialchars(((string)($nv['MaNV'] ?? '')) . ' - ' . ((string)($nv['HoTen'] ?? '')), ENT_QUOTES, 'UTF-8') ?>" readonly>
        <?php else: ?>
        <select name="MaNV" required>
          <option value="">-- Chọn nhân viên --</option>
          <?php while ($r = $nhanvien->fetch_assoc()): ?>
            <option value="<?= $r['MaNV'] ?>">
              <?= $r['MaNV'] . ' - ' . $r['HoTen'] ?>
            </option>
          <?php endwhile; ?>
        </select>
        <?php endif; ?>
      </div>

      <!-- TỪ NGÀY -->
      <div class="form-group">
        <label>Từ ngày:</label>
        <input type="date" name="TuNgay" required>
      </div>

      <!-- ĐẾN NGÀY -->
      <div class="form-group">
        <label>Đến ngày:</label>
        <input type="date" name="DenNgay" required>
      </div>

      <!-- LOẠI NGHỈ -->
      <div class="form-group">
        <label>Loại nghỉ:</label>
        <select name="LoaiNghi" required>
          <option value="">-- Chọn loại nghỉ --</option>
          <option value="Nghỉ phép năm">Nghỉ phép năm</option>
          <option value="Nghỉ ốm">Nghỉ ốm</option>
          <option value="Nghỉ không lương">Nghỉ không lương</option>
          <option value="Nghỉ việc riêng">Nghỉ việc riêng</option>
        </select>
      </div>

      <!-- LÝ DO -->
      <div class="form-group">
        <label>Lý do:</label>
        <textarea name="LyDo" rows="3"></textarea>
      </div>

      <!-- BUTTON -->
      <div class="form-buttons">
        <button type="submit" class="btn add">💾 Lưu</button>
        <a href="index.php?controller=nghiphep&action=index" class="btn cancel">
          ↩️ Quay lại
        </a>
      </div>

    </form>
  </main>

<?php include 'views/layout/footer.php'; ?>
