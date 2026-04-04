<?php include 'views/layout/header.php'; ?>
<?php include 'views/layout/sidebar.php'; ?>

  <!-- ===== MAIN ===== -->
  <main class="main-content">

    <header>
      <h1>➕ Thêm phòng ban mới</h1>
    </header>

    <form action="index.php?controller=phongban&action=luuThem"
          method="POST"
          class="form-nv">

      <div class="form-group">
        <label for="tenpb">Tên phòng ban <span class="required">*</span></label>
        <input
          type="text"
          id="tenpb"
          name="tenpb"
          placeholder="VD: Phòng Nhân sự"
          maxlength="100"
          required>
      </div>

      <div class="form-group">
        <label for="mota">Mô tả</label>
        <textarea
          id="mota"
          name="mota"
          rows="4"
          placeholder="Mô tả chức năng của phòng ban..."></textarea>
      </div>

      <div class="form-buttons">
        <button type="submit" class="btn add">💾 Lưu</button>
        <a href="index.php?controller=phongban&action=index" class="btn cancel">↩️ Quay lại</a>
      </div>

    </form>

  </main>
<?php include 'views/layout/footer.php'; ?>