<?php include 'views/layout/header.php'; ?>
<?php include 'views/layout/sidebar.php'; ?>

    <main class="main-content">
        <header>
            <h1>✏️ Sửa thông tin Nhân viên</h1>
        </header>

        <form action="index.php?controller=nhanvien&action=luuSua" method="POST" class="form-nv">
            <input type="hidden" name="MaNV" value="<?= $nhanvien['MaNV'] ?>">

            <div class="form-group">
                <label>Họ và tên:</label>
                <input type="text" name="HoTen" value="<?= htmlspecialchars($nhanvien['HoTen']) ?>" required>
            </div>

            <div class="form-group">
                <label>Giới tính:</label>
                <select name="GioiTinh" required>
                    <option value="Nam" <?= $nhanvien['GioiTinh']=='Nam'?'selected':'' ?>>Nam</option>
                    <option value="Nữ" <?= $nhanvien['GioiTinh']=='Nữ'?'selected':'' ?>>Nữ</option>
                </select>
            </div>

            <div class="form-group">
                <label>Ngày sinh:</label>
                <input type="date" name="NgaySinh" value="<?= $nhanvien['NgaySinh'] ?>" required>
            </div>

            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="Email" value="<?= htmlspecialchars($nhanvien['Email']) ?>">
            </div>

            <div class="form-group">
                <label>Điện thoại:</label>
                <input type="text" name="DienThoai" value="<?= htmlspecialchars($nhanvien['DienThoai']) ?>" pattern="[0-9]{9,11}">
            </div>

            <div class="form-group">
                <label>Trạng thái:</label>
                <select name="TrangThai" required>
                    <option value="Đang làm" <?= $nhanvien['TrangThai']=='Đang làm'?'selected':'' ?>>Đang làm</option>
                    <option value="Nghỉ" <?= $nhanvien['TrangThai']=='Nghỉ'?'selected':'' ?>>Nghỉ</option>
                </select>
            </div>

            <div class="form-group">
                <label>Ngạch lương:</label>
                <select id="select-ngach" class="form-control" required>
                    <option value="">-- Chọn ngạch lương --</option>
                    <?php 
                    if ($dsNgach) {
                        mysqli_data_seek($dsNgach, 0); 
                        while ($n = mysqli_fetch_assoc($dsNgach)): 
                    ?>
                        <option value="<?= $n['MaNgach'] ?>" <?= ($maNgachHienTai == $n['MaNgach']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($n['TenNgach']) ?>
                        </option>
                    <?php 
                        endwhile; 
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <label>Bậc lương:</label>
                <select id="select-bac" name="MaBac" class="form-control" data-current-bac="<?= $nhanvien['MaBac'] ?>" required>
                    <?php 
                    if ($bacluongs) {
                        mysqli_data_seek($bacluongs, 0);
                        while ($bac = mysqli_fetch_assoc($bacluongs)): 
                    ?>
                        <option value="<?= $bac['MaBac'] ?>" <?= ($nhanvien['MaBac'] == $bac['MaBac']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($bac['TenBac']) ?> (HS: <?= $bac['HeSoLuong'] ?>)
                        </option>
                    <?php 
                        endwhile; 
                    }
                    ?>
                </select>
            </div>

            <div class="form-buttons">
                <button type="submit" class="btn edit">💾 Cập nhật</button>
                <a href="index.php?controller=nhanvien&action=index" class="btn cancel">↩️ Quay lại</a>
            </div>
        </form>
    </main>
<?php include 'views/layout/footer.php'; ?>