<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>➕ Thêm ứng viên mới</title>
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
                <li><a href="index.php?controller=chucvu&action=index">🙍‍♂️ Quản lý chức vụ</a></li>
                <li><a href="index.php?controller=hoso&action=index">👤 Hồ sơ cá nhân</a></li>
                <li><a href="index.php?controller=tuyendung&action=index" class="active">💼 Quản lý tuyển dụng</a></li>
                <li><a href="index.php?controller=daotao&action=index">📚 Quản lý đào tạo</a></li>
                <li><a href="index.php?controller=taikhoan&action=index">🗂 Quản lý tài khoản</a></li>
                
               <li><a href="index.php?controller=dangnhap&action=dangxuat">🚪 Đăng xuất</a></li>
            </ul>
        </ul>
    </nav>

    <main class="main-content">
        <header><h1>➕ Thêm Ứng viên mới</h1></header>

        <form action="index.php?controller=tuyendung&action=add" method="POST" class="form-nv">
            
            <?php if (isset($error)): ?>
                <p style="color: red; font-weight: bold; margin-bottom: 15px;"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>

            <div class="form-group">
                <label for="HoTen">Họ và Tên:</label>
                <input type="text" id="HoTen" name="HoTen" required placeholder="Nhập họ tên ứng viên">
            </div>

            <div class="form-group">
                <label for="Email">Email:</label>
                <input type="email" id="Email" name="Email" placeholder="example@gmail.com">
            </div>

            <div class="form-group">
                <label for="SoDienThoai">Số điện thoại:</label>
                <input type="text" id="SoDienThoai" name="SoDienThoai" placeholder="Nhập số điện thoại">
            </div>

            <div class="form-group">
                <label for="ViTriUngTuyen">Vị trí ứng tuyển:</label>
                <input type="text" id="ViTriUngTuyen" name="ViTriUngTuyen" required placeholder="Ví dụ: Nhân viên IT, Kế toán...">
            </div>

            <div class="form-group">
                <label for="NgayNop">Ngày nộp hồ sơ:</label>
                <input type="date" id="NgayNop" name="NgayNop" value="<?php echo date('Y-m-d'); ?>">
            </div>

            <div class="form-group">
                <label for="GhiChu">Ghi chú / Đánh giá:</label>
                <textarea id="GhiChu" name="GhiChu" rows="4" style="width: 100%; border: 1px solid #ccc; border-radius: 4px; padding: 8px;"></textarea>
            </div>

            <div class="form-buttons">
                <button type="submit" class="btn add">💾 Lưu hồ sơ</button>
                <a href="index.php?controller=tuyendung&action=index" class="btn cancel">↩️ Quay lại</a>
            </div>
        </form>
    </main>
</div>
</body>
</html>