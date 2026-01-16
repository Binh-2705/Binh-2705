<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Thêm khóa học đào tạo</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="container">

    <nav class="sidebar">
      <h2>QUẢN LÝ NHÂN SỰ</h2>
      <ul>
        <li><a href="index.php?controller=home&action=index">🏠 Trang chủ</a></li>
        <li><a href="index.php?controller=nhanvien&action=index">👥 Quản lý nhân viên</a></li>
        <li><a href="index.php?controller=phongban&action=index">🏢 Quản lý phòng ban</a></li>
        <li><a href="index.php?controller=daotao&action=index" class="active">📚 Quản lý đào tạo</a></li>
      </ul>
    </nav>

    <main class="main-content">
      <header>
        <h1> Thêm khóa học đào tạo</h1>
      </header>

      <form method="POST" class="form-add">
        <div class="form-group">
          <label for="MaDT">Mã khóa học:</label>
          <input type="text" name="MaDT" id="MaDT" required>
        </div>

        <div class="form-group">
          <label for="TenKhoaHoc">Tên khóa học:</label>
          <input type="text" name="TenKhoaHoc" id="TenKhoaHoc" required>
        </div>

        <div class="form-group">
          <label for="NoiDung">Nội dung:</label>
          <textarea name="NoiDung" id="NoiDung" rows="3"></textarea>
        </div>

        <div class="form-group">
          <label for="NgayBatDau">Ngày bắt đầu:</label>
          <input type="date" name="NgayBatDau" id="NgayBatDau">
        </div>

        <div class="form-group">
          <label for="NgayKetThuc">Ngày kết thúc:</label>
          <input type="date" name="NgayKetThuc" id="NgayKetThuc">
        </div>

        <div class="form-group">
          <label for="GiangVien">Giảng viên:</label>
          <input type="text" name="GiangVien" id="GiangVien">
        </div>

        <div class="form-group">
          <label for="DiaDiem">Địa điểm:</label>
          <input type="text" name="DiaDiem" id="DiaDiem">
        </div>

        <div class="form-group">
          <label for="ChiPhi">Chi phí:</label>
          <input type="number" name="ChiPhi" id="ChiPhi">
        </div>

        <div class="form-group">
          <label for="GhiChu">Ghi chú:</label>
          <input type="text" name="GhiChu" id="GhiChu">
        </div>

        <div class="form-actions">
          <button type="submit" class="btn save">💾 Lưu</button>
          <a href="index.php?controller=daotao&action=index" class="btn back">↩️ Quay lại</a>
        </div>
      </form>
    </main>
  </div>
</body>
</html>
