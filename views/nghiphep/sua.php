<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Sửa Nghỉ phép</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">

  <!-- SIDEBAR -->
  <nav class="sidebar">
    <h2>HỆ THỐNG <br> QUẢN LÝ NHÂN SỰ</h2>
    <ul>
      <li><a href="index.php?controller=home&action=index">🏠 Trang chủ</a></li>
      <li><a href="index.php?controller=nhanvien&action=index">👥 Quản lý nhân viên</a></li>
      <li><a href="index.php?controller=phongban&action=index">🏢 Quản lý phòng ban</a></li>
      <li><a href="index.php?controller=luong&action=index">💰 Quản lý lương</a></li>
      <li><a href="index.php?controller=chamcong&action=index">🕒 Quản lý chấm công</a></li>
      <li><a href="index.php?controller=hopdong&action=index">📄 Quản lý hợp đồng</a></li>
      <li><a href="index.php?controller=nghiphep&action=index" class="active">📆 Quản lý nghỉ phép</a></li>
      <li><a href="index.php?controller=dangxuat&action=index">🚪 Đăng xuất</a></li>
    </ul>
  </nav>

  <!-- MAIN -->
  <main class="main-content">
    <header>
      <h1>✏️ Sửa Nghỉ phép</h1>
    </header>

    <form method="post"
          action="index.php?controller=nghiphep&action=luuSua"
          class="form-nv">

      <!-- Mã nghỉ phép (KHÔNG cho sửa) -->
      <div class="form-group">
        <label>Mã nghỉ phép:</label>
        <input type="text" name="MaNP"
               value="<?= $row['MaNP'] ?>"
               readonly>
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

      <!-- Lý do -->
      <div class="form-group">
        <label>Lý do:</label>
        <input type="text" name="LyDo"
               value="<?= $row['LyDo'] ?>">
      </div>

      <!-- Trạng thái -->
      <div class="form-group">
        <label>Trạng thái:</label>
        <select name="TrangThai">
          <option value="Chờ duyệt" <?= $row['TrangThai']=='Chờ duyệt'?'selected':'' ?>>Chờ duyệt</option>
          <option value="Đã duyệt" <?= $row['TrangThai']=='Đã duyệt'?'selected':'' ?>>Đã duyệt</option>
          <option value="Từ chối" <?= $row['TrangThai']=='Từ chối'?'selected':'' ?>>Từ chối</option>
        </select>
      </div>

      <div class="form-buttons">
        <button class="btn add">💾 Cập nhật</button>
        <a href="index.php?controller=nghiphep&action=index"
           class="btn cancel">↩️ Quay lại</a>
      </div>

    </form>
  </main>

</div>
</body>
</html>
