<?php include 'views/layout/header.php'; ?>
<?php include 'views/layout/sidebar.php'; ?>
    <main class="main-content">
        <header>
    <h1>✏️ Sửa Quyết định (Mã: <?= $quyetdinh['MaKTKL'] ?>)</h1>
</header>

<form action="index.php?controller=khenthuong&action=luuSua" 
      method="POST" class="form-nv">

    <!-- ID ẨN -->
    <input type="hidden" name="MaKTKL" 
           value="<?= $quyetdinh['MaKTKL'] ?>">

    <!-- Nhân viên -->
    <div class="form-group">
        <label>Nhân viên:</label>
        <select name="MaNV" required>
            <?php 
            if (isset($nhanviens)):
                mysqli_data_seek($nhanviens, 0);
                while ($nv = mysqli_fetch_assoc($nhanviens)):
                    $selected = ($nv['MaNV'] == $quyetdinh['MaNV']) ? 'selected' : '';
            ?>
                <option value="<?= $nv['MaNV'] ?>" <?= $selected ?>>
                    <?= htmlspecialchars($nv['HoTen']) ?> (<?= $nv['MaNV'] ?>)
                </option>
            <?php 
                endwhile;
            endif;
            ?>
        </select>
    </div>

    <!-- Loại quyết định -->
    <div class="form-group">
        <label>Loại quyết định:</label>
        <select name="MaLoai" required>
            <?php 
            if (isset($loais)):
                mysqli_data_seek($loais, 0);
                while ($l = mysqli_fetch_assoc($loais)):
                    $selected = ($l['MaLoai'] == $quyetdinh['MaLoai']) ? 'selected' : '';
            ?>
                <option value="<?= $l['MaLoai'] ?>" <?= $selected ?>>
                    <?= $l['TenLoai'] ?> (<?= $l['Loai'] ?>)
                </option>
            <?php 
                endwhile;
            endif;
            ?>
        </select>
    </div>

    <!-- Ngày quyết định -->
    <div class="form-group">
        <label>Ngày quyết định:</label>
        <input type="date" name="NgayQuyetDinh"
               value="<?= $quyetdinh['NgayQuyetDinh'] ?>" required>
    </div>

    <!-- Hình thức -->
    <div class="form-group">
        <label>Hình thức:</label>
        <input type="text" name="HinhThuc"
               value="<?= htmlspecialchars($quyetdinh['HinhThuc']) ?>">
    </div>

    <!-- Số tiền -->
    <div class="form-group">
        <label>Số tiền (VNĐ):</label>
        <input type="number" name="SoTien"
               value="<?= $quyetdinh['SoTien'] ?>" min="0">
    </div>

    <!-- Lý do -->
    <div class="form-group">
        <label>Lý do:</label>
        <textarea name="LyDo" rows="3"><?= 
            htmlspecialchars($quyetdinh['LyDo']) 
        ?></textarea>
    </div>

    <!-- Ghi chú -->
    <div class="form-group">
        <label>Ghi chú:</label>
        <textarea name="GhiChu" rows="2"><?= 
            htmlspecialchars($quyetdinh['GhiChu']) 
        ?></textarea>
    </div>

    <div class="form-buttons">
        <button type="submit" class="btn edit">
            💾 Cập nhật
        </button>
        <a href="index.php?controller=khenthuong&action=index" 
           class="btn cancel">↩️ Quay lại</a>
    </div>

</form>
        </main>
<?php include 'views/layout/footer.php'; ?>