<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quên mật khẩu</title>
    <link rel="stylesheet" href="public/style1.css">
</head>
<body>

<div class="login-container">
    <div class="login-right" style="margin:auto">
        <h2>Quên mật khẩu 🔐</h2>
        <p>Nhập tên đăng nhập để đặt lại mật khẩu</p>

        <form method="post">
            <label>Tên đăng nhập</label>
            <input type="text" name="TenDangNhap" required>

            <button type="submit">LẤY LẠI MẬT KHẨU</button>
        </form>

        <?php if(isset($thongbao)) echo "<p style='color:green'>$thongbao</p>"; ?>
        <?php if(isset($loi)) echo "<p style='color:red'>$loi</p>"; ?>

        <a href="index.php?controller=dangnhap&action=login">← Quay lại đăng nhập</a>
    </div>
</div>

</body>
</html>
