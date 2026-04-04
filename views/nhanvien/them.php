<?php include 'views/layout/header.php'; ?>
<?php include 'views/layout/sidebar.php'; ?>

    <main class="main-content">
        <header>
            <h1>➕ Thêm Nhân viên mới</h1>
        </header>

        <form action="index.php?controller=nhanvien&action=luuThem" method="POST" class="form-nv">

            <div class="form-group">
                <label>Họ và tên:</label>
                <input type="text" name="HoTen" required maxlength="120" placeholder="VD: Nguyễn Văn A" minlength="2">
            </div>

            <div class="form-group">
                <label>Giới tính:</label>
                <select name="GioiTinh" required>
                    <option value="">-- Chọn giới tính --</option>
                    <option value="Nam">Nam</option>
                    <option value="Nữ">Nữ</option>
                </select>
            </div>

            <div class="form-group">
                <label>Ngày sinh:</label>
                <input type="date" name="NgaySinh" required>
            </div>

            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="Email" maxlength="150" placeholder="VD: nguyenvana@example.com">
            </div>

            <div class="form-group">
                <label>Điện thoại:</label>
                <input type="tel" name="DienThoai" pattern="[0-9]{9,11}" placeholder="VD: 0901234567" maxlength="11">
            </div>

            <div class="form-group">
                <label>Trạng thái:</label>
                <select name="TrangThai" required>
                    <option value="Đang làm">Đang làm</option>
                    <option value="Nghỉ">Nghỉ</option>
                </select>
            </div>

            <div class="form-group">
                <label>Ngạch lương:</label>
                <select id="select-ngach" class="form-control" required>
                    <option value="">-- Chọn ngạch lương --</option>
                    <?php if (isset($dsNgach) && mysqli_num_rows($dsNgach) > 0): ?>
                        <?php while ($row_n = mysqli_fetch_assoc($dsNgach)): ?>
                            <option value="<?= $row_n['MaNgach'] ?>">
                                <?= $row_n['TenNgach'] ?>
                            </option>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Bậc lương:</label>
                <select id="select-bac" name="MaBac" class="form-control" required disabled>
                    <option value="">-- Vui lòng chọn ngạch trước --</option>
                </select>
            </div>

            <div class="form-buttons">
                <button type="submit" class="btn add">💾 Lưu</button>
                <a href="index.php?controller=nhanvien&action=index" class="btn cancel">↩️ Quay lại</a>
            </div>
        </form>
    </main>
<?php include 'views/layout/footer.php'; ?>