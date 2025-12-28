<?php
// views/nhanvien/sua.php
// Biến $nhanvien, $phongbans, và $chucvus được controller truyền vào
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>✏️ Sửa thông tin Nhân viên</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <nav class="sidebar">
            <h2>HỆ THỐNG <br> QUẢN LÝ NHÂN SỰ</h2>
        <ul>
            <ul>
                <li><a href="index.php?controller=home&action=index" >🏠 Trang chủ</a></li>
                <li><a href="index.php?controller=nhanvien&action=index" class="active">👥 Quản lý nhân viên</a></li>
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
                <li><a href="index.php?controller=taikhoan&action=index">🗂 Quản lý tài khoản</a></li>
                <li><a href="index.php?controller=timkiem&action=index">🔎 Tìm kiếm nâng cao</a></li>
               <li><a href="index.php?controller=dangnhap&action=dangxuat">🚪 Đăng xuất</a></li>
            </ul>
        </ul>
        </nav>

        <main class="main-content">
            <header>
                <h1>✏️ Sửa thông tin Nhân viên</h1>
            </header>

            <form action="index.php?controller=nhanvien&action=luuSua" method="POST" class="form-nv">
                <input type="hidden" name="manv" value="<?= $nhanvien['MaNV'] ?>">

                <div class="form-group">
                    <label for="hoten">Họ và tên:</label>
                    <input type="text" id="hoten" name="hoten" value="<?= htmlspecialchars($nhanvien['HoTen']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="gioitinh">Giới tính:</label>
                    <select id="gioitinh" name="gioitinh" required>
                        <option value="Nam" <?= $nhanvien['GioiTinh']=='Nam'?'selected':'' ?>>Nam</option>
                        <option value="Nữ" <?= $nhanvien['GioiTinh']=='Nữ'?'selected':'' ?>>Nữ</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="ngaysinh">Ngày sinh:</label>
                    <input type="date" id="ngaysinh" name="ngaysinh" value="<?= $nhanvien['NgaySinh'] ?>" required>
                </div>

                <div class="form-group">
                    <label for="phongban">Phòng ban:</label>
                    <select id="phongban" name="phongban" required>
                        <option value="">-- Chọn phòng ban --</option>
                        <?php while($pb = mysqli_fetch_assoc($phongbans)) {
                            // SỬA LỖI: So sánh với MaPB trong DB
                            $selected = ($nhanvien['MaPB'] == $pb['MaPB']) ? 'selected' : ''; 
                        ?>
                          <option value="<?= $pb['MaPB'] ?>" <?= $selected ?>><?= htmlspecialchars($pb['TenPB']) ?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="chucvu">Chức vụ:</label>
                    <select id="chucvu" name="chucvu" required>
                        <option value="">-- Chọn chức vụ --</option>
                        <?php 
                        $chucvus = $chucvus ?? [];
                        foreach ($chucvus as $cv): 
                            // SỬA LỖI: So sánh với MaCV trong DB
                            $selected = ($nhanvien['MaCV'] == $cv['MaCV']) ? 'selected' : '';
                        ?>
                            <option value="<?= $cv['MaCV'] ?>" <?= $selected ?>>
                                <?= htmlspecialchars($cv['TenChucVu']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-buttons">
                    <button type="submit" class="btn edit">💾 Cập nhật</button>
                    <a href="index.php?controller=nhanvien&action=index" class="btn cancel">↩️ Quay lại</a>
                </div>
            </form>
        </main>
    </div>
</body>
</html>