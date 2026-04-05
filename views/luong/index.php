<?php include 'views/layout/header.php'; ?>
<?php include 'views/layout/sidebar.php'; ?>
<?php
$quyen = $quyen ?? [];
$canTinhLuong = in_array('tinh_luong_thang', $quyen, true);
$canChotLuong = in_array('chot_luong', $quyen, true);
$canMoChotLuong = in_array('mo_chot_luong', $quyen, true);
$canXuatExcelLuong = in_array('xuat_excel_luong', $quyen, true) || $canTinhLuong || $canChotLuong || $canMoChotLuong;
$showQuanTriLuong = $canChotLuong || $canMoChotLuong;
?>
  <!-- MAIN -->
  <main class="main-content">
    <header>
        <h1>💰 Bảng Lương</h1>
    </header>

    <!-- ====== FORM TÍNH LƯƠNG ====== -->
    <div class="actions">
      <?php if ($canTinhLuong): ?>
        <form method="POST" action="index.php?controller=luong&action=tinhLuongThang">
            <label>Tháng:</label>
            <input type="number" name="thang" min="1" max="12" required>

            <label>Năm:</label>
            <input type="number" name="nam" value="<?= date('Y') ?>" required>

            <button type="submit" class="btn add">
                ⚙️ Tính lương tháng
            </button>
        </form>
          <?php endif; ?>

          <?php if ($canXuatExcelLuong): ?>
        <a href="index.php?controller=luong&action=exportExcel" class="btn export">
            📥 Xuất Excel
        </a>
          <?php endif; ?>
    </div>

    <!-- ====== BẢNG LƯƠNG ====== -->
    <table class="table">
      <thead>
        <tr>
          <th>Mã BL</th>
          <th>Nhân viên</th>
          <th>Tháng</th>
          <th>Năm</th>
          <th>Lương cơ sở</th>
          <th>Hệ số</th>
          <th>Phụ cấp</th>
          <th>Thưởng</th>
          <th>Phạt</th>
          <th>Bảo hiểm</th>
          <th><b>Thực nhận</b></th>
          <th><b>Tổng lương</b></th>
          <?php if ($showQuanTriLuong): ?>
          <th>Trạng thái</th>
          <th>Thao tác</th>
          <?php endif; ?>
        </tr>
      </thead>

      <tbody>
        <?php if (!empty($luong)): ?>
          <?php foreach ($luong as $row): ?>
            <tr>
              <td><?= $row['MaBL'] ?></td>
              <td><?= $row['HoTen'] ?></td>
              <td><?= $row['Thang'] ?></td>
              <td><?= $row['Nam'] ?></td>

              <td><?= number_format($row['LuongCoSo'],0,',','.') ?></td>
              <td><?= $row['HeSoLuong'] ?></td>
              <td><?= number_format($row['PhuCap'],0,',','.') ?></td>
              <td><?= number_format($row['Thuong'],0,',','.') ?></td>
              <td><?= number_format($row['Phat'],0,',','.') ?></td>
              <td style="color:red">
                <?= number_format($row['BaoHiem'] ?? 0,0,',','.') ?>
</td>

<td>
    <b style="color:#e53935">
        <?= number_format($row['TongLuong'],0,',','.') ?>
    </b>
</td>

              <td>
                <b style="color:#e53935">
                  <?= number_format($row['TongLuong'],0,',','.') ?>
                </b>
              </td>

              <?php if ($showQuanTriLuong): ?>
              <!-- TRẠNG THÁI -->
              <td>
                <?php if ($row['TrangThai'] == 'Đã chốt'): ?>
                    <span style="color:green;font-weight:bold">✔ Đã chốt</span>
                <?php else: ?>
                    <span style="color:#ff9800;font-weight:bold">⏳ Chưa chốt</span>
                <?php endif; ?>
              </td>

              <!-- THAO TÁC -->
              <td>
                <div class="table-actions">
                <?php if ($row['TrangThai'] != 'Đã chốt' && $canChotLuong): ?>
                    <a href="index.php?controller=luong&action=chotLuong&id=<?= $row['MaBL'] ?>"
                       class="btn add"
                       title="Chốt"
                       onclick="return confirm('Chốt lương tháng này? Sau khi chốt sẽ KHÔNG sửa được!')">🔒</a>
                <?php elseif ($row['TrangThai'] == 'Đã chốt' && $canMoChotLuong): ?>
                    <a href="index.php?controller=luong&action=moChot&id=<?= $row['MaBL'] ?>"
                       class="btn delete"
                       title="Mở"
                       onclick="return confirm('Mở chốt lương?')">🔓</a>
                <?php else: ?>
                    <span class="muted-inline-note">Chỉ xem</span>
                <?php endif; ?>
                </div>
              </td>
              <?php endif; ?>

            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="<?= $showQuanTriLuong ? 14 : 12 ?>">Chưa có dữ liệu lương</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>

  </main>
<?php include 'views/layout/footer.php'; ?>