<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Thêm Chấm Công</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="container">
    <nav class="sidebar">
      <h2>HỆ THỐNG <br> QUẢN LÝ NHÂN SỰ</h2>
      <ul>
        <li><a href="index.php?controller=nhanvien&action=index">👥 Nhân viên</a></li>
        <li><a href="index.php?controller=phongban&action=index">🏢 Quản lý phòng ban</a></li>
        <li><a href="index.php?controller=chamcong&action=index" class="active">🕒 Chấm công</a></li>
      </ul>
    </nav>

    <main class="main-content">
      <header>
        <h1>➕ Thêm Chấm Công</h1>
      </header>

      <form method="POST" action="index.php?controller=chamcong&action=luu" class="form-nv">
        <div class="form-group">
          <label>Mã chấm công:</label>
          <input type="text" name="MaCC" value="<?= $newMaCC ?>" readonly>
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
          <label>Tháng:</label>
          <input type="month" name="Thang" required>
        </div>

        <div class="form-group">
          <label>Số ngày làm:</label>
          <input type="number" name="SoNgayLam" min="0" max="31" value="0" required>
        </div>

        <div class="form-group">
          <label>Số ngày nghỉ:</label>
          <input type="number" name="SoNgayNghi" min="0" max="31" value="0" required>
        </div>

        <div class="form-group">
          <label>Ghi chú:</label>
          <textarea name="GhiChu"></textarea>
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
