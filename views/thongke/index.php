<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>📊 Thống kê - Báo cáo</title>
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
      <li><a href="index.php?controller=thongke&action=index" class="active">📊 Thống kê - Báo cáo</a></li>
    </ul>
  </nav>

  <main class="main-content">
    <header>
      <h1>📊 Thống kê - Báo cáo</h1>
    </header>

    <div class="actions">
      <div class="btn-group">
        <a href="index.php?controller=thongke&action=them" class="btn add">➕ Thêm báo cáo</a>
      
      </div>
      <form method="GET" action="index.php" style="display:inline;">
        <input type="hidden" name="controller" value="thongke">
        <input type="hidden" name="action" value="index">
        <select name="loai" class="search-box">
          <option value="">-- Loại báo cáo --</option>
          <option value="nhanvien" <?php echo (isset($_GET['loai']) && $_GET['loai'] == 'nhanvien') ? 'selected' : ''; ?>>Nhân viên</option>
          <option value="chamcong" <?php echo (isset($_GET['loai']) && $_GET['loai'] == 'chamcong') ? 'selected' : ''; ?>>Chấm công</option>
          <option value="luong" <?php echo (isset($_GET['loai']) && $_GET['loai'] == 'luong') ? 'selected' : ''; ?>>Lương</option>
          <option value="daotao" <?php echo (isset($_GET['loai']) && $_GET['loai'] == 'daotao') ? 'selected' : ''; ?>>Đào tạo</option>
        </select>
        <input type="text" name="thang" placeholder="📅 Tháng (YYYY-MM)" class="search-box" value="<?php echo isset($_GET['thang']) ? htmlspecialchars($_GET['thang']) : ''; ?>">
        <input type="text" name="ma_pb" placeholder="🏢 Mã phòng ban" class="search-box" value="<?php echo isset($_GET['ma_pb']) ? htmlspecialchars($_GET['ma_pb']) : ''; ?>">
        <button type="submit" class="btn search">Tìm</button>
      </form>
    </div>

    <h2>📑 Danh sách báo cáo</h2>
    <table class="table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Tiêu đề</th>
          <th>Loại</th>
          <th>Tháng</th>
          <th>Phòng ban</th>
          <th>Tạo lúc</th>
          <th>Thao tác</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($dsBaoCao && $dsBaoCao->num_rows > 0): ?>
          <?php while ($row = $dsBaoCao->fetch_assoc()): ?>
            <tr>
              <td><?= $row['id'] ?></td>
              <td><?= $row['tieu_de'] ?></td>
              <td><?= $row['loai'] ?></td>
              <td><?= $row['thang'] ?></td>
              <td><?= $row['TenPB'] ?></td>
              <td><?= $row['created_at'] ?></td>
              <td>
                <a href="index.php?controller=thongke&action=sua&id=<?= $row['id'] ?>" class="btn edit">✏️ Sửa</a>
                <a href="index.php?controller=thongke&action=xoa&id=<?= $row['id'] ?>" class="btn delete"
                   onclick="return confirm('Bạn có chắc muốn xóa báo cáo này không?');">🗑️ Xóa</a>
                <a href="index.php?controller=thongke&action=chitiet&id=<?= $row['id'] ?>" class="btn detail">📑 Chi tiết</a>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="7">❌ Chưa có dữ liệu báo cáo nào.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>

    <h2>📊 Thống kê tổng hợp</h2>

    <h3>👥 Nhân viên theo phòng ban</h3>
    <table class="table">
      <thead><tr><th>Phòng ban</th><th>Số lượng</th></tr></thead>
      <tbody>
        <?php 
        if ($thongKeNV && $thongKeNV->num_rows > 0): 
          while ($row = $thongKeNV->fetch_assoc()): ?>
            <tr>
              <td><?= $row['PhongBan'] ?></td>
              <td><?= $row['soLuong'] ?></td>
            </tr>
          <?php endwhile; 
        else: ?>
          <tr><td colspan="2">❌ Không có dữ liệu</td></tr>
        <?php endif; ?>
      </tbody>
    </table>

    <h3>🕒 Chấm công <?= isset($_GET['thang']) && !empty($_GET['thang']) ? "tháng {$_GET['thang']}" : "(tất cả)" ?></h3>
    <table class="table">
      <thead><tr><th>Tháng</th><th>Ngày làm</th><th>Ngày nghỉ</th></tr></thead>
      <tbody>
        <?php 
        if ($thongKeCC && $thongKeCC->num_rows > 0): 
          while ($row = $thongKeCC->fetch_assoc()): ?>
            <tr>
              <td><?= $row['Thang'] ?></td>
              <td><?= $row['tongNgayLam'] ?></td>
              <td><?= $row['tongNgayNghi'] ?></td>
            </tr>
          <?php endwhile; 
        else: ?>
          <tr><td colspan="3">❌ Không có dữ liệu</td></tr>
        <?php endif; ?>
      </tbody>
    </table>

    <h3>💰 Lương <?= isset($_GET['thang']) && !empty($_GET['thang']) ? "tháng {$_GET['thang']}" : "(tất cả)" ?></h3>
    <table class="table">
      <thead>
        <tr><th>Tháng</th><th>Lương CB</th><th>Phụ cấp</th><th>Thưởng</th><th>Khấu trừ</th><th>Tổng lương</th></tr>
      </thead>
      <tbody>
        <?php 
        if ($thongKeLuong && $thongKeLuong->num_rows > 0): 
          while ($row = $thongKeLuong->fetch_assoc()): ?>
            <tr>
              <td><?= $row['Thang'] ?></td>
              <td><?= number_format($row['tongLuongCB'], 0, ',', '.') ?></td>
              <td><?= number_format($row['tongPhuCap'], 0, ',', '.') ?></td>
              <td><?= number_format($row['tongThuong'], 0, ',', '.') ?></td>
              <td><?= number_format($row['tongKhauTru'], 0, ',', '.') ?></td>
              <td><?= number_format($row['tongTongLuong'], 0, ',', '.') ?></td>
            </tr>
          <?php endwhile; 
        else: ?>
          <tr><td colspan="6">❌ Không có dữ liệu</td></tr>
        <?php endif; ?>
      </tbody>
    </table>

    <h3>📚 Đào tạo</h3>
    <?php 
    if ($thongKeDT && $thongKeDT->num_rows > 0): 
      $dt = $thongKeDT->fetch_assoc(); ?>
      <p>Số khóa học: <strong><?= $dt['soKhoaHoc'] ?></strong></p>
      <p>Tổng chi phí đào tạo: <strong><?= number_format($dt['tongChiPhi'], 0, ',', '.') ?></strong></p>
    <?php else: ?>
      <p>❌ Không có dữ liệu đào tạo</p>
    <?php endif; ?>

  </main>
</div>
</body>
</html>