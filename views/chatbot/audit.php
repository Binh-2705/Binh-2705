<?php include 'views/layout/header.php'; ?>
<?php include 'views/layout/sidebar.php'; ?>

<main class="main-content">
  <header>
    <h1>Nhật ký Chatbot</h1>
  </header>

  <div class="actions">
    <form method="GET" action="index.php" class="filter-form">
      <input type="hidden" name="controller" value="chatbot">
      <input type="hidden" name="action" value="audit">

      <select name="source">
        <option value="">Tất cả nguồn</option>
        <option value="tool" <?= (($_GET['source'] ?? '') === 'tool') ? 'selected' : '' ?>>tool</option>
        <option value="llm" <?= (($_GET['source'] ?? '') === 'llm') ? 'selected' : '' ?>>llm</option>
        <option value="action_plan" <?= (($_GET['source'] ?? '') === 'action_plan') ? 'selected' : '' ?>>action_plan</option>
        <option value="action_execution" <?= (($_GET['source'] ?? '') === 'action_execution') ? 'selected' : '' ?>>action_execution</option>
        <option value="fallback" <?= (($_GET['source'] ?? '') === 'fallback') ? 'selected' : '' ?>>fallback</option>
      </select>

      <select name="status">
        <option value="">Tất cả trạng thái action</option>
        <option value="pending" <?= (($_GET['status'] ?? '') === 'pending') ? 'selected' : '' ?>>pending</option>
        <option value="executed" <?= (($_GET['status'] ?? '') === 'executed') ? 'selected' : '' ?>>executed</option>
        <option value="failed" <?= (($_GET['status'] ?? '') === 'failed') ? 'selected' : '' ?>>failed</option>
      </select>

      <input type="text" name="q" class="search-box" placeholder="Tìm trong nội dung, user hoặc tiêu đề action..." value="<?= htmlspecialchars((string)($_GET['q'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
      <button type="submit" class="btn search">Lọc</button>
      <a href="index.php?controller=chatbot&action=audit" class="btn cancel">Đặt lại</a>
    </form>
  </div>

  <table class="table">
    <thead>
      <tr>
        <th>Thời gian</th>
        <th>Người dùng</th>
        <th>Vai trò</th>
        <th>Role chat</th>
        <th>Nguồn</th>
        <th>Nội dung</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($logs)): ?>
        <?php foreach ($logs as $row): ?>
          <tr>
            <td><?= htmlspecialchars((string)$row['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars((string)$row['username'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars((string)($row['user_role'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars((string)($row['message_role'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars((string)($row['source_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
            <td><div style="max-width: 420px; white-space: pre-wrap;"><?= htmlspecialchars((string)$row['content'], ENT_QUOTES, 'UTF-8') ?></div></td>
            <td>
              <?php if (!empty($row['action_title'])): ?>
                <div><strong><?= htmlspecialchars((string)$row['action_title'], ENT_QUOTES, 'UTF-8') ?></strong></div>
                <div><?= htmlspecialchars((string)($row['action_type'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                <div><?= htmlspecialchars((string)($row['action_status'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
              <?php else: ?>
                <span>---</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="7">Chưa có dữ liệu nhật ký chatbot.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>

  <?php if (($totalPages ?? 1) > 1): ?>
  <div class="pagination-wrap">
    <?php $currentPage = (int)($page ?? 1); ?>
    <?php $q = urlencode((string)($_GET['q'] ?? '')); ?>
    <?php $source = urlencode((string)($_GET['source'] ?? '')); ?>
    <?php $status = urlencode((string)($_GET['status'] ?? '')); ?>
    <a class="page-link <?= $currentPage <= 1 ? 'disabled' : '' ?>" href="index.php?controller=chatbot&action=audit&page=<?= max(1, $currentPage - 1) ?>&q=<?= $q ?>&source=<?= $source ?>&status=<?= $status ?>">← Trước</a>
    <span class="page-indicator">Trang <?= $currentPage ?> / <?= (int)$totalPages ?></span>
    <a class="page-link <?= $currentPage >= (int)$totalPages ? 'disabled' : '' ?>" href="index.php?controller=chatbot&action=audit&page=<?= min((int)$totalPages, $currentPage + 1) ?>&q=<?= $q ?>&source=<?= $source ?>&status=<?= $status ?>">Sau →</a>
  </div>
  <?php endif; ?>
</main>

<?php include 'views/layout/footer.php'; ?>
