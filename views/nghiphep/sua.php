<?php include 'views/layout/header.php'; ?>
<?php include 'views/layout/sidebar.php'; ?>
  <!-- MAIN -->
  <main class="main-content">
    <header>
      <h1>✏️ Sửa Nghỉ phép</h1>
    </header>

    <form method="post"
          action="index.php?controller=nghiphep&action=luuSua"
          class="form-nv">

      <!-- Mã nghỉ phép -->
      <div class="form-group">
        <label>Mã nghỉ phép:</label>
        <input type="text" value="<?= $row['MaNP'] ?>" readonly>
        <input type="hidden" name="MaNP" value="<?= $row['MaNP'] ?>">
      </div>

      <!-- Nhân viên -->
      <div class="form-group">
        <label>Nhân viên:</label>
        <select name="MaNV" required>
          <?php while ($nv = $nhanvien->fetch_assoc()): ?>
            <option value="<?= $nv['MaNV'] ?>"
              <?= ($nv['MaNV'] == $row['MaNV']) ? 'selected' : '' ?>>
              <?= $nv['MaNV'] . ' - ' . $nv['HoTen'] ?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>

      <!-- Từ ngày -->
      <div class="form-group">
        <label>Từ ngày:</label>
        <input type="date" name="TuNgay"
               value="<?= $row['TuNgay'] ?>" required>
      </div>

      <!-- Đến ngày -->
      <div class="form-group">
        <label>Đến ngày:</label>
        <input type="date" name="DenNgay"
               value="<?= $row['DenNgay'] ?>" required>
      </div>

      <!-- Loại nghỉ (BẮT BUỘC) -->
      <div class="form-group">
        <label>Loại nghỉ:</label>
        <select name="LoaiNghi" required>
          <?php
          $loai = $row['LoaiNghi'];
          $dsLoai = [
            'Nghỉ phép năm',
            'Nghỉ ốm',
            'Nghỉ không lương',
            'Nghỉ việc riêng'
          ];
          foreach ($dsLoai as $l):
          ?>
            <option value="<?= $l ?>" <?= ($l == $loai) ? 'selected' : '' ?>>
              <?= $l ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Lý do -->
      <div class="form-group">
        <label>Lý do:</label>
        <textarea name="LyDo" rows="3"><?= $row['LyDo'] ?></textarea>
      </div>

      <!-- Trạng thái (HR duyệt) -->
      <div class="form-group">
        <label>Trạng thái:</label>
        <select name="TrangThai">
          <option value="Chờ duyệt" <?= $row['TrangThai']=='Chờ duyệt'?'selected':'' ?>>Chờ duyệt</option>
          <option value="Đã duyệt" <?= $row['TrangThai']=='Đã duyệt'?'selected':'' ?>>Đã duyệt</option>
          <option value="Từ chối" <?= $row['TrangThai']=='Từ chối'?'selected':'' ?>>Từ chối</option>
        </select>
      </div>

      <!-- BUTTON -->
      <div class="form-buttons">
        <button type="submit" class="btn add">💾 Cập nhật</button>
        <a href="index.php?controller=nghiphep&action=index" class="btn cancel">
          ↩️ Quay lại
        </a>
      </div>

    </form>
  </main>

<?php include 'views/layout/footer.php'; ?>