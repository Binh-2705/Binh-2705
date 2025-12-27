<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Quản lý Chấm công</title>
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
        <li><a href="index.php?controller=tuyendung&action=index">💼 Quản lý tuyển dụng</a></li>
        <li><a href="index.php?controller=daotao&action=index">📚 Quản lý đào tạo</a></li>
        <li><a href="index.php?controller=phanquyen&action=index">🗂 Quản lý đăng nhập – phân quyền</a></li>
        <li><a href="index.php?controller=timkiem&action=index">🔎 Tìm kiếm nâng cao</a></li>
        <li><a href="index.php?controller=dangxuat&action=index">🚪 Đăng xuất</a></li>
      </ul>
    </ul>
  </nav>

  <main class="main-content">
    <header>
      <h1>🕒 Quản lý Chấm công</h1>
    </header>

    <div class="actions">
      <div class="btn-group">
       <a href="index.php?controller=chamcong&action=them" class="btn add">➕ Thêm chấm công</a>
     <a href="index.php?controller=chamcong&action=exportExcel" class="btn export">📥 Xuất Excel</a>
</div>
      <form method="GET" action="index.php" style="display:inline;">
        <input type="hidden" name="controller" value="chamcong">
        <input type="hidden" name="action" value="search">
        <input type="text" name="keyword" placeholder="🔍 Nhập tên hoặc mã NV..." class="search-box" required>
        <button type="submit" class="btn search">Tìm</button>
      </form>
    </div>

    <table class="table">
      <thead>
        <tr>
          <th>Mã CC</th>
          <th>Mã NV</th>
          <th>Họ tên</th>
          <th>Tháng</th>
          <th>Số ngày làm</th>
          <th>Số ngày nghỉ</th>
          <th>Ghi chú</th>
          <th>Thao tác</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($data && $data->num_rows > 0): ?>
          <?php while ($row = $data->fetch_assoc()): ?>
            <tr>
              <td><?= $row['MaCC'] ?></td>
              <td><?= $row['MaNV'] ?></td>
              <td><?= $row['HoTen'] ?></td>
              <td><?= date('m/Y', strtotime($row['Thang'])) ?></td>
              <td><?= $row['SoNgayLam'] ?></td>
              <td><?= $row['SoNgayNghi'] ?></td>
              <td><?= $row['GhiChu'] ?></td>
              <td>
                <a href="index.php?controller=chamcong&action=sua&macc=<?= $row['MaCC'] ?>" class="btn edit">✏️ Sửa</a>
                <a href="index.php?controller=chamcong&action=xoa&macc=<?= $row['MaCC'] ?>" class="btn delete"
                   onclick="return confirm('Bạn có chắc muốn xóa bản chấm công này không?');">🗑️ Xóa</a>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="8">❌ Chưa có dữ liệu chấm công nào.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </main>
</div>
</body>
</html>
