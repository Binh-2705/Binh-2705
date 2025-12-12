<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>✏️ Sửa Quyết định Khen thưởng/Kỷ luật</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <nav class="sidebar">
        <h2>HỆ THỐNG <br> QUẢN LÝ NHÂN SỰ</h2>
        <ul>
            <li><a href="index.php?controller=home&action=index">🏠 Trang chủ</a></li>
            <li><a href="index.php?controller=nhanvien&action=index">👥 Quản lý nhân viên</a></li>
            <li><a href="index.php?controller=phongban&action=index">🏢 Quản lý phòng ban</a></li>
            <li><a href="index.php?controller=luong&action=index">💰 Quản lý lương</a></li>
            <li><a href="index.php?controller=chamcong&action=index">🕒 Quản lý chấm công</a></li>
            <li><a href="index.php?controller=hopdong&action=index">📄 Quản lý hợp đồng</a></li> 
            <li><a href="index.php?controller=nghiphep&action=index">📆 Quản lý nghỉ phép</a></li> 
            <li><a href="index.php?controller=khenthuong&action=index" class="active">🏅 Khen thưởng - Kỷ luật</a></li>
            <li><a href="index.php?controller=thongke&action=index">📊 Thống kê - Báo cáo</a></li>
            <li><a href="index.php?controller=chucvu&action=index">🙍‍♂️ Quản lý chức vụ</a></li>
            <li><a href="index.php?controller=hoso&action=index">👤 Hồ sơ cá nhân</a></li>
            <li><a href="">💼 Quản lý tuyển dụng</a></li>
            <li><a href="">📚 Quản lý đào tạo</a></li>
            <li><a href="">🗂 Quản lý đăng nhập – phân quyền</a></li>
            <li><a href="index.php?controller=timkiem&action=index">🔎 Tìm kiếm nâng cao</a></li>
            <li><a href="index.php?controller=dangxuat&action=index">🚪 Đăng xuất</a></li>
        </ul>
    </nav>
    <main class="main-content">
        <header><h1>✏️ Sửa Quyết định (Mã: <?= htmlspecialchars($quyetdinh['MaQuyetDinh']) ?>)</h1></header>

        <form action="index.php?controller=khenthuong&action=luuSua" method="POST" class="form-nv">
            
            <div class="form-group">
                <label for="maQD">Mã Quyết định:</label>
                <input type="text" id="maQD" name="maQD" value="<?= htmlspecialchars($quyetdinh['MaQuyetDinh']) ?>" readonly required>
            </div>

            <div class="form-group">
                <label for="maNV">Nhân viên:</label>
                <select id="maNV" name="maNV" required>
                    <?php 
                    // Mã nhân viên hiện tại sẽ được chọn tự động
                    if (isset($nhanviens) && is_object($nhanviens)) {
                        mysqli_data_seek($nhanviens, 0); // Đảm bảo lặp từ đầu
                        while ($nv = mysqli_fetch_assoc($nhanviens)) {
                            $selected = ($nv['MaNV'] == $quyetdinh['MaNV']) ? 'selected' : '';
                            echo "<option value='{$nv['MaNV']}' {$selected}>" . htmlspecialchars($nv['HoTen']) . " ({$nv['MaNV']})</option>";
                        }
                    }
                    ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="loaiQD">Loại Quyết định:</label>
                <select id="loaiQD" name="loaiQD" required>
                    <option value="Khen thưởng" <?= $quyetdinh['LoaiQD'] == 'Khen thưởng' ? 'selected' : '' ?>>Khen thưởng</option>
                    <option value="Kỷ luật" <?= $quyetdinh['LoaiQD'] == 'Kỷ luật' ? 'selected' : '' ?>>Kỷ luật</option>
                </select>
            </div>

            <div class="form-group">
                <label for="ngayQD">Ngày ra Quyết định (NgayRaQD):</label>
                <input type="date" id="ngayQD" name="ngayQD" value="<?= $quyetdinh['NgayRaQD'] ?>" required>
            </div>

            <div class="form-group">
                <label for="tieuDe">Tiêu đề (TieuDe):</label>
                <input type="text" id="tieuDe" name="tieuDe" value="<?= htmlspecialchars($quyetdinh['TieuDe']) ?>" required>
            </div>

            <div class="form-group">
                <label for="noiDung">Nội dung (NoiDung):</label>
                <textarea id="noiDung" name="noiDung" rows="3" required><?= htmlspecialchars($quyetdinh['NoiDung']) ?></textarea>
            </div>

            <div class="form-group">
                <label for="giaTri">Giá trị (GiaTri - VNĐ/USD):</label>
                <input type="number" id="giaTri" name="giaTri" value="<?= $quyetdinh['GiaTri'] ?>" required>
            </div>


            <div class="form-buttons">
                <button type="submit" class="btn edit">💾 Cập nhật Quyết định</button>
                <a href="index.php?controller=khenthuong&action=index" class="btn cancel">↩️ Quay lại</a>
            </div>
        </form>
        </main>
</div>
</body>
</html>