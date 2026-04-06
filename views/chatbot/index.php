<?php include 'views/layout/header.php'; ?>
<?php include 'views/layout/sidebar.php'; ?>
<?php
$chatbotRole = (string)(($_SESSION['taikhoan']['VaiTro'] ?? 'NhanVien'));
$chatbotQuickActionsByRole = [
    'Admin' => [
        'Tổng số nhân viên hiện tại là bao nhiêu?' => 'Tổng nhân viên',
        'Hợp đồng sắp hết hạn' => 'HĐ sắp hết hạn',
        'Top tăng ca tháng này' => 'Tăng ca',
        'Tóm tắt tuyển dụng' => 'Tuyển dụng',
    ],
    'HR' => [
        'Có bao nhiêu đơn nghỉ phép chờ duyệt?' => 'Đơn chờ duyệt',
        'Tạo đơn nghỉ phép từ 2026-04-10 đến 2026-04-12 lý do việc riêng' => 'Tạo đơn nghỉ',
        'Khóa đào tạo đang diễn ra' => 'Đào tạo',
        'Hợp đồng sắp hết hạn' => 'HĐ sắp hết hạn',
    ],
    'KeToan' => [
        'Lương tháng này của tôi' => 'Lương của tôi',
        'Tổng quan bảo hiểm nhân viên' => 'Bảo hiểm',
        'Hợp đồng của tôi' => 'HĐ của tôi',
        'Khen thưởng/kỷ luật gần đây' => 'Khen thưởng',
    ],
    'QuanLy' => [
        'Có bao nhiêu đơn nghỉ phép chờ duyệt?' => 'Đơn chờ duyệt',
        'Phân bổ nhân sự theo phòng ban' => 'Theo phòng ban',
        'Hợp đồng sắp hết hạn' => 'HĐ sắp hết hạn',
        'Top tăng ca tháng này' => 'Tăng ca',
    ],
    'NhanVien' => [
        'Thông tin cá nhân của tôi' => 'Hồ sơ của tôi',
        'Lương tháng này của tôi' => 'Lương của tôi',
        'Hợp đồng của tôi' => 'HĐ của tôi',
        'Đơn nghỉ phép của tôi' => 'Đơn nghỉ phép',
    ],
];
$chatbotQuickActions = $chatbotQuickActionsByRole[$chatbotRole] ?? $chatbotQuickActionsByRole['NhanVien'];
?>

<main class="main-content chatbot-page">
  <header class="chatbot-header">
    <h1>AI Chatbot Hỗ trợ nghiệp vụ</h1>
    <p>Chatbot có thể trả lời, gợi ý giải pháp và truy vấn dữ liệu nội bộ ở chế độ an toàn.</p>
  </header>

  <section class="chatbot-shell" data-chatbot-shell data-endpoint="index.php?controller=chatbot&action=ask" data-confirm-endpoint="index.php?controller=chatbot&action=confirmDraft" data-brief-endpoint="index.php?controller=chatbot&action=brief" data-reset-endpoint="index.php?controller=chatbot&action=clearHistory">
    <div class="chatbot-toolbar">
      <span class="chatbot-toolbar-note">Vai trò hiện tại: <?= htmlspecialchars($chatbotRole, ENT_QUOTES, 'UTF-8'); ?></span>
      <button type="button" class="chatbot-toolbar-btn" data-chatbot-reset>Cuộc trò chuyện mới</button>
    </div>
    <div class="chatbot-messages" aria-live="polite">
      <article class="chatbot-msg bot">
        <div class="chatbot-bubble">
          Xin chào. Bạn có thể hỏi về nhân viên, phân công, nghỉ phép, hợp đồng hoặc nhờ tôi đề xuất hướng xử lý.
        </div>
      </article>
    </div>

    <div class="chatbot-quick-actions">
      <?php foreach ($chatbotQuickActions as $prompt => $label) { ?>
      <button type="button" class="chatbot-chip" data-prompt="<?= htmlspecialchars($prompt, ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?></button>
      <?php } ?>
    </div>

    <form class="chatbot-form" method="post" action="index.php?controller=chatbot&action=ask">
      <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars((string)($_SESSION['_csrf_token'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
      <textarea name="message" rows="2" maxlength="1000" placeholder="Nhập câu hỏi cho chatbot..." required></textarea>
      <button type="submit" class="btn search">Gửi</button>
    </form>
  </section>
</main>

<?php include 'views/layout/footer.php'; ?>
