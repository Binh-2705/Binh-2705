<?php include 'views/layout/header.php'; ?>
<?php include 'views/layout/sidebar.php'; ?>
  <!-- ===== MAIN ===== -->
  <main class="main-content">
    <header>
      <h1>🏢 Quản lý bậc lương</h1>
    </header>

    <!-- ===== ACTIONS ===== -->
    <div class="actions">
      

      <form method="GET" action="index.php" class="search-form">
        <input type="hidden" name="controller" value="bacluong">
       
      </form>
    </div>

    <!-- ===== TABLE ===== -->
    <table class="table">
  <thead>
    <tr>
      <th>STT</th> <th>Ngạch lương</th>
      <th>Tên bậc</th>
      <th>Hệ số</th>
      <th>Lương cơ sở</th>
      <th>Lương tính</th>
    </tr>
  </thead>
  <tbody>
    <?php 
    if (isset($bacluong) && $bacluong->num_rows > 0): 
      // Chuyển dữ liệu từ object sang array để dễ xử lý đếm dòng
      $rows = [];
      while ($r = $bacluong->fetch_assoc()) { $rows[] = $r; }

      // Đếm số lần xuất hiện của từng Ngạch lương để gộp ô (rowspan)
      $ngach_counts = array_count_values(array_column($rows, 'TenNgach'));
      
      $stt = 1;
      $displayed_ngach = []; // Mảng đánh dấu ngạch đã hiển thị tên
      
      foreach ($rows as $row): 
    ?>
      <tr>
        <td style="text-align: center;"><?= $stt++ ?></td>

        <?php if (!isset($displayed_ngach[$row['TenNgach']])): ?>
          <td rowspan="<?= $ngach_counts[$row['TenNgach']] ?>" 
              style="vertical-align: middle; font-weight: bold; background-color: #f8f9fa; border-right: 2px solid #dee2e6;">
            <?= htmlspecialchars($row['TenNgach']) ?>
          </td>
          <?php $displayed_ngach[$row['TenNgach']] = true; ?>
        <?php endif; ?>

        <td><?= htmlspecialchars($row['TenBac']) ?></td>
        <td><?= number_format($row['HeSoLuong'], 2) ?></td>
        <td><?= number_format($row['LuongCoSo'], 0, ',', '.') ?> VNĐ</td>
        <td style="color: #d32f2f;">
           <strong><?= number_format($row['LuongTinh'] ?? ($row['HeSoLuong'] * $row['LuongCoSo']), 0, ',', '.') ?></strong> VNĐ
        </td>
      </tr>
    <?php endforeach; ?>
    <?php else: ?>
      <tr>
        <td colspan="6" style="text-align:center;">Chưa có dữ liệu bậc lương nào.</td>
      </tr>
    <?php endif; ?>
  </tbody>
</table>

  </main>
  <?php include 'views/layout/footer.php'; ?>