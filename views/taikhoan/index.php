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
        <h2>HỆ THỐNG <br> QUẢN LÝ NHÂN SỰ</h2>
        <ul>
            <ul>
                <li><a href="index.php?controller=home&action=index" >🏠 Trang chủ</a></li>
                <li><a href="index.php?controller=nhanvien&action=index">👥 Quản lý nhân viên</a></li>
                <li><a href="index.php?controller=phongban&action=index" >🏢 Quản lý phòng ban</a></li>
                <li><a href="index.php?controller=luong&action=index">💰 Quản lý lương</a></li>
                <li><a href="index.php?controller=chamcong&action=index">🕒 Quản lý chấm công</a></li>
                <li><a href="index.php?controller=hopdong&action=index">📄 Quản lý hợp đồng</a></li>
                <li><a href="index.php?controller=nghiphep&action=index">📆 Quản lý nghỉ phép</a></li>
                <li><a href="index.php?controller=khenthuong&action=index">🏅 Khen thưởng - Kỷ luật</a></li>
                <li><a href="index.php?controller=thongke&action=index">📊 Thống kê - Báo cáo</a></li>
                <li><a href="index.php?controller=chucvu&action=index">🙍‍♂️ Quản lý chức vụ</a></li>
                <li><a href="index.php?controller=hoso&action=index">👤 Hồ sơ cá nhân</a></li>
                <li><a href="index.php?controller=tuyendung&action=index">💼 Quản lý tuyển dụng</a></li>
                <li><a href="index.php?controller=daotao&action=index">📚 Quản lý đào tạo</a></li>
                <li><a href="index.php?controller=taikhoan&action=index" class="active">🗂 Quản lý tài khoản</a></li>
              
                <li><a href="index.php?controller=dangnhap&action=dangxuat">🚪 Đăng xuất</a></li>
            </ul>
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