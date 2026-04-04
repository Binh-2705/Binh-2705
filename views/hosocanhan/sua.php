<?php include 'views/layout/header.php'; ?>
<?php include 'views/layout/sidebar.php'; ?>

    <!-- CONTENT -->
    <main class="main-content">

        <h1>✏️ Sửa hồ sơ nhân viên</h1>

        <?php $data = mysqli_fetch_assoc($hoso); ?>

        <form method="POST">

            <!-- Nhân viên -->
            <div class="form-group">
                <label>Nhân viên:</label>
                <select name="MaNV" disabled>
                    <?php while($nv = mysqli_fetch_assoc($nhanvien)): ?>
                        <option value="<?= $nv['MaNV'] ?>"
                            <?= ($nv['MaNV'] == $data['MaNV']) ? 'selected' : '' ?>>
                            <?= $nv['HoTen'] ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- CCCD -->
                <div class="form-group">    
            <label>CCCD:</label>
            <input type="text" name="CCCD" value="<?= $data['CCCD'] ?>">
                </div>

            <!-- Nơi cấp -->
             <div class="form-group">
            <label>Nơi cấp:</label>
            <input type="text" name="NoiCap" value="<?= $data['NoiCap'] ?>">
             </div>

            <!-- Ngày cấp -->
             <div class="form-group">
            <label>Ngày cấp:</label>
            <input type="date" name="NgayCap" value="<?= $data['NgayCap'] ?>">
             </div>

            <!-- Địa chỉ -->
             <div class="form-group">
            <label>Địa chỉ:</label>
            <textarea name="DiaChi"><?= $data['DiaChi'] ?></textarea>
             </div>

            <!-- Dân tộc -->
             <div class="form-group">
            <label>Dân tộc:</label>
            <input type="text" name="DanToc" value="<?= $data['DanToc'] ?>">
             </div>

            <!-- Tôn giáo -->
             <div class="form-group">
            <label>Tôn giáo:</label>
            <input type="text" name="TonGiao" value="<?= $data['TonGiao'] ?>">
             </div>

            <!-- Trình độ -->
             <div class="form-group">
            <label>Trình độ:</label>
            <input type="text" name="TrinhDo" value="<?= $data['TrinhDo'] ?>">
             </div>

            <!-- Chuyên môn -->
             <div class="form-group">
            <label>Chuyên môn:</label>
            <input type="text" name="ChuyenMon" value="<?= $data['ChuyenMon'] ?>">
             </div>

            <!-- Ngày vào làm -->
             <div class="form-group">
            <label>Ngày vào làm:</label>
            <input type="date" name="NgayVaoLam" value="<?= $data['NgayVaoLam'] ?>">
             </div>

            <!-- Phòng ban -->
             <div class="form-group">
            <label>Phòng ban:</label>
            <select name="MaPB">
                <?php while($pb = mysqli_fetch_assoc($phongban)): ?>
                    <option value="<?= $pb['MaPB'] ?>"
                        <?= ($pb['MaPB'] == $data['MaPB']) ? 'selected' : '' ?>>
                        <?= $pb['TenPhongBan'] ?>
                    </option>
                <?php endwhile; ?>
            </select>
             </div>

            <!-- Chức vụ -->
             <div class="form-group">
            <label>Chức vụ:</label>
            <select name="MaCV">
                <?php while($cv = mysqli_fetch_assoc($chucvu)): ?>
                    <option value="<?= $cv['MaCV'] ?>"
                        <?= ($cv['MaCV'] == $data['MaCV']) ? 'selected' : '' ?>>
                        <?= $cv['TenChucVu'] ?>
                    </option>
                <?php endwhile; ?>
            </select>
             </div>

            <!-- Hôn nhân -->
             <div class="form-group">
            <label>Trạng thái hôn nhân:</label>
            <select name="TrangThaiHonNhan">
                <option value="Độc thân" <?= ($data['TrangThaiHonNhan']=='Độc thân')?'selected':'' ?>>Độc thân</option>
                <option value="Đã kết hôn" <?= ($data['TrangThaiHonNhan']=='Đã kết hôn')?'selected':'' ?>>Đã kết hôn</option>
            </select>
             </div>

            <br><br>

            <!-- Button -->
            <button type="submit" class="btn edit">💾 Cập nhật</button>
            <a href="index.php?controller=hosocanhan&action=index" class="btn">⬅ Quay lại</a>

        </form>

    </main>
  <?php include 'views/layout/footer.php'; ?>