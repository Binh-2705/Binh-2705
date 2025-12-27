<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Thêm Nghỉ phép</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">

  <main class="main-content">
    <header>
      <h1>➕ Thêm Nghỉ phép</h1>
    </header>

    <form method="post"
          action="index.php?controller=nghiphep&action=luu"
          class="form-nv">

      <div class="form-group">
        <label>Mã nghỉ phép:</label>
        <input type="text" name="MaNP" required>
      </div>

      <div class="form-group">
      <label>Nhân viên:</label>
      <select name="MaNV" required>
          <option value="">-- Chọn nhân viên --</option>
          <?php while ($r = $nhanvien->fetch_assoc()): ?>
              <option value="<?= $r['MaNV'] ?>"><?= $r['MaNV'] . " - " . $r['HoTen'] ?></option>
          <?php endwhile; ?>
      </select>
      </div>

      <div class="form-group">
        <label>Từ ngày:</label>
        <input type="date" name="TuNgay" required>
      </div>

      <div class="form-group">
        <label>Đến ngày:</label>
        <input type="date" name="DenNgay" required>
      </div>

      <div class="form-group">
        <label>Lý do:</label>
        <input type="text" name="LyDo">
      </div>

      <input type="hidden" name="NgayDangKy" value="<?= date('Y-m-d') ?>">

      <div class="form-buttons">
        <button class="btn add">💾 Lưu</button>
        <a href="index.php?controller=nghiphep" class="btn cancel">↩️ Quay lại</a>
      </div>
    </form>
  </main>

</div>
</body>
</html>
