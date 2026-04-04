<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập hệ thống</title>
    <link rel="stylesheet" href="public/style1.css?v=20260404-2">
</head>
<body>

<?php
$authFlash = null;
if (!empty($_SESSION['success'])) {
    $authFlash = ['type' => 'success', 'text' => $_SESSION['success']];
    unset($_SESSION['success']);
} elseif (!empty($_SESSION['error'])) {
    $authFlash = ['type' => 'error', 'text' => $_SESSION['error']];
    unset($_SESSION['error']);
} elseif (!empty($_SESSION['message'])) {
    $authFlash = ['type' => 'success', 'text' => $_SESSION['message']];
    unset($_SESSION['message']);
}
?>

<div class="login-shell login-showcase">
<div class="login-container showcase-card">
    <header class="showcase-topbar">
        <div class="showcase-brand">HR Workspace</div>
        <nav class="showcase-nav">
            <a href="#">Home</a>
            <a href="#">Profile</a>
            <a href="#">About</a>
            <a href="#">Contact</a>
        </nav>
    </header>

    <div class="showcase-body">
        <section class="showcase-form-wrap">
            <div class="showcase-form-card">
                <h2>WELCOME</h2>

                <form method="post" class="showcase-form">
                    <input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars($_SESSION['_csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">

                    <label>Tên đăng nhập</label>
                    <input type="text" name="TenDangNhap" required minlength="3" maxlength="50" placeholder="Nhập tên đăng nhập">

                    <label>Mật khẩu</label>
                    <input type="password" name="MatKhau" required minlength="6" maxlength="50" placeholder="Nhập mật khẩu">

                    <div class="showcase-options">
                        <label class="remember-check">
                            <input type="checkbox"> Ghi nhớ
                        </label>
                        <a href="index.php?controller=dangnhap&action=quenMatKhau">Quên mật khẩu?</a>
                    </div>

                    <button type="submit">ĐĂNG NHẬP</button>
                </form>

                <?php if (isset($loi)): ?>
                    <p class="auth-alert auth-alert-error"><?php echo htmlspecialchars($loi, ENT_QUOTES, 'UTF-8'); ?></p>
                <?php endif; ?>
                <?php if (!empty($authFlash)): ?>
                    <p class="auth-alert auth-alert-<?php echo $authFlash['type'] === 'error' ? 'error' : 'success'; ?>"><?php echo htmlspecialchars($authFlash['text'], ENT_QUOTES, 'UTF-8'); ?></p>
                <?php endif; ?>
            </div>
        </section>

        <section class="showcase-visual">
            <div class="icon-row">
                <span>🔒</span>
                <span>🔑</span>
                <span>🌐</span>
                <span>⚙️</span>
            </div>
            <div class="showcase-image-frame">
                <img src="public/anh/anh.jpg" alt="Security Login Illustration">
            </div>
        </section>
    </div>
</div>

<!-- fallback compact auth pages keep same stylesheet classes -->

</div>

</body>
</html>
