<?php include 'views/layout/header.php'; ?>
<?php include 'views/layout/sidebar.php'; ?>

  <!-- MAIN -->
  <main class="main-content">

    <header>
      <h1>✏️ Sửa phân công</h1>
    </header>

    <form method="post"
          action="index.php?controller=phancong&action=update"
          class="form-nv">

      <input type="hidden" name="MaQT" value="<?= $phancong['MaQT'] ?>">

      <!-- NHÂN VIÊN -->
      <div class="form-group">
        <label>Nhân viên</label>
        <input type="text"
               value="<?= htmlspecialchars($phancong['HoTen'] ?? $phancong['MaNV']) ?>"
               readonly>
      </div>

      <!-- PHÒNG BAN -->
      <div class="form-group">
        <label>Phòng ban</label>
        <select name="MaPB" required>
          <?php while ($pb = mysqli_fetch_assoc($phongbans)): ?>
            <option value="<?= $pb['MaPB'] ?>"
              <?= $pb['MaPB'] == $phancong['MaPB'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($pb['TenPB']) ?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>

      <!-- CHỨC VỤ -->
      <div class="form-group">
        <label>Chức vụ</label>
        <select name="MaCV" required>
          <?php while ($cv = mysqli_fetch_assoc($chucvus)): ?>
            <option value="<?= $cv['MaCV'] ?>"
              <?= $cv['MaCV'] == $phancong['MaCV'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($cv['TenCV']) ?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>

      <!-- NGÀY BẮT ĐẦU -->
      <div class="form-group">
        <label>Ngày bắt đầu</label>
        <input type="date"
               name="NgayBatDau"
               value="<?= $phancong['NgayBatDau'] ?>"
               required>
      </div>

      <!-- NGÀY KẾT THÚC -->
      <div class="form-group">
        <label>Ngày kết thúc</label>
        <input type="date"
               name="NgayKetThuc"
               value="<?= $phancong['NgayKetThuc'] ?? '' ?>">
      </div>
      <!-- LOẠI ĐIỀU CHUYỂN -->
<div class="form-group">
  <label>Loại điều chuyển</label>
  <select name="LoaiDieuChuyen" required>
    <option value="BO_NHIEM"
      <?= $phancong['LoaiDieuChuyen'] == 'BO_NHIEM' ? 'selected' : '' ?>>
      Bổ nhiệm
    </option>

    <option value="THANG_CHUC"
      <?= $phancong['LoaiDieuChuyen'] == 'THANG_CHUC' ? 'selected' : '' ?>>
      Thăng chức
    </option>

    <option value="DIEU_CHUYEN"
      <?= $phancong['LoaiDieuChuyen'] == 'DIEU_CHUYEN' ? 'selected' : '' ?>>
      Điều chuyển
    </option>

    <option value="GIAM_CHUC"
      <?= $phancong['LoaiDieuChuyen'] == 'GIAM_CHUC' ? 'selected' : '' ?>>
      Giảm chức
    </option>

    <option value="TAI_CO_CAU"
      <?= $phancong['LoaiDieuChuyen'] == 'TAI_CO_CAU' ? 'selected' : '' ?>>
      Tái cơ cấu
    </option>
  </select>
</div>

      <!-- BUTTON -->
      <div class="form-buttons">
        <button type="submit" class="btn edit">💾 Cập nhật</button>
        <a href="index.php?controller=phancong&action=index" class="btn cancel">
          ↩ Quay lại
        </a>
      </div>

    </form>

  </main>
<?php include 'views/layout/footer.php'; ?>
