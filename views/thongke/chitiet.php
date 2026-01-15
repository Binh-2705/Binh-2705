<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>📑 Chi tiết báo cáo</title>
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
                <li><a href="index.php?controller=chamcong&action=index">🕒 Quản lý chấm công</a></li>
                <li><a href="index.php?controller=hopdong&action=index">📄 Quản lý hợp đồng</a></li>
                <li><a href="index.php?controller=nghiphep&action=index">📆 Quản lý nghỉ phép</a></li>
                <li><a href="index.php?controller=khenthuong&action=index">🏅 Khen thưởng - Kỷ luật</a></li>
                <li><a href="index.php?controller=thongke&action=index" class="active">📊 Thống kê - Báo cáo</a></li>
                <li><a href="index.php?controller=chucvu&action=index">🙍‍♂️ Quản lý chức vụ</a></li>
                <li><a href="index.php?controller=hoso&action=index">👤 Hồ sơ cá nhân</a></li>
                <li><a href="index.php?controller=tuyendung&action=index" >💼 Quản lý tuyển dụng</a></li>
                <li><a href="index.php?controller=daotao&action=index">📚 Quản lý đào tạo</a></li>
                <li><a href="index.php?controller=taikhoan&action=index">🗂 Quản lý tài khoản</a></li>
                
               <li><a href="index.php?controller=dangnhap&action=dangxuat">🚪 Đăng xuất</a></li>
            </ul>
        </ul>
  </nav>

  <main class="main-content">
    <header>
      <h1>📑 Chi tiết báo cáo: <?= htmlspecialchars($baoCao['tieu_de']) ?></h1>
    </header>

    <div style="margin-bottom: 20px;">
      <form method="GET" action="index.php" style="display: inline;">
        <input type="hidden" name="controller" value="thongke">
        <input type="hidden" name="action" value="exportExcelChiTiet">
        <input type="hidden" name="id" value="<?= $baoCao['id'] ?>">
        <button type="submit" style="padding: 10px 20px; background: #28a745; color: white; border: none; border-radius: 5px; font-weight: bold; cursor: pointer;">
          📤 Xuất Excel Chi Tiết
        </button>
      </form>
    </div>

    <?php if ($baoCao['loai'] == 'nhanvien'): ?>
      <h3>👥 Nhân viên phòng <?= htmlspecialchars($baoCao['ma_pb']) ?></h3>
      <table class="table">
        <thead><tr><th>Mã NV</th><th>Họ tên</th><th>Giới tính</th><th>Ngày sinh</th><th>Chức vụ</th><th>Lương</th></tr></thead>
        <tbody>
          <?php if ($dsChiTiet && $dsChiTiet->num_rows > 0): ?>
            <?php while ($nv = $dsChiTiet->fetch_assoc()): ?>
              <tr>
                <td><?= $nv['MaNV'] ?></td>
                <td><?= $nv['HoTen'] ?></td>
                <td><?= $nv['GioiTinh'] ?></td>
                <td><?= $nv['NgaySinh'] ?></td>
                <td><?= $nv['ChucVu'] ?></td>
                <td><?= number_format($nv['Luong'], 0, ',', '.') ?> VND</td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="6">❌ Không có nhân viên nào.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>

    <?php elseif ($baoCao['loai'] == 'chamcong'): ?>
      <h3>🕒 Chi tiết chấm công (Mã CC: <?= htmlspecialchars($baoCao['ma_pb']) ?>)</h3>
      <table class="table">
        <thead><tr><th>Mã CC</th><th>Mã NV</th><th>Họ tên</th><th>Tháng</th><th>Ngày làm</th><th>Ngày nghỉ</th><th>Ghi chú</th></tr></thead>
        <tbody>
          <?php if ($dsChiTiet && $dsChiTiet->num_rows > 0): ?>
            <?php while ($cc = $dsChiTiet->fetch_assoc()): ?>
              <tr>
                <td><?= $cc['MaCC'] ?></td>
                <td><?= $cc['MaNV'] ?></td>
                <td><?= $cc['HoTen'] ?></td>
                <td><?= $cc['Thang'] ?></td>
                <td><?= $cc['SoNgayLam'] ?></td>
                <td><?= $cc['SoNgayNghi'] ?></td>
                <td><?= $cc['GhiChu'] ?></td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="7">❌ Không có dữ liệu chấm công.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>

    <?php elseif ($baoCao['loai'] == 'luong'): ?>
      <h3>💰 Chi tiết lương (Mã lương: <?= htmlspecialchars($baoCao['ma_pb']) ?>)</h3>
      <table class="table">
        <thead><tr><th>Mã lương</th><th>Mã NV</th><th>Họ tên</th><th>Tháng</th><th>Lương CB</th><th>Phụ cấp</th><th>Thưởng</th><th>Khấu trừ</th><th>Tổng lương</th></tr></thead>
        <tbody>
          <?php if ($dsChiTiet && $dsChiTiet->num_rows > 0): ?>
            <?php while ($l = $dsChiTiet->fetch_assoc()): ?>
              <tr>
                <td><?= $l['MaLuong'] ?></td>
                <td><?= $l['MaNV'] ?></td>
                <td><?= $l['HoTen'] ?></td>
                <td><?= $l['Thang'] ?></td>
                <td><?= number_format($l['LuongCB'], 0, ',', '.') ?> VND</td>
                <td><?= number_format($l['PhuCap'], 0, ',', '.') ?> VND</td>
                <td><?= number_format($l['Thuong'], 0, ',', '.') ?> VND</td>
                <td><?= number_format($l['KhauTru'], 0, ',', '.') ?> VND</td>
                <td><?= number_format($l['TongLuong'], 0, ',', '.') ?> VND</td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="9">❌ Không có dữ liệu lương.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>

    <?php elseif ($baoCao['loai'] == 'daotao'): ?>
      <h3>📚 Danh sách khóa đào tạo</h3>
      <table class="table">
        <thead><tr><th>Tên khóa học</th><th>Ngày bắt đầu</th><th>Ngày kết thúc</th><th>Chi phí</th></tr></thead>
        <tbody>
          <?php if ($dsChiTiet && $dsChiTiet->num_rows > 0): ?>
            <?php while ($dt = $dsChiTiet->fetch_assoc()): ?>
              <tr>
                <td><?= $dt['TenKhoaHoc'] ?></td>
                <td><?= $dt['NgayBatDau'] ?></td>
                <td><?= $dt['NgayKetThuc'] ?></td>
                <td><?= number_format($dt['ChiPhi'], 0, ',', '.') ?> VND</td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="4">❌ Không có dữ liệu đào tạo.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>

    <?php else: ?>
      <p>⚠️ Loại báo cáo này chưa có chi tiết hiển thị.</p>
    <?php endif; ?>

    <?php if (!empty($baoCao['noi_dung'])): ?>
      <div style="margin-top: 30px; padding: 15px; background: #f8f9fa; border-radius: 5px; border: 1px solid #dee2e6;">
        <h3>📝 Nội dung báo cáo</h3>
        <p style="white-space: pre-line;"><?= htmlspecialchars($baoCao['noi_dung']) ?></p>
      </div>
    <?php endif; ?>

    <div class="form-actions" style="margin-top: 20px;">
      <a href="index.php?controller=thongke&action=index" class="btn back">↩️ Quay lại</a>
    </div>
  </main>
</div>
</body>
</html>