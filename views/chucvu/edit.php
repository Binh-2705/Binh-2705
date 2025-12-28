<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>✏️ Sửa Chức vụ</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
<nav class="sidebar">
     <h2>HỆ THỐNG <br> QUẢN LÝ NHÂN SỰ</h2>
        <ul>
            <ul>
                <li><a href="index.php?controller=home&action=index" >🏠 Trang chủ</a></li>
                <li><a href="index.php?controller=nhanvien&action=index">👥 Quản lý nhân viên</a></li>
                <li><a href="index.php?controller=phongban&action=index">🏢 Quản lý phòng ban</a></li>
                <li><a href="index.php?controller=luong&action=index">💰 Quản lý lương</a></li>
                <li><a href="index.php?controller=chamcong&action=index">🕒 Quản lý chấm công</a></li>
                <li><a href="index.php?controller=hopdong&action=index">📄 Quản lý hợp đồng</a></li>
                <li><a href="index.php?controller=nghiphep&action=index">📆 Quản lý nghỉ phép</a></li>
                <li><a href="index.php?controller=khenthuong&action=index">🏅 Khen thưởng - Kỷ luật</a></li>
                <li><a href="index.php?controller=thongke&action=index">📊 Thống kê - Báo cáo</a></li>
                <li><a href="index.php?controller=chucvu&action=index" class="active">🙍‍♂️ Quản lý chức vụ</a></li>
                <li><a href="index.php?controller=hoso&action=index">👤 Hồ sơ cá nhân</a></li>
                <li><a href="index.php?controller=tuyendung&action=index">💼 Quản lý tuyển dụng</a></li>
                <li><a href="index.php?controller=daotao&action=index">📚 Quản lý đào tạo</a></li>
                <li><a href="index.php?controller=taikhoan&action=index">🗂 Quản lý tài khoản</a></li>
                <li><a href="index.php?controller=timkiem&action=index">🔎 Tìm kiếm nâng cao</a></li>
                <li><a href="index.php?controller=dangnhap&action=dangxuat">🚪 Đăng xuất</a></li>
            </ul>
        </ul>
</nav>

<main class="main-content">
<header><h1>✏️ Sửa Chức vụ Mã: <?php echo htmlspecialchars($maCV ?? ''); ?></h1></header>

<form action="index.php?controller=chucvu&action=edit&id=<?php echo htmlspecialchars($maCV ?? ''); ?>" method="POST" class="form-nv">
    <?php if (isset($message)): ?>
        <p style="color: red; font-weight: bold; margin-bottom: 15px;"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>
    
    <div class="form-group">
        <label for="MaCV">Mã Chức vụ:</label>
        <input type="text" id="MaCV" name="MaCV" value="<?php echo htmlspecialchars($maCV ?? ''); ?>" disabled>
        <input type="hidden" name="MaCV_Hidden" value="<?php echo htmlspecialchars($maCV ?? ''); ?>">
    </div>

    <div class="form-group">
        <label for="TenChucVu">Tên Chức vụ:</label>
        <input type="text" id="TenChucVu" name="TenChucVu" required value="<?php echo htmlspecialchars($tenChucVu ?? ''); ?>">
    </div>

    <div class="form-buttons">
        <button type="submit" class="btn edit">💾 Cập nhật</button>
        <a href="index.php?controller=chucvu&action=index" class="btn cancel">↩️ Quay lại</a>
    </div>
</form>
</main>
</div>
</body>
</html>