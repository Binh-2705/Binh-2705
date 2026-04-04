<?php include 'views/layout/header.php'; ?>
<?php include 'views/layout/sidebar.php'; ?>

<main class="main-content">
  <header>
    <h1>📜 Nhật ký hệ thống</h1>
  </header>

  <div class="actions">
    <form method="GET" action="index.php" class="filter-form">
      <input type="hidden" name="controller" value="auditlog">
      <input type="hidden" name="action" value="index">

      <select name="level">
        <option value="">Tất cả mức</option>
        <option value="INFO" <?= (($_GET['level'] ?? '') === 'INFO') ? 'selected' : '' ?>>INFO</option>
        <option value="WARNING" <?= (($_GET['level'] ?? '') === 'WARNING') ? 'selected' : '' ?>>WARNING</option>
        <option value="ERROR" <?= (($_GET['level'] ?? '') === 'ERROR') ? 'selected' : '' ?>>ERROR</option>
        <option value="SECURITY" <?= (($_GET['level'] ?? '') === 'SECURITY') ? 'selected' : '' ?>>SECURITY</option>
      </select>

      <input type="text" name="q" class="search-box" placeholder="Tìm trong message/context..." value="<?= htmlspecialchars((string)($_GET['q'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
      <button type="submit" class="btn search">Lọc</button>
      <a href="index.php?controller=auditlog&action=index" class="btn cancel">Đặt lại</a>
      <a href="index.php?controller=auditlog&action=exportCsv&level=<?= urlencode((string)($_GET['level'] ?? '')) ?>&q=<?= urlencode((string)($_GET['q'] ?? '')) ?>" class="btn">Xuất CSV</a>
      <a href="index.php?controller=auditlog&action=exportJson&level=<?= urlencode((string)($_GET['level'] ?? '')) ?>&q=<?= urlencode((string)($_GET['q'] ?? '')) ?>" class="btn">Xuất JSON</a>
    </form>
  </div>

  <table class="table">
    <thead>
      <tr>
        <th>Thời gian</th>
        <th>Mức</th>
        <th>Thông điệp</th>
        <th>Context</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($logs)): ?>
        <?php foreach ($logs as $log): ?>
          <tr>
            <td><?= htmlspecialchars($log['time'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><span class="status-badge <?= strtolower($log['level']) === 'error' ? 'danger' : (strtolower($log['level']) === 'warning' ? 'warning' : (strtolower($log['level']) === 'security' ? 'danger' : 'success')) ?>"><?= htmlspecialchars($log['level'], ENT_QUOTES, 'UTF-8') ?></span></td>
            <td><?= htmlspecialchars($log['message'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><pre class="audit-context"><?= htmlspecialchars($log['context'], ENT_QUOTES, 'UTF-8') ?></pre></td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="4">Chưa có log phù hợp bộ lọc.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>

  <?php if (($totalPages ?? 1) > 1): ?>
  <div class="pagination-wrap">
    <?php $currentPage = (int)($page ?? 1); ?>
    <?php
      $q = urlencode((string)($_GET['q'] ?? ''));
      $level = urlencode((string)($_GET['level'] ?? ''));
    ?>
    <a class="page-link <?= $currentPage <= 1 ? 'disabled' : '' ?>" href="index.php?controller=auditlog&action=index&page=<?= max(1, $currentPage - 1) ?>&level=<?= $level ?>&q=<?= $q ?>">← Trước</a>
    <span class="page-indicator">Trang <?= $currentPage ?> / <?= (int)$totalPages ?></span>
    <a class="page-link <?= $currentPage >= (int)$totalPages ? 'disabled' : '' ?>" href="index.php?controller=auditlog&action=index&page=<?= min((int)$totalPages, $currentPage + 1) ?>&level=<?= $level ?>&q=<?= $q ?>">Sau →</a>
  </div>
  <?php endif; ?>
</main>

<?php include 'views/layout/footer.php'; ?>
