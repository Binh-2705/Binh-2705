<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>➕ Thêm Hợp đồng mới</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <nav class="sidebar">
        <h2>HỆ THỐNG <br> QUẢN LÝ NHÂN SỰ</h2>
        <ul>
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
        </ul>
    </nav>
    <main class="main-content">
        <header><h1>Thêm Hợp đồng mới</h1></header>

        <form action="index.php?controller=hopdong&action=luuThem" method="POST" class="form-nv"> 
            
            <div class="form-group">
                <label for="maHD">Mã Hợp đồng (*):</label>
                <input type="text" id="maHD" name="maHD" required placeholder="Ví dụ: HD001">
            </div>

            <div class="form-group">
                <label for="maNV">Nhân viên:</label>
                <select id="maNV" name="maNV" required> <option value="">-- Chọn Nhân viên --</option>
                    <?php 
                    // $nhanviens được lấy từ HopDongController::them()
                    if (isset($nhanviens) && mysqli_num_rows($nhanviens) > 0): 
                        mysqli_data_seek($nhanviens, 0); // Đảm bảo bắt đầu từ đầu
                        while ($nv = mysqli_fetch_assoc($nhanviens)): ?>
                            <option value="<?php echo $nv['MaNV']; ?>">
                                <?php echo htmlspecialchars($nv['HoTen']) . ' (' . $nv['MaNV'] . ')'; ?>
                            </option>
                        <?php endwhile; 
                    endif;
                    ?>
                </select>
            </div>

            <div class="form-group">
                <label for="loaiHopDong">Loại Hợp đồng:</label>
                <input type="text" id="loaiHopDong" name="loaiHopDong" required placeholder="Ví dụ: Chính thức 1 năm"> </div>

            <div class="form-group">
                <label for="ngayBatDau">Ngày Bắt đầu (Ngày Ký):</label>
                <input type="date" id="ngayBatDau" name="ngayBatDau" required> </div>

            <div class="form-group">
                <label for="ngayKetThuc">Ngày Kết thúc (Để trống nếu HĐ không thời hạn):</label>
                <input type="date" id="ngayKetThuc" name="ngayKetThuc"> </div>

            <div class="form-group">
                <label for="luongCoBan">Lương Cơ bản (VNĐ):</label>
                <input type="number" id="luongCoBan" name="luongCoBan" required min="0" value="0"> </div>

            <div class="form-group">
                <label for="trangThai">Trạng thái:</label>
                <select id="trangThai" name="trangThai" required>
                    <option value="Còn hiệu lực">Còn hiệu lực</option>
                    <option value="Hết hạn">Hết hạn</option>
                    <option value="Đã hủy">Đã hủy</option>
                </select>
            </div>

            <div class="form-buttons">
                <button type="submit" class="btn add">💾 Lưu Hợp đồng</button> <a href="index.php?controller=hopdong&action=index" class="btn cancel">↩️ Hủy</a>
            </div>
        </form>
    </main>
</div>
</body>
</html>