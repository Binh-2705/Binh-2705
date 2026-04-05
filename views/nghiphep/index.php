<?php include 'views/layout/header.php'; ?>
<?php include 'views/layout/sidebar.php'; ?>
<?php
$quyen = $quyen ?? [];
$canThemNghiPhep = in_array('them_nghiphep', $quyen, true);
$canDuyetNghiPhep = in_array('duyet_nghiphep', $quyen, true);
$canTuChoiNghiPhep = in_array('tuchoi_nghiphep', $quyen, true);
$canXoaNghiPhep = in_array('xoa_nghiphep', $quyen, true);
$showActionColumn = $canDuyetNghiPhep || $canTuChoiNghiPhep || $canXoaNghiPhep;
?>

  <!-- MAIN -->
  <main class="main-content">
    <header>
      <h1>📆 Quản lý Nghỉ phép</h1>
    </header>

    <div class="actions">
      <?php if ($canThemNghiPhep): ?>
      <a href="index.php?controller=nghiphep&action=them" class="btn add">
        ➕ Thêm nghỉ phép
      </a>
      <?php endif; ?>

      <form method="get" action="index.php" class="filter-form">
        <input type="hidden" name="controller" value="nghiphep">
        <input type="hidden" name="action" value="index">
        <input type="text" name="keyword" class="search-box"
               placeholder="🔍 Nhập mã NV hoặc tên..." value="<?= htmlspecialchars((string)($_GET['keyword'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        <button class="btn search">Tìm</button>
      </form>
    </div>

    <table class="table">
      <thead>
        <tr>
          <th>Mã NP</th>
          <th>Mã NV</th>
          <th>Họ tên</th>
          <th>Từ ngày</th>
          <th>Đến ngày</th>
          <th>Số ngày</th>
          <th>Loại nghỉ</th>
          <th>Lý do</th>
          <th>Trạng thái</th>
          <?php if ($showActionColumn): ?>
          <th>Thao tác</th>
          <?php endif; ?>
        </tr>
      </thead>

      <tbody>
        <?php if ($data && $data->num_rows > 0): ?>
          <?php while ($row = $data->fetch_assoc()): ?>
            <tr>
              <td><?= $row['MaNP'] ?></td>
              <td><?= $row['MaNV'] ?></td>
              <td><?= $row['HoTen'] ?></td>
              <td><?= $row['TuNgay'] ?></td>
              <td><?= $row['DenNgay'] ?></td>
              <td><?= $row['SoNgayNghi'] ?></td>
              <td><?= $row['LoaiNghi'] ?></td>
              <td><?= $row['LyDo'] ?></td>

              <td>
                <?php if ($row['TrangThai'] == 'Chờ duyệt'): ?>
                  <span style="color:#f59e0b;font-weight:bold;">⏳ Chờ duyệt</span>
                <?php elseif ($row['TrangThai'] == 'Đã duyệt'): ?>
                  <span style="color:#16a34a;font-weight:bold;">✅ Đã duyệt</span>
                <?php else: ?>
                  <span style="color:#dc2626;font-weight:bold;">❌ Từ chối</span>
                <?php endif; ?>
              </td>

              <?php if ($showActionColumn): ?>
              <td>
                <div class="table-actions">
                <?php if ($row['TrangThai'] == 'Chờ duyệt'): ?>
                  <?php if ($canDuyetNghiPhep): ?>
                  <a href="index.php?controller=nghiphep&action=duyet&id=<?= $row['MaNP'] ?>"
                     class="btn add"
                     title="Duyệt"
                     onclick="return confirm('Duyệt đơn nghỉ phép này?')">✅</a>
                  <?php endif; ?>

                  <?php if ($canTuChoiNghiPhep): ?>
                  <a href="index.php?controller=nghiphep&action=tuchoi&id=<?= $row['MaNP'] ?>"
                     class="btn delete"
                     title="Từ chối"
                     onclick="return confirm('Từ chối đơn nghỉ phép này?')">❌</a>
                  <?php endif; ?>

                  <?php if ($canXoaNghiPhep): ?>
                  <a href="index.php?controller=nghiphep&action=xoa&id=<?= $row['MaNP'] ?>"
                     class="btn cancel"
                     title="Rút"
                     onclick="return confirm('Rút đơn nghỉ phép này?')">↩️</a>
                  <?php endif; ?>

                  <?php if (!$canDuyetNghiPhep && !$canTuChoiNghiPhep && !$canXoaNghiPhep): ?>
                  <span class="muted-inline-note">Chỉ xem</span>
                  <?php endif; ?>
                <?php else: ?>
                  <?php if ($canXoaNghiPhep): ?>
                  <a href="index.php?controller=nghiphep&action=xoa&id=<?= $row['MaNP'] ?>"
                     class="btn delete"
                     title="Xóa"
                     onclick="return confirm('Xóa bản ghi này?')">🗑️</a>
                  <?php else: ?>
                  <span class="muted-inline-note">Chỉ xem</span>
                  <?php endif; ?>
                <?php endif; ?>
                </div>
              </td>
              <?php endif; ?>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr>
            <td colspan="<?= $showActionColumn ? 10 : 9 ?>">❌ Chưa có dữ liệu nghỉ phép</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>

    <?php if (($totalPages ?? 1) > 1): ?>
      <?php
        $currentPage = (int)($page ?? 1);
        $keywordParam = urlencode((string)($_GET['keyword'] ?? ''));
      ?>
      <div class="pagination-wrap">
        <a class="page-link <?= $currentPage <= 1 ? 'disabled' : '' ?>" href="index.php?controller=nghiphep&action=index&page=<?= max(1, $currentPage - 1) ?>&keyword=<?= $keywordParam ?>">← Trước</a>
        <span class="page-indicator">Trang <?= $currentPage ?> / <?= (int)$totalPages ?></span>
        <a class="page-link <?= $currentPage >= (int)$totalPages ? 'disabled' : '' ?>" href="index.php?controller=nghiphep&action=index&page=<?= min((int)$totalPages, $currentPage + 1) ?>&keyword=<?= $keywordParam ?>">Sau →</a>
      </div>
    <?php endif; ?>
  </main>
<?php include 'views/layout/footer.php'; ?>
