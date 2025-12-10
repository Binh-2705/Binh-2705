<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Quản lý Nhân viên</title>
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
    <header>
      <h1>👥 Quản lý Nhân viên</h1>
    </header>

    <div class="actions">
     <div class="btn-group">
       <a href="index.php?controller=nhanvien&action=them" class="btn add">➕ Thêm nhân viên</a>
     <a href="index.php?controller=nhanvien&action=exportExcel" class="btn export">📥 Xuất Excel</a>
     </div>

      <form method="GET" action="index.php" style="display:inline;">
        <input type="hidden" name="controller" value="nhanvien">
        <input type="hidden" name="action" value="timkiem">
        <input type="text" name="keyword" placeholder="🔍 Nhập tên hoặc mã nhân viên..." class="search-box" required>
        <button type="submit" class="btn search">Tìm</button>
      </form>
    </div>

    <table class="table">
      <thead>
        <tr>
          <th>Mã NV</th>
          <th>Họ tên</th>
          <th>Giới tính</th>
          <th>Ngày sinh</th>
          <th>Phòng ban</th>
          <th>Chức vụ</th>
          <th>Mức lương</th>
          <th>Thao tác</th>
        </tr>
      </thead>
      <tbody>
        <?php
        if ($result && mysqli_num_rows($result) > 0) {
          while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>
                    <td>{$row['MaNV']}</td>
                    <td>{$row['HoTen']}</td>
                    <td>{$row['GioiTinh']}</td>
                    <td>{$row['NgaySinh']}</td>
                    <td>" . ($row['TenPB'] ?? 'Chưa có') . "</td>
                    <td>{$row['ChucVu']}</td>
                    <td>" . (isset($row['LuongCB']) ? number_format($row['LuongCB'],0,',','.') . "đ" : "Chưa có") . "</td>
                    <td>
                      <a href='index.php?controller=nhanvien&action=sua&manv={$row['MaNV']}' class='btn edit'>✏️ Sửa</a>
                      <a href='index.php?controller=nhanvien&action=xoa&manv={$row['MaNV']}' class='btn delete'
                         onclick='return confirm(\"Bạn có chắc muốn xóa nhân viên này không?\");'>🗑️ Xóa</a>
                    </td>
                  </tr>";
          }
        } else {
          echo "<tr><td colspan='8'>Không có nhân viên nào trong cơ sở dữ liệu.</td></tr>";
        }
        ?>
      </tbody>
    </table>
  </main>
</div>
</body>
</html>

