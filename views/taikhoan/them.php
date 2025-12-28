<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">

<div class="main-content">
    <header>
        <h1>THÊM TÀI KHOẢN</h1>
    </header>

    <form method="post" class="form-nv">

        <div class="form-group">
            <label>Tên đăng nhập</label>
            <input name="user" required>
        </div>

        <div class="form-group">
            <label>Mật khẩu</label>
            <input type="password" name="pass" required>
        </div>

        <div class="form-group">
            <label>Vai trò</label>
            <select name="vaitro">
                <option value="Admin">Admin</option>
                <option value="NhanVien">Nhân viên</option>
               
                
            </select>
        </div>

        <div class="form-group">
            <label>Mã nhân viên</label>
            <input name="manv">
        </div>

        <div class="form-buttons">
            <button class="btn add">💾 Lưu</button>
            <a href="?controller=taikhoan" class="btn cancel">Hủy</a>
        </div>

    </form>
</div>

</div>

</body>
</html>