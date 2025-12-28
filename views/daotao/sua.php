<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Sửa khóa học đào tạo</title>
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
        <h1>✏️ Sửa khóa học đào tạo</h1>
      </header>

      <form method="POST" class="form-edit">
        <input type="hidden" name="MaDT" value="<?= $daotao['MaDT'] ?>">

        <div class="form-group">
          <label for="TenKhoaHoc">Tên khóa học:</label>
          <input type="text" name="TenKhoaHoc" id="TenKhoaHoc" value="<?= $daotao['TenKhoaHoc'] ?>" required>
        </div>

        <div class="form-group">
          <label for="NoiDung">Nội dung:</label>
          <textarea name="NoiDung" id="NoiDung" rows="3"><?= $daotao['NoiDung'] ?></textarea>
        </div>

        <div class="form-group">
          <label for="NgayBatDau">Ngày bắt đầu:</label>
          <input type="date" name="NgayBatDau" id="NgayBatDau" value="<?= $daotao['NgayBatDau'] ?>">
        </div>

        <div class="form-group">
          <label for="NgayKetThuc">Ngày kết thúc:</label>
          <input type="date" name="NgayKetThuc" id="NgayKetThuc" value="<?= $daotao['NgayKetThuc'] ?>">
        </div>

        <div class="form-group">
          <label for="GiangVien">Giảng viên:</label>
          <input type="text" name="GiangVien" id="GiangVien" value="<?= $daotao['GiangVien'] ?>">
        </div>

        <div class="form-group">
          <label for="DiaDiem">Địa điểm:</label>
          <input type="text" name="DiaDiem" id="DiaDiem" value="<?= $daotao['DiaDiem'] ?>">
        </div>

        <div class="form-group">
          <label for="ChiPhi">Chi phí:</label>
          <input type="number" name="ChiPhi" id="ChiPhi" value="<?= $daotao['ChiPhi'] ?>">
        </div>

        <div class="form-group">
          <label for="GhiChu">Ghi chú:</label>
          <input type="text" name="GhiChu" id="GhiChu" value="<?= $daotao['GhiChu'] ?>">
        </div>

        <div class="form-actions">
          <button type="submit" class="btn save">💾 Cập nhật</button>
          <a href="index.php?controller=daotao&action=index" class="btn back">↩️ Quay lại</a>
        </div>
      </form>
    </main>
  </div>
</body>
</html>
