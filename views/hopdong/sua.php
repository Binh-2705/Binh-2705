<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>✏️ Sửa Hợp đồng</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <nav class="sidebar">
        <h2>HỆ THỐNG <br> QUẢN LÝ NHÂN SỰ</h2>
        <ul>
            <ul>
                <li><a href="index.php?controller=home&action=index">🏠 Trang chủ</a></li>
                <li><a href="index.php?controller=nhanvien&action=index">👥 Quản lý nhân viên</a></li>
                <li><a href="index.php?controller=phongban&action=index">🏢 Quản lý phòng ban</a></li>
                <li><a href="index.php?controller=luong&action=index">💰 Quản lý lương</a></li>
                <li><a href="index.php?controller=chamcong&action=index">🕒 Quản lý chấm công</a></li>
                <li><a href="index.php?controller=hopdong&action=index" class="active">📄 Quản lý hợp đồng</a></li>
                <li><a href="index.php?controller=nghiphep&action=index">📆 Quản lý nghỉ phép</a></li>
                <li><a href="index.php?controller=khenthuong&action=index">🏅 Khen thưởng - Kỷ luật</a></li>
                <li><a href="index.php?controller=thongke&action=index">📊 Thống kê - Báo cáo</a></li>
                <li><a href="index.php?controller=chucvu&action=index">🙍‍♂️ Quản lý chức vụ</a></li>
                <li><a href="index.php?controller=hoso&action=index">👤 Hồ sơ cá nhân</a></li>
                <li><a href="">💼 Quản lý tuyển dụng</a></li>
                <li><a href="">📚 Quản lý đào tạo</a></li>
                <li><a href="">🗂 Quản lý đăng nhập – phân quyền</a></li>
                <li><a href="index.php?controller=timkiem&action=index">🔎 Tìm kiếm nâng cao</a></li>
                <li><a href="index.php?controller=dangxuat&action=index">🚪 Đăng xuất</a></li>
            </ul>
        </ul>
    </nav>
    <main class="main-content">
        <header>
            <h1>✏️ Sửa Hợp đồng</h1>
        </header>

        <form action="index.php?controller=hopdong&action=luuSua" method="POST" class="form-nv">
            <div class="form-group">
                <label for="maHD">Mã hợp đồng:</label>
                <input type="text" id="maHD" name="maHD" value="<?= htmlspecialchars($hopdong['MaHD']) ?>" readonly required>
            </div>

            <div class="form-group">
                <label for="maNV">Nhân viên:</label>
                <select id="maNV" name="maNV" required>
                    <?php 
                    // 🔥 SỬA: Kiểm tra an toàn trước khi dùng mysqli_data_seek và lặp
                    if (isset($nhanviens) && is_object($nhanviens)) { 
                        // is_object($nhanviens) kiểm tra xem nó có phải là mysqli_result không
                        
                        // Đặt lại con trỏ kết quả về đầu để lặp lại
                        mysqli_data_seek($nhanviens, 0); 
                         while ($nv = mysqli_fetch_assoc($nhanviens)) {
                            // Lấy dữ liệu MaNV hiện tại từ $hopdong['MaNV']
                            $selected = ($nv['MaNV'] == $hopdong['MaNV']) ? 'selected' : '';
                            echo "<option value='{$nv['MaNV']}' {$selected}>" . htmlspecialchars($nv['HoTen']) . " ({$nv['MaNV']})</option>";
                        }
                    }
                    ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="loaiHopDong">Loại Hợp đồng:</label>
                <input type="text" id="loaiHopDong" name="loaiHopDong" value="<?= htmlspecialchars($hopdong['LoaiHopDong']) ?>" required>
            </div>

            <div class="form-group">
                <label for="ngayBatDau">Ngày Bắt đầu (Ngày Ký):</label>
                <input type="date" id="ngayBatDau" name="ngayBatDau" value="<?= $hopdong['NgayBatDau'] ?>" required>
            </div>

            <div class="form-group">
                <label for="ngayKetThuc">Ngày Kết thúc (Ngày Hết Hạn):</label>
                <input type="date" id="ngayKetThuc" name="ngayKetThuc" 
                       value="<?= !empty($hopdong['NgayKetThuc']) ? $hopdong['NgayKetThuc'] : '' ?>">
            </div>

            <div class="form-group">
                <label for="luongCoBan">Lương Cơ bản (VNĐ):</label>
                <input type="number" id="luongCoBan" name="luongCoBan" value="<?= htmlspecialchars($hopdong['LuongCoBan']) ?>" required min="0">
            </div>

            <div class="form-group">
                <label for="trangThai">Trạng thái:</label>
                <select id="trangThai" name="trangThai" required>
                    <option value="Còn hiệu lực" <?= $hopdong['TrangThai'] == 'Còn hiệu lực' ? 'selected' : '' ?>>Còn hiệu lực</option>
                    <option value="Đã hết hạn" <?= $hopdong['TrangThai'] == 'Đã hết hạn' ? 'selected' : '' ?>>Đã hết hạn</option>
                    <option value="Đã hủy" <?= $hopdong['TrangThai'] == 'Đã hủy' ? 'selected' : '' ?>>Đã hủy</option>
                </select>
            </div>

            <div class="form-buttons">
                <button type="submit" class="btn edit">💾 Cập nhật Hợp đồng</button>
                <a href="index.php?controller=hopdong&action=index" class="btn cancel">↩️ Quay lại</a>
            </div>
        </form>
    </main>
</div>
</body>
</html>