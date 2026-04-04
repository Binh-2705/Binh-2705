<?php include 'views/layout/header.php'; ?>
<?php include 'views/layout/sidebar.php'; ?>
  <!-- ===== MAIN ===== -->
  <main class="main-content">
    <header>
      <h1>🏢 Quản lý ngạch lương</h1>
    </header>

    <!-- ===== ACTIONS ===== -->
    <div class="actions">
      <div class="btn-group">
        
        <!--<a href="index.php?controller=ngachluong&action=import" class="btn export">📂 Nhập CSV</a>
        <a href="index.php?controller=ngachluong&action=exportExcel" class="btn export">📥 Xuất Excel</a> -->
      </div>

     
    </div>

    <!-- ===== TABLE ===== -->
    <table class="table">
      <thead>
        <tr>
          <th>Mã ngạch</th>
          <th>Tên ngạch</th>
          <th>Mô tả</th>
        
        </tr>
      </thead>
      <tbody>
        <?php if ($ngachluongs && $ngachluongs->num_rows > 0): ?>
          <?php while ($row = $ngachluongs->fetch_assoc()): ?>
            <tr>
              <td><?= $row['MaNgach'] ?></td>
              <td><?= htmlspecialchars($row['TenNgach']) ?></td>
              <td><?= htmlspecialchars($row['MoTa']) ?></td>
             
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr>
            <td colspan="4">Không có ngạch</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>

  </main>
<?php include 'views/layout/footer.php'; ?>