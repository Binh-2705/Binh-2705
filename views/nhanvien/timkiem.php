<?php
// views/nhanvien/timkiem.php
// $nhanviens: mảng nhân viên (Array of associative arrays), $keyword: từ khóa tìm kiếm
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>🔍 Kết quả tìm kiếm</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <nav class="sidebar">
        <h2>QUẢN LÝ NHÂN SỰ</h2>
        <ul>
            <li><a href="index.php?controller=nhanvien&action=index">🏠 Trang chủ</a></li>
            <li><a href="index.php?controller=nhanvien&action=index" class="active">👥 Quản lý nhân viên</a></li>
            <li><a href="index.php?controller=phongban&action=index">🏢 Quản lý phòng ban</a></li>
            <li><a href="index.php?controller=luong&action=index">💰 Quản lý lương</a></li>
            <li><a href="index.php?controller=chamcong&action=index">🕒 Quản lý chấm công</a></li>
            <li><a href="index.php?controller=hopdong&action=index">📄 Quản lý hợp đồng</a></li>
            <li><a href="index.php?controller=nghiphep&action=index">📆 Quản lý nghỉ phép</a></li>
            <li><a href="index.php?controller=khenthuong&action=index">🏅 Khen thưởng - Kỷ luật</a></li>
            <li><a href="index.php?controller=thongke&action=index">📊 Thống kê - Báo cáo</a></li>
            <li><a href="index.php?controller=hoso&action=index">👤 Hồ sơ cá nhân</a></li>
            <li><a href="index.php?controller=timkiem&action=timkiem">🔎 Tìm kiếm nâng cao</a></li>
        </ul>
    </nav>

    <main class="main-content">
        <header>
            <h1>🔍 Kết quả tìm kiếm: "<?= htmlspecialchars($keyword) ?>"</h1>
        </header>

        <div class="actions">
            <a href="index.php?controller=nhanvien&action=index" class="btn clear">↩️ Quay lại</a>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>Mã NV</th>
                    <th>Họ tên</th>
                    <th>Giới tính</th>
                    <th>Ngày sinh</th>
                    <th>Phòng ban</th>
                    <th>Chức vụ</th>
                    <th>Mức lương</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if(!empty($nhanviens)): ?>
                    <?php foreach($nhanviens as $row): ?>
                        <tr>
                            <td><?= $row['MaNV'] ?></td>
                            <td><?= htmlspecialchars($row['HoTen']) ?></td>
                            <td><?= $row['GioiTinh'] ?></td>
                            <td><?= $row['NgaySinh'] ?></td>
                            <td><?= $row['TenPB'] ?? 'N/A' ?></td> 
                            <td><?= $row['TenChucVu'] ?? 'N/A' ?></td> 
                            <td><?= isset($row['Luong']) ? number_format($row['Luong'],0,',','.') . 'đ' : 'Chưa có' ?></td>
                            <td>
                                <a href="index.php?controller=nhanvien&action=sua&manv=<?= $row['MaNV'] ?>" class="btn edit">✏️ Sửa</a>
                                <a href="index.php?controller=nhanvien&action=xoa&manv=<?= $row['MaNV'] ?>"
                                   onclick="return confirm('Bạn có chắc muốn xóa nhân viên này không?');" 
                                   class="btn delete">🗑️ Xóa</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="8">❌ Không tìm thấy nhân viên nào phù hợp!</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>
</div>
</body>
</html>