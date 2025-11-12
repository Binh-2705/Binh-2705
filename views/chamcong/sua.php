<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Sửa Chấm Công</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
  <main class="main-content">
    <h1>✏️ Sửa Chấm Công</h1>

    <form method="POST" action="index.php?controller=chamcong&action=luuSua" class="form-nv">
      <input type="hidden" name="MaCC" value="<?= $row['MaCC'] ?>">

      <div class="form-group">
        <label>Nhân viên:</label>
        <select name="MaNV" required>
          <option value="">-- Chọn nhân viên --</option>
          <?php while ($r = $nhanvien->fetch_assoc()): ?>
            <option value="<?= $r['MaNV'] ?>" <?= ($r['MaNV'] == $row['MaNV']) ? "selected" : "" ?>>
              <?= $r['MaNV'] . " - " . $r['HoTen'] ?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>

      <div class="form-group">
        <label>Tháng:</label>
        <input type="month" name="Thang" value="<?= $row['Thang'] ?>" required>
      </div>

      <div class="form-group">
        <label>Số ngày làm:</label>
        <input type="number" name="SoNgayLam" min="0" max="31" value="<?= $row['SoNgayLam'] ?>" required>
      </div>

      <div class="form-group">
        <label>Số ngày nghỉ:</label>
        <input type="number" name="SoNgayNghi" min="0" max="31" value="<?= $row['SoNgayNghi'] ?>" required>
      </div>

      <div class="form-group">
        <label>Ghi chú:</label>
        <textarea name="GhiChu"><?= $row['GhiChu'] ?></textarea>
      </div>

      <div class="form-buttons">
        <button type="submit" class="btn add">💾 Lưu</button>
        <a href="index.php?controller=chamcong&action=index" class="btn cancel">↩️ Quay lại</a>
      </div>
    </form>
  </main>
</div>
</body>
</html>
