<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Quản lý Nghỉ phép</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">

  <!-- SIDEBAR -->
  <nav class="sidebar">
    <h2>HỆ THỐNG <br> QUẢN LÝ NHÂN SỰ</h2>
        <ul>
            <ul>
                <li><a href="index.php?controller=home&action=index" >🏠 Trang chủ</a></li>
                <li><a href="index.php?controller=nhanvien&action=index">👥 Quản lý nhân viên</a></li>
                <li><a href="index.php?controller=phongban&action=index">🏢 Quản lý phòng ban</a></li>
                <li><a href="index.php?controller=luong&action=index">💰 Quản lý lương</a></li>
                <li><a href="index.php?controller=chamcong&action=index">🕒 Quản lý chấm công</a></li>
                <li><a href="index.php?controller=hopdong&action=index">📄 Quản lý hợp đồng</a></li>
                <li><a href="index.php?controller=nghiphep&action=index" class="active">📆 Quản lý nghỉ phép</a></li>
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

  <!-- MAIN -->
  <main class="main-content">
    <header>
      <h1>📆 Quản lý Nghỉ phép</h1>
    </header>

    <div class="actions">
      <a href="index.php?controller=nghiphep&action=them" class="btn add">
        ➕ Thêm nghỉ phép
      </a>

      <form method="post" action="index.php?controller=nghiphep&action=timkiem">
        <input type="text" name="keyword" class="search-box" placeholder="🔍 Nhập mã NV..." required>
        <button class="btn search">Tìm</button>
      </form>
    </div>

    <table class="table">
      <thead>
        <tr>
          <th>Mã NP</th>
          <th>Mã NV</th>
          <th>Từ ngày</th>
          <th>Đến ngày</th>
          <th>Lý do</th>
          <th>Trạng thái</th>
          <th>Thao tác</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($data && $data->num_rows > 0): ?>
          <?php while ($row = $data->fetch_assoc()): ?>
            <tr>
              <td><?= $row['MaNP'] ?></td>
              <td><?= $row['MaNV'] ?></td>
              <td><?= $row['TuNgay'] ?></td>
              <td><?= $row['DenNgay'] ?></td>
              <td><?= $row['LyDo'] ?></td>
              <td><?= $row['TrangThai'] ?></td>
             <td>
        <?php if ($row['TrangThai'] == 'Chờ duyệt'): ?>

            <a href="index.php?controller=nghiphep&action=sua&id=<?= $row['MaNP'] ?>"
            class="btn edit">
            ✏️ Sửa
            </a>

            <a href="index.php?controller=nghiphep&action=duyet&id=<?= $row['MaNP'] ?>"
            class="btn add"
            onclick="return confirm('Duyệt đơn nghỉ phép này?')">
            ✅ Duyệt
            </a>

            <a href="index.php?controller=nghiphep&action=tuchoi&id=<?= $row['MaNP'] ?>"
            class="btn delete"
            onclick="return confirm('Từ chối đơn nghỉ phép này?')">
            ❌ Từ chối
            </a>

        <?php else: ?>
            <span>—</span>
        <?php endif; ?>
        </td>

            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr>
            <td colspan="7">❌ Chưa có dữ liệu nghỉ phép</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </main>
</div>
</body>
</html>
