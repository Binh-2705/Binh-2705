<?php include 'views/layout/header.php'; ?>
<?php include 'views/layout/sidebar.php'; ?>

  <!-- ===== MAIN CONTENT ===== -->
  <main class="main-content">

    <header>
      <h1>✏️ Sửa thông tin phòng ban</h1>
    </header>

    <form action="index.php?controller=phongban&action=luuSua"
          method="POST"
          class="form-nv">

      <!-- giữ mã phòng ban để submit -->
      <input type="hidden" name="mapb" value="<?php echo $phongban['MaPB']; ?>">

      <div class="form-group">
        <label>Mã phòng ban</label>
        <input type="text"
               value="<?php echo $phongban['MaPB']; ?>"
               disabled>
      </div>

      <div class="form-group">
        <label for="tenpb">Tên phòng ban</label>
        <input type="text"
               id="tenpb"
               name="tenpb"
               value="<?php echo $phongban['TenPB']; ?>"
               maxlength="100"
               required>
      </div>

      <div class="form-group">
        <label for="mota">Mô tả</label>
        <textarea id="mota"
                  name="mota"
                  rows="4"
                  placeholder="Mô tả chức năng phòng ban..."><?php echo $phongban['MoTa']; ?></textarea>
      </div>

      <div class="form-buttons">
        <button type="submit" class="btn edit">💾 Cập nhật</button>
        <a href="index.php?controller=phongban&action=index" class="btn cancel">↩️ Quay lại</a>
      </div>

    </form>

  </main>
<?php include 'views/layout/footer.php'; ?>