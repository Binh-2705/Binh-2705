<?php include 'views/layout/header.php'; ?>
<?php include 'views/layout/sidebar.php'; ?>

<div class="main-content">
    <header>
        <h1>SỬA TÀI KHOẢN</h1>
    </header>

    <form method="post" class="form-nv">

        <div class="form-group">
            <label>Tên đăng nhập</label>
            <input value="<?= $tk['TenDangNhap'] ?>" disabled>
        </div>

        <div class="form-group">
            <label>Vai trò</label>
            <select name="vaitro">
                <option <?= $tk['VaiTro']=='Admin'?'selected':'' ?>>Admin</option>
                <option <?= $tk['VaiTro']=='NhanVien'?'selected':'' ?>>NhanVien</option>
            </select>
        </div>

        <div class="form-group">
            <label>Mã nhân viên</label>
            <input name="manv" maxlength="10" value="<?= $tk['MaNV'] ?>">
        </div>

        <div class="form-buttons">
            <button class="btn edit">💾 Cập nhật</button>
            <a href="?controller=taikhoan" class="btn cancel">Hủy</a>
        </div>

    </form>
</div>

<?php include 'views/layout/footer.php'; ?>