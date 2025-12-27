<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>➕ Thêm Quyết định Khen thưởng/Kỷ luật</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <nav class="sidebar">
        <h2>HỆ THỐNG <br> QUẢN LÝ NHÂN SỰ</h2>
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
            <h1>➕ Thêm Quyết định mới</h1>
        </header>

        <form action="index.php?controller=khenthuong&action=luuThem" method="POST" class="form-nv">
            
            <div class="form-group">
                <label for="maQD">Mã Quyết định (*):</label>
                <input type="text" id="maQD" name="maQD" required placeholder="Ví dụ: KT001 hoặc KL001">
            </div>

            <div class="form-group">
                <label for="maNV">Nhân viên:</label>
                <select id="maNV" name="maNV" required> 
                    <option value="">-- Chọn Nhân viên --</option>
                    <?php 
                    if (isset($nhanviens) && mysqli_num_rows($nhanviens) > 0): 
                        mysqli_data_seek($nhanviens, 0);
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
                <label for="loaiQD">Loại Quyết định:</label>
                <select id="loaiQD" name="loaiQD" required>
                    <option value="Khen thưởng">Khen thưởng</option>
                    <option value="Kỷ luật">Kỷ luật</option>
                </select>
            </div>

            <div class="form-group">
                <label for="ngayQD">Ngày ra Quyết định (NgayRaQD):</label>
                <input type="date" id="ngayQD" name="ngayQD" required>
            </div>

            <div class="form-group">
                <label for="tieuDe">Tiêu đề (TieuDe):</label>
                <input type="text" id="tieuDe" name="tieuDe" required placeholder="Ví dụ: Thưởng hiệu suất Quý 1">
            </div>

            <div class="form-group">
                <label for="noiDung">Nội dung (NoiDung):</label>
                <textarea id="noiDung" name="noiDung" rows="3" required placeholder="Chi tiết lý do khen thưởng/kỷ luật"></textarea>
            </div>

            <div class="form-group">
                <label for="giaTri">Giá trị (GiaTri - VNĐ/USD):</label>
                <input type="number" id="giaTri" name="giaTri" required min="0" value="0">
            </div>


            <div class="form-buttons">
                <button type="submit" class="btn add">💾 Lưu Quyết định</button>
                <a href="index.php?controller=khenthuong&action=index" class="btn cancel">↩️ Hủy</a>
            </div>
        </form>
        </main>
</div>
</body>
</html>