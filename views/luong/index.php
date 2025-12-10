<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>💰 Quản lý Lương</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
  <nav class="sidebar">
    <h2>HỆ THỐNG <br> QUẢN LÝ NHÂN SỰ</h2>
    <ul>
      <ul>
        <li><a href="index.php?controller=home&action=index" class="active">🏠 Trang chủ</a></li>
        <li><a href="index.php?controller=nhanvien&action=index">👥 Quản lý nhân viên</a></li>
        <li><a href="index.php?controller=phongban&action=index">🏢 Quản lý phòng ban</a></li>
        <li><a href="index.php?controller=luong&action=index">💰 Quản lý lương</a></li>
        <li><a href="index.php?controller=chamcong&action=index">🕒 Quản lý chấm công</a></li>
        <li><a href="index.php?controller=hopdong&action=index">📄 Quản lý hợp đồng</a></li>
        <li><a href="index.php?controller=nghiphep&action=index">📆 Quản lý nghỉ phép</a></li>
        <li><a href="index.php?controller=khenthuong&action=index">🏅 Khen thưởng - Kỷ luật</a></li>
        <li><a href="index.php?controller=thongke&action=index">📊 Thống kê - Báo cáo</a></li>
        <li><a href="index.php?controller=chucvu&action=index">🙍‍♂️ Quản lý chức vụ</a></li>
        <li><a href="index.php?controller=hoso&action=index">👤 Hồ sơ cá nhân</a></li>
        <li><a href="">💼 Quản lý tuyển dụng</a></li>
        <li><a href="">📚 Quản lý đào tạo</a></li>
        <li><a href="">🗂 Quản lý đăng nhập – phân quyền</a></li>
        <li><a href="index.php?controller=timkiem&action=index">🔎 Tìm kiếm nâng cao</a></li>
        <li><a href="index.php?controller=dangxuat&action=index">🚪 Đăng xuất</a></li>
      </ul>
    </ul>
  </nav>

  <main class="main-content">
    <header><h1>💰 Quản lý Lương</h1></header>

    <div class="actions">
     <div class="btn-group">
       <a href="index.php?controller=luong&action=them" class="btn add">➕ Thêm lương</a>
     <a href="index.php?controller=luong&action=exportExcel" class="btn export">📥 Xuất Excel</a>
     </div>

      <form method="GET" action="index.php" style="display:inline;">
        <input type="hidden" name="controller" value="luong">
        <input type="hidden" name="action" value="timkiem">
        <input type="text" name="keyword" placeholder="🔍 Tìm theo mã NV..." class="search-box" required>
        <button type="submit" class="btn search">Tìm</button>
      </form>
    </div>

    <table class="table">
      <thead>
        <tr>
          <th>Mã lương</th>
          <th>Mã NV</th>
          <th>Họ tên</th>
          <th>Tháng</th>
          <th>Lương cơ bản</th>
          <th>Phụ cấp</th>
          <th>Thưởng</th>
          <th>Khấu trừ</th>
          <th>Tổng lương</th>
          <th>Thao tác</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($luong)): ?>
          <?php foreach ($luong as $row): ?>
            <tr>
              <td><?= $row['MaLuong'] ?></td>
              <td><?= $row['MaNV'] ?></td>
              <td><?= $row['HoTen'] ?></td>
              <td><?= $row['Thang'] ?></td>
              <td><?= number_format($row['LuongCB'], 0, ',', '.') ?></td>
              <td><?= number_format($row['PhuCap'], 0, ',', '.') ?></td>
              <td><?= number_format($row['Thuong'], 0, ',', '.') ?></td>
              <td><?= number_format($row['KhauTru'], 0, ',', '.') ?></td>
              <td><b><?= number_format($row['TongLuong'], 0, ',', '.') ?></b></td>
              <td>
                <a href="index.php?controller=luong&action=sua&maluong=<?= $row['MaLuong'] ?>" class="btn edit">✏️ Sửa</a>

                <a href="index.php?controller=luong&action=delete&maluong=<?= $row['MaLuong'] ?>" class="btn delete"
                   onclick="return confirm('Xóa bản lương này?');">🗑️ Xóa</a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="10">Chưa có dữ liệu lương.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </main>
</div>
</body>
</html>
