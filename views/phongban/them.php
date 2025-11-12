<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Thêm Phòng ban</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
  <nav class="sidebar">
    <h2>HỆ THỐNG<br>QUẢN LÝ NHÂN SỰ</h2>
    <ul>
      <li><a href="index.php">🏠 Trang chủ</a></li>
      <li><a href="index.php?controller=nhanvien&action=index" >👥 Quản lý nhân viên</a></li>
      <li><a href="index.php?controller=phongban&action=index" class="active">🏢 Quản lý phòng ban</a></li>
    </ul>
  </nav>

  <main class="main-content">
    <header><h1>➕ Thêm phòng ban mới</h1></header>

    <form action="index.php?controller=phongban&action=luuThem" method="POST" class="form-nv">
      <div class="form-group">
        <label for="mapb">Mã phòng ban:</label>
        <input type="text" name="mapb" id="mapb" required>
      </div>
      <div class="form-group">
        <label for="tenpb">Tên phòng ban:</label>
        <input type="text" name="tenpb" id="tenpb" required>
      </div>
      <div class="form-group">
        <label for="mota">Mô tả:</label>
        <textarea name="mota" id="mota" rows="4"></textarea>
      </div>

      <div class="form-buttons">
        <button type="submit" name="save" class="btn add">💾 Lưu</button>
        <a href="index.php?controller=phongban&action=index" class="btn cancel">↩️ Quay lại</a>
      </div>
    </form>

  </main>
</div>
</body>
</html>
