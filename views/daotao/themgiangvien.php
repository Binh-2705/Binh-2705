<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thêm giảng viên</title>
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
                <h1> Thêm giảng viên mới</h1>
            </header>

            <form method="POST" class="form-nv">
                <div class="form-group">
                    <label for="MaGV">Mã giảng viên:</label>
                    <input type="text" name="MaGV" id="MaGV" required 
                           placeholder="VD: GV001">
                </div>

                <div class="form-group">
                    <label for="HoTen">Họ tên:</label>
                    <input type="text" name="HoTen" id="HoTen" required>
                </div>

                <div class="form-group">
                    <label for="ChuyenMon">Chuyên môn:</label>
                    <input type="text" name="ChuyenMon" id="ChuyenMon" 
                           placeholder="VD: Quản trị nhân sự, Kỹ năng mềm...">
                </div>

                <div class="form-group">
                    <label for="KinhNghiem">Kinh nghiệm (năm):</label>
                    <input type="number" name="KinhNghiem" id="KinhNghiem" 
                           min="0" max="50" value="0">
                </div>

                <div class="form-group">
                    <label for="Email">Email:</label>
                    <input type="email" name="Email" id="Email" 
                           placeholder="example@company.com">
                </div>

                <div class="form-group">
                    <label for="SDT">Số điện thoại:</label>
                    <input type="text" name="SDT" id="SDT" 
                           placeholder="VD: 0912345678">
                </div>

                <div class="form-group">
                    <label for="GhiChu">Ghi chú:</label>
                    <textarea name="GhiChu" id="GhiChu" rows="3"></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn save">💾 Lưu</button>
                    <a href="index.php?controller=daotao&action=giangvien" class="btn back">↩️ Quay lại</a>
                </div>
            </form>
        </main>
    </div>
</body>
</html>