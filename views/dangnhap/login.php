<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng nhập hệ thống</title>
    <link rel="stylesheet" href="public/style1.css">
</head>
<body>

<div class="login-container">

    <!-- CỘT TRÁI -->
    <div class="login-left">
        <img src="public/anh/anh.jpg" alt="Login">
        <h2>Welcome</h2>
    </div>

    <!-- CỘT PHẢI -->
    <div class="login-right">
        <h2>Xin chào 👋</h2>
        <p>Rất vui khi gặp lại bạn</p>

        <form method="post">
            <label>Tên đăng nhập</label>
            <input type="text" name="TenDangNhap">



            <label>Mật khẩu</label>
            <input type="password" name="MatKhau">

            <div class="options">
                <label>
                    <input type="checkbox"> Ghi nhớ đăng nhập
                </label>
                <a href="index.php?controller=dangnhap&action=quenMatKhau">Quên mật khẩu?</a>

            </div>

            <button type="submit">ĐĂNG NHẬP</button>
        </form>

        <?php if(isset($loi)) echo "<p style='color:red'>$loi</p>"; ?>
    </div>

</div>

</body>
</html>
