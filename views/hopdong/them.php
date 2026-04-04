<?php include 'views/layout/header.php'; ?>
<?php include 'views/layout/sidebar.php'; ?>

    <!-- MAIN -->
    <main class="main-content">
        <header>
            <h1>➕ Thêm Hợp đồng mới</h1>
        </header>

        <form action="index.php?controller=hopdong&action=luuThem"
              method="POST"
              class="form-nv">

            <!-- SỐ HỢP ĐỒNG -->
            <div class="form-group">
                <label>Số hợp đồng *</label>
                <input type="text" name="SoHopDong" required placeholder="VD: HD2024-001" maxlength="50" minlength="2">
            </div>

            <!-- NHÂN VIÊN -->
            <div class="form-group">
                <label>Nhân viên *</label>
                <select name="MaNV" id="MaNV" required>
    <option value="">-- Chọn nhân viên --</option>
    <?php while ($nv = mysqli_fetch_assoc($nhanviens)): ?>
       <option value="<?= $nv['MaNV'] ?>" 
        data-mabac="<?= htmlspecialchars($nv['MaBac'] ?? '') ?>"> 
    <?= htmlspecialchars($nv['HoTen']) ?> (<?= $nv['MaNV'] ?>)
</option>
    <?php endwhile; ?>
</select>
                
            </div>

            <!-- LOẠI HỢP ĐỒNG -->
            <div class="form-group">
                <label>Loại hợp đồng *</label>
                <select name="LoaiHopDong" required>
                    <option value="Thử việc">Thử việc</option>
                    <option value="Xác định thời hạn">Xác định thời hạn</option>
                    <option value="Không xác định thời hạn">Không xác định thời hạn</option>
                </select>
            </div>

            <!-- NGÀY -->
            <div class="form-group">
                <label>Ngày ký *</label>
                <input type="date" name="NgayKy" value="<?= date('Y-m-d') ?>" required>
            </div>

            <div class="form-group">
                <label>Ngày bắt đầu *</label>
                <input type="date" name="NgayBatDau" required>
            </div>

            <div class="form-group">
                <label>Ngày kết thúc</label>
                <input type="date" name="NgayKetThuc">
            </div>

            <!-- BẬC LƯƠNG -->
            <div class="form-group">
                <label>Bậc lương *</label>
                <select name="MaBac" id="MaBac" required>
                    <option value="">-- Chọn bậc lương --</option>
                    <?php while ($b = mysqli_fetch_assoc($bacluongs)): ?>
                       <option value="<?= $b['MaBac'] ?>"
        data-luong="<?= $b['LuongCoSo'] ?? 0 ?>"
        data-heso="<?= $b['HeSoLuong'] ?? 0 ?>">
    <?= htmlspecialchars($b['TenBac']) ?> – <?= number_format($b['LuongCoSo'] ?? 0) ?> đ
</option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- HIỂN THỊ LƯƠNG -->
           <div class="form-group">
    <label>Lương cơ bản</label>
    <input type="text" id="LuongCoBan" name="LuongCoBan" readonly style="background: #eee;">
</div>

<div class="form-group">
    <label>Hệ số lương</label>
    <input type="text" id="HeSoLuong" name="HeSoLuong" readonly style="background: #eee;">
</div>
            <!-- GHI CHÚ -->
            <div class="form-group">
                <label>Ghi chú</label>
                <textarea name="GhiChu" rows="3" maxlength="2000" placeholder="Ghi chú (tối đa 2000 ký tự)"></textarea>
            </div>

            <input type="hidden" name="TrangThai" value="con">

            <!-- BUTTON -->
            <div class="form-buttons">
                <button type="submit" class="btn add">💾 Lưu hợp đồng</button>
                <a href="index.php?controller=hopdong&action=index" class="btn cancel">↩ Hủy</a>
            </div>

        </form>
    </main>
  <?php include 'views/layout/footer.php'; ?>
