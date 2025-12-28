<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Quản lý Phòng ban</title>
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
                <li><a href="index.php?controller=phongban&action=index" class="active">🏢 Quản lý phòng ban</a></li>
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
                <li><a href="index.php?controller=taikhoan&action=index">🗂 Quản lý tài khoản</a></li>
                <li><a href="index.php?controller=timkiem&action=index">🔎 Tìm kiếm nâng cao</a></li>
                <li><a href="index.php?controller=dangnhap&action=dangxuat">🚪 Đăng xuất</a></li>
            </ul>
        </ul>
    </nav>


    <main class="main-content">
      <header>
        <h1>🏢 Quản lý Phòng ban</h1>
      </header>

     <div class="actions">
    <a href="index.php?controller=phongban&action=them" class="btn add">➕ Thêm phòng ban</a>
    <form method="GET" action="index.php" style="display:inline-block;">
        <input type="hidden" name="controller" value="phongban">
        <input type="hidden" name="action" value="timkiem">
        <input type="text" name="keyword" placeholder="🔍 Tìm kiếm phòng ban..." class="search-box"
               value="<?php echo isset($_GET['keyword']) ? $_GET['keyword'] : ''; ?>">
        <button type="submit" class="btn search">Tìm</button>
    </form>
</div>

      <table class="table">
    <thead>
        <tr>
            <th>Mã PB</th>
            <th>Tên phòng ban</th>
            <th>Mô tả</th>
            <th>Thao tác</th>
        </tr>
    </thead>
    <tbody>
        <?php
        if ($phongbans && $phongbans->num_rows > 0) {
            while ($row = $phongbans->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['MaPB']}</td>
                        <td>{$row['TenPB']}</td>
                        <td>{$row['MoTa']}</td>
                        <td>
                            <a href='index.php?controller=phongban&action=sua&mapb={$row['MaPB']}' class='btn edit'>✏️ Sửa</a>
                            <a href='index.php?controller=phongban&action=xoa&mapb={$row['MaPB']}' class='btn delete' onclick='return confirm(\"Bạn có chắc muốn xóa phòng ban này không?\");'>🗑️ Xóa</a>
                        </td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='4'>Không có dữ liệu</td></tr>";
        }
        ?>
    </tbody>
</table>
    </main>
  </div>
</body>
</html>
