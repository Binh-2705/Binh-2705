<?php include 'views/layout/header.php'; ?>
<?php include 'views/layout/sidebar.php'; ?>
    <main class="main-content">
        <header>
            <h1>➕ Thêm Quyết định mới</h1>
        </header>

        <form action="index.php?controller=khenthuong&action=luuThem" 
      method="POST" class="form-nv">

    <!-- Nhân viên -->
    <div class="form-group">
        <label>Nhân viên:</label>
        <select name="MaNV" required>
            <option value="">-- Chọn nhân viên --</option>
            <?php 
            if (isset($nhanviens) && mysqli_num_rows($nhanviens) > 0):
                while ($nv = mysqli_fetch_assoc($nhanviens)): ?>
                    <option value="<?= $nv['MaNV']; ?>">
                        <?= htmlspecialchars($nv['HoTen']) . " (" . $nv['MaNV'] . ")"; ?>
                    </option>
                <?php endwhile;
            endif;
            ?>
        </select>
    </div>

    <!-- Loại (lấy từ bảng loaikhenthuongkyluat) -->
    <div class="form-group">
        <label>Loại quyết định:</label>
        <select name="MaLoai" required>
            <option value="">-- Chọn loại --</option>
            <?php 
            if (isset($loais) && mysqli_num_rows($loais) > 0):
                while ($l = mysqli_fetch_assoc($loais)): ?>
                    <option value="<?= $l['MaLoai']; ?>">
                        <?= $l['TenLoai']; ?> 
                        (<?= $l['Loai']; ?>)
                    </option>
                <?php endwhile;
            endif;
            ?>
        </select>
    </div>

    <!-- Ngày quyết định -->
    <div class="form-group">
        <label>Ngày quyết định:</label>
        <input type="date" name="NgayQuyetDinh" required>
    </div>

    <!-- Hình thức -->
    <div class="form-group">
        <label>Hình thức:</label>
        <input type="text" name="HinhThuc" 
               placeholder="Ví dụ: Thưởng tiền mặt / Khiển trách">
    </div>

    <!-- Số tiền -->
    <div class="form-group">
        <label>Số tiền (VNĐ):</label>
        <input type="number" name="SoTien" min="0" value="0">
    </div>

    <!-- Lý do -->
    <div class="form-group">
        <label>Lý do:</label>
        <textarea name="LyDo" rows="3"
            placeholder="Nhập lý do khen thưởng/kỷ luật"></textarea>
    </div>

    <!-- Ghi chú -->
    <div class="form-group">
        <label>Ghi chú:</label>
        <textarea name="GhiChu" rows="2"></textarea>
    </div>

    <div class="form-buttons">
        <button type="submit" class="btn add">💾 Lưu</button>
        <a href="index.php?controller=khenthuong&action=index" 
           class="btn cancel">↩️ Hủy</a>
    </div>

</form>
        </main>
<?php include 'views/layout/footer.php'; ?>