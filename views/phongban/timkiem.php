<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Tìm kiếm phòng ban</title>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>
<div class="container">
    <nav class="sidebar">
        <h2>QUẢN LÝ NHÂN SỰ</h2>
        <ul>
           <li><a href="index.php?controller=home&action=index" class="active">🏠 Trang chủ</a></li>
            <li><a href="index.php?controller=nhanvien&action=index">👥 Quản lý nhân viên</a></li>
            <li><a href="index.php?controller=phongban&action=index">🏢 Quản lý phòng ban</a></li>
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
            <li><a href="index.php?controller=phanquyen&action=index">🗂 Quản lý đăng nhập – phân quyền</a></li>
            <li><a href="index.php?controller=timkiem&action=index">🔎 Tìm kiếm nâng cao</a></li>
            <li><a href="index.php?controller=dangxuat&action=index">🚪 Đăng xuất</a></li>
        </ul>
    </nav>

    <main class="main-content">
        <header>
            <h1>🔎 Kết quả tìm kiếm: "<?php echo htmlspecialchars($keyword); ?>"</h1>
        </header>

        <div class="actions">
            <a href="index.php?controller=phongban&action=index" class="btn clear">↩️ Quay lại</a>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>Mã phòng ban</th>
                    <th>Tên phòng ban</th>
                    <th>Mô tả</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?= $row['MaPB'] ?></td>
                            <td><?= $row['TenPB'] ?></td>
                            <td><?= $row['MoTa'] ?></td>
                            <td>
                                <a href="index.php?controller=phongban&action=edit&mapb=<?= $row['MaPB'] ?>" class="btn edit">✏️ Sửa</a>
                                <a href="index.php?controller=phongban&action=delete&mapb=<?= $row['MaPB'] ?>" class="btn delete" onclick="return confirm('Bạn có chắc muốn xóa phòng ban này không?');">🗑️ Xóa</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4">❌ Không tìm thấy phòng ban nào phù hợp!</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>
</div>
</body>
</html>
