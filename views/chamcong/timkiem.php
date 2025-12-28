<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tìm kiếm Chấm Công</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
  <nav class="sidebar">
     <h2>HỆ THỐNG <br> QUẢN LÝ NHÂN SỰ</h2>
        <ul>
            <ul>
                <li><a href="index.php?controller=home&action=index" >🏠 Trang chủ</a></li>
                <li><a href="index.php?controller=nhanvien&action=index">👥 Quản lý nhân viên</a></li>
                <li><a href="index.php?controller=phongban&action=index">🏢 Quản lý phòng ban</a></li>
                <li><a href="index.php?controller=luong&action=index">💰 Quản lý lương</a></li>
                <li><a href="index.php?controller=chamcong&action=index" class="active">🕒 Quản lý chấm công</a></li>
                <li><a href="index.php?controller=hopdong&action=index">📄 Quản lý hợp đồng</a></li>
                <li><a href="index.php?controller=nghiphep&action=index">📆 Quản lý nghỉ phép</a></li>
                <li><a href="index.php?controller=khenthuong&action=index">🏅 Khen thưởng - Kỷ luật</a></li>
                <li><a href="index.php?controller=thongke&action=index">📊 Thống kê - Báo cáo</a></li>
                <li><a href="index.php?controller=chucvu&action=index">🙍‍♂️ Quản lý chức vụ</a></li>
                <li><a href="index.php?controller=hoso&action=index">👤 Hồ sơ cá nhân</a></li>
                <li><a href="index.php?controller=tuyendung&action=index">💼 Quản lý tuyển dụng</a></li>
                <li><a href="index.php?controller=daotao&action=index">📚 Quản lý đào tạo</a></li>
                <li><a href="index.php?controller=taikhoan&action=index">🗂 Quản lý tài khoản</a></li>
                <li><a href="index.php?controller=timkiem&action=index">🔎 Tìm kiếm nâng cao</a></li>
               <li><a href="index.php?controller=dangnhap&action=dangxuat">🚪 Đăng xuất</a></li>
            </ul>
        </ul>
  </nav>

  <main class="main-content">
    <header>
      <h1>🔍 Kết quả tìm kiếm: "<?= htmlspecialchars($keyword) ?>"</h1>
    </header>

    <div class="actions">
      <a href="index.php?controller=chamcong&action=index" class="btn cancel">↩️ Quay lại danh sách</a>
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
        <?php if (empty($data)) : ?>
            <tr><td colspan="8">❌ Không tìm thấy bản ghi nào.</td></tr>
        <?php else : ?>
            <?php foreach ($data as $row) : ?>
            <tr>
                <td><?= $row['MaCC'] ?></td>
                <td><?= $row['MaNV'] ?></td>
                <td><?= $row['HoTen'] ?></td>
                <td><?= $row['Thang'] ?></td>
                <td><?= $row['SoNgayLam'] ?></td>
                <td><?= $row['SoNgayNghi'] ?></td>
                <td><?= $row['GhiChu'] ?></td>
                <td>
                  <a href="index.php?controller=chamcong&action=them&macc=<?= $row['MaCC'] ?>" class="btn edit">✏️ Sửa</a>
                  <a href="index.php?controller=chamcong&action=xoa&macc=<?= $row['MaCC'] ?>"
                     onclick='return confirm("Bạn có chắc muốn xóa bản chấm công này không?");' class="btn delete">🗑️ Xóa</a>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </main>
</div>
</body>
</html>
