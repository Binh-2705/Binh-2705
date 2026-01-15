<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Quản lý Đào tạo</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="container">

    <nav class="sidebar">
      <h2>HỆ THỐNG<br>QUẢN LÝ NHÂN SỰ</h2>
      <ul>
        <li><a href="index.php?controller=home&action=index">🏠 Trang chủ</a></li>
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
        <li><a href="index.php?controller=daotao&action=index" class="active">📚 Quản lý đào tạo</a></li>
        <li><a href="index.php?controller=daotao&action=giangvien">👨‍🏫 Quản lý giảng viên</a></li>
        <li><a href="index.php?controller=daotao&action=baocao">📊 Báo cáo đào tạo</a></li>
        <li><a href="index.php?controller=taikhoan&action=index">🗂 Quản lý tài khoản</a></li>
        
        <li><a href="index.php?controller=dangxuat&action=index">🚪 Đăng xuất</a></li>
      </ul>
    </nav>

    <main class="main-content">
      <header>
        <h1>📚 Quản lý đào tạo</h1>
        
        <!-- Dashboard thống kê nhanh -->
        <div class="dashboard" style="margin-bottom: 25px;">
          <div class="card" onclick="location.href='index.php?controller=daotao&action=index'" style="cursor:pointer;">
            <h3>Khóa học</h3>
            <p style="font-size: 32px; font-weight: bold; color: #3b82f6;">
              <?= $countKhoaHoc ?? 0 ?>
            </p>
          </div>
          <div class="card" onclick="location.href='index.php?controller=daotao&action=giangvien'" style="cursor:pointer;">
            <h3>Giảng viên</h3>
            <p style="font-size: 32px; font-weight: bold; color: #10b981;">
              <?= $countGiangVien ?? 0 ?>
            </p>
          </div>
          <div class="card" onclick="location.href='index.php?controller=daotao&action=baocao'" style="cursor:pointer;">
            <h3>Học viên</h3>
            <p style="font-size: 32px; font-weight: bold; color: #ef4444;">
              <?= $countHocVien ?? 0 ?>
            </p>
          </div>
        </div>

        <!-- Các nút chức năng -->
        <div class="action-buttons" style="margin-bottom: 15px; display: flex; gap: 10px; flex-wrap: wrap;">
          <a href="index.php?controller=daotao&action=them" class="btn add">➕ Thêm khóa học</a>
          <a href="index.php?controller=daotao&action=giangvien" class="btn" style="background: #8b5cf6;">👨‍🏫 Quản lý giảng viên</a>
          <a href="index.php?controller=daotao&action=baocao" class="btn" style="background: #f59e0b;">📊 Báo cáo đào tạo</a>
          <a href="index.php?controller=daotao&action=exportExcel" class="btn export">📥 Xuất Excel</a>
        </div>

        <!-- Tìm kiếm -->
        <form method="GET" action="index.php" class="search-form" style="margin-bottom: 15px; display: flex; gap: 10px;">
          <input type="hidden" name="controller" value="daotao">
          <input type="hidden" name="action" value="timkiem">
          <input type="text" name="keyword" placeholder="🔎 Tìm kiếm mã hoặc tên..." value="<?= $_GET['keyword'] ?? '' ?>" style="flex: 1; padding: 8px;">
          <button type="submit" class="btn search">Tìm kiếm</button>
        </form>

        <!-- Danh sách khóa học -->
        <table class="table">
          <thead>
            <tr>
              <th>Mã ĐT</th>
              <th>Tên khóa học</th>
              <th>Giảng viên</th>
              <th>Ngày bắt đầu</th>
              <th>Ngày kết thúc</th>
              <th>Địa điểm</th>
              <th>Chi phí</th>
              <th>Thao tác</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($daotao && $daotao->num_rows > 0) {
                while ($row = $daotao->fetch_assoc()) { ?>
                  <tr>
                    <td><?= $row['MaDT'] ?></td>
                    <td><?= $row['TenKhoaHoc'] ?></td>
                    <td><?= $row['GiangVien'] ?></td>
                    <td><?= $row['NgayBatDau'] ?></td>
                    <td><?= $row['NgayKetThuc'] ?></td>
                    <td><?= $row['DiaDiem'] ?></td>
                    <td><?= number_format($row['ChiPhi']) ?> VNĐ</td>
                    <td style="white-space: nowrap;">
                      <a href="index.php?controller=daotao&action=hocvien&madt=<?= $row['MaDT'] ?>" 
                         class="btn" style="background: #10b981; color: white;">👥 Học viên</a>
                      <a href="index.php?controller=daotao&action=sua&madt=<?= $row['MaDT'] ?>" 
                         class="btn edit">✏️ Sửa</a>
                      <a href="index.php?controller=daotao&action=xoa&madt=<?= $row['MaDT'] ?>" 
                         class="btn delete" onclick="return confirm('Xóa khóa học này?');">🗑️ Xóa</a>
                    </td>
                  </tr>
            <?php } } else { echo "<tr><td colspan='8'>Không có dữ liệu đào tạo</td></tr>"; } ?>
          </tbody>
        </table>
      </main>
    </div>
  </body>
</html>