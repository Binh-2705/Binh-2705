<?php include 'views/layout/header.php'; ?>
<?php include 'views/layout/sidebar.php'; ?>

<div class="main-content">
    <header>
        <h1>THÊM TÀI KHOẢN</h1>
    </header>

    <form method="post" class="form-nv">
        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($_SESSION['_csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">

        <div class="form-group">
            <label>Tên đăng nhập</label>
            <input name="user" required maxlength="50" minlength="4" placeholder="VD: nguyenvana">
        </div>

        <div class="form-group">
            <label>Mật khẩu</label>
            <input type="password" name="pass" required minlength="6" maxlength="50" placeholder="Tối thiểu 6 ký tự">
        </div>

        <div class="form-group">
            <label>Vai trò</label>
            <select name="vaitro" required>
                <option value="">-- Chọn vai trò --</option>
                <option value="Admin">Admin</option>
                <option value="NhanVien">Nhân viên</option>
                <option value="HR">HR</option>
                <option value="KeToan">Kế toán</option>
                <option value="QuanLy">Quản lý</option>
            </select>
        </div>

        <div class="form-group">
            <label>Mã nhân viên</label>
            <input name="manv" maxlength="10" placeholder="VD: NV001">
        </div>

        <div class="form-buttons">
            <button class="btn add">💾 Lưu</button>
            <a href="?controller=taikhoan" class="btn cancel">Hủy</a>
        </div>

    </form>
</div>

<?php include 'views/layout/footer.php'; ?>