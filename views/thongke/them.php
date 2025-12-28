<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>➕ Thêm báo cáo</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
  <nav class="sidebar">
    <h2>QUẢN LÝ NHÂN SỰ</h2>
    <ul>
      <li><a href="index.php?controller=thongke&action=index" class="active">📊 Thống kê - Báo cáo</a></li>
    </ul>
  </nav>

  <main class="main-content">
    <header><h1>➕ Thêm báo cáo</h1></header>
    <form method="POST" action="index.php?controller=thongke&action=luu" class="form">
      <div class="form-group">
        <label>Tiêu đề:</label>
        <input type="text" name="tieu_de" required>
      </div>
      <div class="form-group">
        <label>Loại báo cáo:</label>
        <select name="loai" required>
          <option value="nhanvien">Nhân viên</option>
          <option value="chamcong">Chấm công</option>
          <option value="luong">Lương</option>
          <option value="daotao">Đào tạo</option>
        </select>
      </div>
      <div class="form-group">
        <label>Tháng (YYYY-MM):</label>
        <input type="text" name="thang" placeholder="2025-12">
      </div>
      <div class="form-group">
        <label>Mã phòng ban / Mã CC:</label>
        <input type="text" name="ma_pb">
      </div>
      <div class="form-group">
        <label>Nội dung:</label>
        <textarea name="noi_dung" rows="5"></textarea>
      </div>
      <div class="form-actions">
        <button type="submit" class="btn save">💾 Lưu</button>
        <a href="index.php?controller=thongke&action=index" class="btn back">↩️ Quay lại</a>
      </div>
    </form>
  </main>
</div>
</body>
</html>
