<?php include 'views/layout/header.php'; ?>
<?php include 'views/layout/sidebar.php'; ?>
  <!-- MAIN -->
  <main class="main-content">
    <header>
      <h1 data-i18n="assignment_page.title">📌 Phân công nhân viên</h1>
    </header>

    <div class="actions">
      <form method="GET" action="index.php" class="search-form">
        <input type="hidden" name="controller" value="phancong">
        <input type="hidden" name="action" value="index">
        <input
          type="text"
          name="keyword"
          class="search-box"
          placeholder="🔍 Tìm mã NV, tên, phòng ban, chức vụ..."
          data-i18n-placeholder="assignment_page.search_placeholder"
          value="<?= htmlspecialchars((string)($keyword ?? ''), ENT_QUOTES, 'UTF-8') ?>"
        >
        <button type="submit" class="btn search" data-i18n="common.search">Tìm kiếm</button>
        <?php if (!empty($keyword)): ?>
        <a href="index.php?controller=phancong&action=index" class="btn" data-i18n="common.refresh">Làm mới</a>
        <?php endif; ?>
      </form>

      <?php if(in_array('them_phancong', $quyen)): ?>
      <a href="index.php?controller=phancong&action=add" class="btn add" data-i18n="assignment_page.add">
        ➕ Phân công mới
      </a>
      <?php endif; ?>
      
    </div>

    <!-- TABLE -->
    <table class="table">
      <thead>
        <tr>
          <th data-i18n="common.employee_code">Mã NV</th>
          <th data-i18n="common.full_name">Họ tên</th>
          <th data-i18n="common.department">Phòng ban</th>
          <th data-i18n="common.position">Chức vụ</th>
          <th data-i18n="common.from_date">Từ ngày</th>
          <th data-i18n="common.to_date">Đến ngày</th>
          <th data-i18n="common.type">Loại</th>
          <th data-i18n="common.actions">Thao tác</th>
        </tr>
      </thead>

      <tbody>
        <?php if ($phancongs && mysqli_num_rows($phancongs) > 0): ?>
          <?php while ($row = mysqli_fetch_assoc($phancongs)): ?>
            <tr>
              <td><?= $row['MaNV'] ?></td>
              <td><?= htmlspecialchars($row['HoTen']) ?></td>
              <td><?= htmlspecialchars($row['TenPB']) ?></td>
              <td><?= htmlspecialchars($row['TenCV']) ?></td>
              <td><?= $row['NgayBatDau'] ?></td>
              <td>
                <?php if (!empty($row['NgayKetThuc'])): ?>
                  <?= $row['NgayKetThuc'] ?>
                <?php else: ?>
                  <span data-i18n="common.present">Hiện tại</span>
                <?php endif; ?>
              </td>
              <td><?= htmlspecialchars($row['LoaiDieuChuyen']) ?></td>
              <td>
                <div class="table-actions">
                <?php if(in_array('sua_phancong', $quyen)): ?>
                <a class="btn edit"
                   href="index.php?controller=phancong&action=edit&id=<?= $row['MaQT'] ?>"
                   title="Chỉnh sửa"
                   data-i18n-title="assignment_page.edit_title">✏️</a>
                <?php endif; ?>
                <?php if(in_array('xoa_phancong', $quyen)): ?>
                <a class="btn delete"
                   title="Xóa"
                   data-i18n-title="assignment_page.delete_title"
                   onclick="return confirm((window.HRMSettings && window.HRMSettings.get().language === 'en') ? 'Delete this assignment?' : 'Xóa phân công này?')"
                   href="index.php?controller=phancong&action=delete&id=<?= $row['MaQT'] ?>">🗑️</a>
                <?php endif; ?>
                <?php if(in_array('xem_lichsu_phancong', $quyen)): ?>
                 <a class="btn search"
                   href="index.php?controller=phancong&action=history&manv=<?= $row['MaNV'] ?>"
                   title="Lịch sử"
                   data-i18n-title="assignment_page.history_title">📈</a>
                <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr>
            <td colspan="8" style="text-align:center" data-i18n="assignment_page.empty">Chưa có dữ liệu phân công</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>

  </main>
<?php include 'views/layout/footer.php'; ?>
