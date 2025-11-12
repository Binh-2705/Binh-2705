<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa phòng ban</title>
     <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <nav class="sidebar">
         <h2>HỆ THỐNG<br>QUẢN LÝ NHÂN SỰ</h2>
    <ul>
     <li><a href="#">🏠 Trang chủ</a></li>
       <li><a href="index.php?controller=nhanvien&action=index">🏠 Trang chủ</a></li>
        <li><a href="index.php?controller=nhanvien&action=index" class="active">👥 Quản lý nhân viên</a></li>
        <li><a href="index.php?controller=phongban&action=index">🏢 Quản lý phòng ban</a></li>
        <li><a href="index.php?controller=luong&action=index">💰 Quản lý lương</a></li>
        <li><a href="index.php?controller=chamcong&action=index">🕒 Quản lý chấm công</a></li>
        <li><a href="index.php?controller=hopdong&action=index">📄 Quản lý hợp đồng</a></li>
        <li><a href="index.php?controller=nghiphep&action=index">📆 Quản lý nghỉ phép</a></li>
        <li><a href="index.php?controller=khenthuong&action=index">🏅 Khen thưởng - Kỷ luật</a></li>
        <li><a href="index.php?controller=thongke&action=index">📊 Thống kê - Báo cáo</a></li>
        <li><a href="index.php?controller=hoso&action=index">👤 Hồ sơ cá nhân</a></li>
        <li><a href="index.php?controller=timkiem&action=index">🔎 Tìm kiếm nâng cao</a></li>
        <li><a href="index.php?controller=dangxuat&action=index">🚪 Đăng xuất</a></li>
    </ul>
        </nav>
       <main class="main-content">
    <header><h1>✏️ Sửa thông tin phòng ban</h1></header>

    <form action="index.php?controller=phongban&action=luuSua" method="POST" class="form-nv">
      <input type="hidden" name="mapb" value="<?php echo $phongban['MaPB']; ?>">

      <div class="form-group">
        <label>Mã phòng ban:</label>
        <input type="text" value="<?php echo $phongban['MaPB']; ?>" disabled>
      </div>

      <div class="form-group">
        <label for="tenpb">Tên phòng ban:</label>
        <input type="text" name="tenpb" id="tenpb" value="<?php echo $phongban['TenPB']; ?>" required>
      </div>

      <div class="form-group">
        <label for="mota">Mô tả:</label>
        <textarea name="mota" id="mota" rows="4"><?php echo $phongban['MoTa']; ?></textarea>
      </div>

      <div class="form-buttons">
        <button type="submit" class="btn edit">💾 Cập nhật</button>
        <a href="index.php?controller=phongban&action=index" class="btn cancel">↩️ Quay lại</a>
      </div>
    </form>
  </main>

    </div>
    
</body>
</html>