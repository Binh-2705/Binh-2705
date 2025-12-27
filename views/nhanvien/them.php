<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Nhân Viên</title>
    <link rel="stylesheet" href="style.css">
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
            <h1>➕ Thêm Nhân viên mới</h1>
        </header>

        <form action="index.php?controller=nhanvien&action=luuThem" method="POST" class="form-nv">
            <div class="form-group">
                <label for="manv">Mã nhân viên:</label>
                <input type="text" id="manv" name="manv" required>
            </div>

            <div class="form-group">
                <label for="hoten">Họ và tên:</label>
                <input type="text" id="hoten" name="hoten" required>
            </div>

            <div class="form-group">
                <label for="gioitinh">Giới tính:</label>
                <select id="gioitinh" name="gioitinh" required>
                    <option value="">-- Chọn giới tính --</option>
                    <option value="Nam">Nam</option>
                    <option value="Nữ">Nữ</option>
                </select>
            </div>

            <div class="form-group">
                <label for="ngaysinh">Ngày sinh:</label>
                <input type="date" id="ngaysinh" name="ngaysinh" required>
            </div>

            <div class="form-group">
                <label for="phongban">Phòng ban:</label>
                <select id="phongban" name="phongban" required>
                    <option value="">-- Chọn phòng ban --</option>
                    <?php
                    // Lặp qua $phongbans (ResultSet)
                    if (isset($phongbans) && $phongbans && mysqli_num_rows($phongbans) > 0) {
                        while ($row = mysqli_fetch_assoc($phongbans)) {
                            echo "<option value='{$row['MaPB']}'>{$row['TenPB']}</option>";
                        }
                    } else {
                        echo "<option value=''>Không có phòng ban</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <label for="chucvu">Chức vụ:</label>
                <select id="chucvu" name="chucvu" required>
                    <option value="">-- Chọn chức vụ --</option>
                    <?php 
                    // Lặp qua $chucvus (Array) đã được Controller truyền từ Model
                    $chucvus = $chucvus ?? [];
                    if (!empty($chucvus)):
                        foreach ($chucvus as $cv): ?>
                            <option value="<?php echo $cv['MaCV']; ?>">
                                <?php echo htmlspecialchars($cv['TenChucVu']); ?>
                            </option>
                        <?php endforeach; 
                    endif;
                    ?>
                </select>
            </div>
            <div class="form-buttons">
                <button type="submit" class="btn add">💾 Lưu</button>
                <a href="index.php?controller=nhanvien&action=index" class="btn cancel">↩️ Quay lại</a>
            </div>
        </form>
    </main>
</div>
</body>
</html>