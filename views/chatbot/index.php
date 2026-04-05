<?php include 'views/layout/header.php'; ?>
<?php include 'views/layout/sidebar.php'; ?>

<main class="main-content chatbot-page">
  <header class="chatbot-header">
    <h1>AI Chatbot Hỗ trợ nghiệp vụ</h1>
    <p>Chatbot có thể trả lời, gợi ý giải pháp và truy vấn dữ liệu nội bộ ở chế độ an toàn.</p>
  </header>

  <section class="chatbot-shell" data-chatbot-shell data-endpoint="index.php?controller=chatbot&action=ask" data-confirm-endpoint="index.php?controller=chatbot&action=confirmDraft">
    <div class="chatbot-messages" aria-live="polite">
      <article class="chatbot-msg bot">
        <div class="chatbot-bubble">
          Xin chào. Bạn có thể hỏi về nhân viên, phân công, nghỉ phép, hợp đồng hoặc nhờ tôi đề xuất hướng xử lý.
        </div>
      </article>
    </div>

    <div class="chatbot-quick-actions">
      <button type="button" class="chatbot-chip" data-prompt="Tổng số nhân viên hiện tại là bao nhiêu?">Tổng nhân viên</button>
      <button type="button" class="chatbot-chip" data-prompt="Có bao nhiêu đơn nghỉ phép chờ duyệt?">Đơn nghỉ phép chờ duyệt</button>
      <button type="button" class="chatbot-chip" data-prompt="Hãy đề xuất 3 việc ưu tiên cho phòng nhân sự tuần này.">Đề xuất ưu tiên</button>
    </div>

    <form class="chatbot-form" method="post" action="index.php?controller=chatbot&action=ask">
      <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars((string)($_SESSION['_csrf_token'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
      <textarea name="message" rows="2" maxlength="1000" placeholder="Nhập câu hỏi cho chatbot..." required></textarea>
      <button type="submit" class="btn search">Gửi</button>
    </form>
  </section>
</main>

<?php include 'views/layout/footer.php'; ?>
