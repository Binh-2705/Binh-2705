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

    <!-- SIDEBAR -->
    <div class="sidebar">
        <h2>HỆ THỐNG<br>NHÂN SỰ</h2>
        <ul>
            <li><a href="?controller=nhanvien">Nhân viên</a></li>
            <li><a class="active" href="?controller=taikhoan">Tài khoản</a></li>
            <li><a href="?controller=dangnhap&action=dangxuat">Đăng xuất</a></li>
        </ul>
    </div>

    <!-- MAIN -->
    <div class="main-content">
        <header>
            <h1>QUẢN LÝ TÀI KHOẢN</h1>
        </header>

        <div class="actions">
            <form>
                <input type="hidden" name="controller" value="taikhoan">
                <input class="search-box" name="key" placeholder="Tìm tài khoản">
                <button class="btn search">Tìm</button>
            </form>

            <a href="?controller=taikhoan&action=them" class="btn add">➕ Thêm</a>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên đăng nhập</th>
                    <th>Vai trò</th>
                    <th>Mã NV</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php while($r = mysqli_fetch_assoc($data)): ?>
                <tr>
                    <td><?= $r['MaTK'] ?></td>
                    <td><?= $r['TenDangNhap'] ?></td>
                    <td><?= $r['VaiTro'] ?></td>
                    <td><?= $r['MaNV'] ?></td>
                    <td>
                        <a class="btn edit" href="?controller=taikhoan&action=sua&id=<?= $r['MaTK'] ?>">Sửa</a>
                        <a class="btn delete" 
                           onclick="return confirm('Xóa tài khoản?')"
                           href="?controller=taikhoan&action=xoa&id=<?= $r['MaTK'] ?>">Xóa</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

    </div>
</div>

</body>
</html>