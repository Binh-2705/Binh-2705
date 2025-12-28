<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Sửa giảng viên</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <nav class="sidebar">
            <h2>QUẢN LÝ NHÂN SỰ</h2>
            <ul>
                <li><a href="index.php?controller=home&action=index">🏠 Trang chủ</a></li>
                <li><a href="index.php?controller=daotao&action=index">📚 Quản lý đào tạo</a></li>
                <li><a href="index.php?controller=daotao&action=giangvien" class="active">👨‍🏫 Quản lý giảng viên</a></li>
            </ul>
        </nav>

        <main class="main-content">
            <header>
                <h1>✏️ Sửa thông tin giảng viên</h1>
                <p style="color: #6c757d; margin-top: 5px;">
                    Mã giảng viên: <strong><?= $giangVien['MaGV'] ?></strong>
                </p>
            </header>

            <form method="POST" class="form-nv">
                <input type="hidden" name="MaGV" value="<?= $giangVien['MaGV'] ?>">

                <div class="form-group">
                    <label for="HoTen">Họ tên:</label>
                    <input type="text" name="HoTen" id="HoTen" 
                           value="<?= htmlspecialchars($giangVien['HoTen']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="ChuyenMon">Chuyên môn:</label>
                    <input type="text" name="ChuyenMon" id="ChuyenMon" 
                           value="<?= htmlspecialchars($giangVien['ChuyenMon'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="KinhNghiem">Kinh nghiệm (năm):</label>
                    <input type="number" name="KinhNghiem" id="KinhNghiem" 
                           min="0" max="50" value="<?= $giangVien['KinhNghiem'] ?? 0 ?>">
                </div>

                <div class="form-group">
                    <label for="Email">Email:</label>
                    <input type="email" name="Email" id="Email" 
                           value="<?= htmlspecialchars($giangVien['Email'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="SDT">Số điện thoại:</label>
                    <input type="text" name="SDT" id="SDT" 
                           value="<?= htmlspecialchars($giangVien['SDT'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="GhiChu">Ghi chú:</label>
                    <textarea name="GhiChu" id="GhiChu" rows="3"><?= htmlspecialchars($giangVien['GhiChu'] ?? '') ?></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn save">💾 Cập nhật</button>
                    <a href="index.php?controller=daotao&action=giangvien" class="btn back">↩️ Quay lại</a>
                </div>
            </form>
        </main>
    </div>
</body>
</html>