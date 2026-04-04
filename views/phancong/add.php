<?php include 'views/layout/header.php'; ?>
<?php include 'views/layout/sidebar.php'; ?>

  <!-- MAIN -->
  <main class="main-content">

    <header>
      <h1>➕ Phân công nhân viên</h1>
    </header>

    <form method="post"
          action="index.php?controller=phancong&action=store"
          class="form-nv">

      <!-- NHÂN VIÊN -->
      <div class="form-group">
        <label>Nhân viên</label>
        <select name="MaNV" required>
          <option value="">-- Chọn nhân viên --</option>
          <?php while ($nv = mysqli_fetch_assoc($nhanviens)): ?>
            <option value="<?= $nv['MaNV'] ?>">
              <?= htmlspecialchars($nv['HoTen']) ?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>

      <!-- PHÒNG BAN -->
      <div class="form-group">
        <label>Phòng ban</label>
        <select name="MaPB" required>
          <option value="">-- Chọn phòng ban --</option>
          <?php while ($pb = mysqli_fetch_assoc($phongbans)): ?>
            <option value="<?= $pb['MaPB'] ?>">
              <?= htmlspecialchars($pb['TenPB']) ?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>

      <!-- CHỨC VỤ -->
      <div class="form-group">
        <label>Chức vụ</label>
        <select name="MaCV" required>
          <option value="">-- Chọn chức vụ --</option>
          <?php while ($cv = mysqli_fetch_assoc($chucvus)): ?>
            <option value="<?= $cv['MaCV'] ?>">
              <?= htmlspecialchars($cv['TenCV']) ?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>

      <!-- NGÀY BẮT ĐẦU -->
      <div class="form-group">
        <label>Ngày bắt đầu</label>
        <input type="date" name="NgayBatDau" required>
      </div>

      <!-- NGÀY KẾT THÚC -->
      <div class="form-group">
        <label>Ngày kết thúc</label>
        <input type="date" name="NgayKetThuc">
      </div>

      <!-- LÝ DO -->
      <div class="form-group">
        <label>Lý do phân công</label>
        <textarea name="LyDoThayDoi"
                  rows="3"
                  style="width:100%;padding:8px 10px;border-radius:6px;border:1px solid #ccc"
                  placeholder="Ví dụ: Bổ nhiệm mới / Điều chuyển nội bộ"></textarea>
      </div>
      <div class="form-group">
    
</div>

<!-- THÊM ĐOẠN NÀY -->
<div class="form-group">
    <label>Loại điều chuyển</label>
    <select name="LoaiDieuChuyen" required>
        <option value="BO_NHIEM">Bổ nhiệm</option>
        <option value="THANG_CHUC">Thăng chức</option>
        <option value="DIEU_CHUYEN" selected>Điều chuyển</option>
        <option value="GIAM_CHUC">Giảm chức</option>
        <option value="TAI_CO_CAU">Tái cơ cấu</option>
    </select>
</div>
      <!-- BUTTONS -->
      <div class="form-buttons">
        <button type="submit" class="btn add">💾 Lưu</button>
        <a href="index.php?controller=phancong&action=index" class="btn cancel">
          ↩ Quay lại
        </a>
      </div>

    </form>

  </main>
<?php include 'views/layout/footer.php'; ?>
