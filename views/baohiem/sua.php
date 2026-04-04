<?php include 'views/layout/header.php'; ?>
<?php include 'views/layout/sidebar.php'; ?>
    <!-- MAIN -->
    <main class="main-content">

        <header>
            <h1>✏️ Sửa Bảo hiểm</h1>
        </header>

        <form method="POST">

        <div class="form-group">   
        <label>Nhân viên</label>
            <select name="MaNV" required>
                <?php while($nv = mysqli_fetch_assoc($nhanviens)): ?>
                    <option value="<?= $nv['MaNV'] ?>"
                        <?= ($nv['MaNV'] == $baohiem['MaNV']) ? 'selected' : '' ?>>
                        <?= $nv['HoTen'] ?>
                    </option>
                <?php endwhile; ?>
            </select>
            </div> 

            <div class="form-group">
                <label>Số BHXH</label>
                <input type="text" name="SoBHXH" value="<?= $baohiem['SoBHXH'] ?>" required>
            </div>

            <div class="form-group">
                <label>Loại bảo hiểm</label>
                <select name="LoaiBaoHiem">
                    <option value="BHXH" <?= $baohiem['LoaiBaoHiem']=='BHXH'?'selected':'' ?>>BHXH</option>
                    <option value="BHYT" <?= $baohiem['LoaiBaoHiem']=='BHYT'?'selected':'' ?>>BHYT</option>
                    <option value="BHTN" <?= $baohiem['LoaiBaoHiem']=='BHTN'?'selected':'' ?>>BHTN</option>
                </select>
            </div>

            <div class="form-group">
                <label>Ngày tham gia</label>
                <input type="date" name="NgayThamGia" value="<?= $baohiem['NgayThamGia'] ?>">
            </div>

            <div class="form-group">
                <label>Mức đóng</label>
                <input type="number" name="MucDong" step="0.01" value="<?= $baohiem['MucDong'] ?>">
            </div>

            <div class="form-group">
                <label>Công ty đóng</label>
                <input type="number" name="CongTyDong" readonly>
            </div>

            <div class="form-group">
                <label>Nhân viên đóng</label>
                <input type="number" name="NhanVienDong" readonly>
            </div>

            <div class="form-group">
                <label>Trạng thái</label>
                <select name="TrangThai">
                    <option value="Đang đóng" <?= $baohiem['TrangThai']=='Đang đóng'?'selected':'' ?>>Đang đóng</option>
                    <option value="Đã dừng" <?= $baohiem['TrangThai']=='Đã dừng'?'selected':'' ?>>Đã dừng</option>
                </select>
            </div>

            <br><br>
            <button type="submit" class="btn edit">💾 Cập nhật</button>

        </form>

    </main>
<?php include 'views/layout/footer.php'; ?>