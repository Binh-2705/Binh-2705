
<?php include 'views/layout/header.php'; ?>
<?php include 'views/layout/sidebar.php'; ?>
    <main class="main-content">
        <header>
            <h1>🔄 Gia hạn hợp đồng</h1>
        </header>

        <div style="margin-bottom: 20px; padding: 15px; background: #e2e8f0; border-radius: 8px; font-size: 0.9em;">
            <strong>Hợp đồng gốc:</strong> <?= htmlspecialchars($hopdong['SoHopDong']) ?> | 
            <strong>Nhân viên:</strong> <?= htmlspecialchars($hopdong['HoTen']) ?> |
            <strong>Bậc lương hiện tại:</strong> <?= htmlspecialchars($hopdong['TenBac']) ?>
        </div>

        <form action="index.php?controller=hopdong&action=luuGiaHan" method="POST" class="form-nv">
            
            <input type="hidden" name="HopDongGoc" value="<?= $hopdong['MaHopDong'] ?>">
            <input type="hidden" name="MaNV" value="<?= $hopdong['MaNV'] ?>">
            <input type="hidden" name="MaBac" value="<?= $hopdong['MaBac'] ?>">

            <div class="form-group">
                <label>Số hợp đồng mới *</label>
                <input type="text" name="SoHopDong" required placeholder="VD: HD-GIAHAN-01">
            </div>

            <div class="form-group">
                <label>Loại hợp đồng *</label>
                <select name="LoaiHopDong" required id="LoaiHopDong">
                    <option value="Xác định thời hạn" <?= $hopdong['LoaiHopDong'] == 'Xác định thời hạn' ? 'selected' : '' ?>>Xác định thời hạn</option>
                    <option value="Không xác định thời hạn" <?= $hopdong['LoaiHopDong'] == 'Không xác định thời hạn' ? 'selected' : '' ?>>Không xác định thời hạn</option>
                    <option value="Thử việc">Thử việc</option>
                </select>
            </div>

            <div class="form-group">
                <label>Ngày bắt đầu mới *</label>
                <input type="date" name="NgayBatDau" value="<?= date('Y-m-d') ?>" required>
            </div>

            <div class="form-group">
                <label>Ngày kết thúc mới</label>
                <input type="date" name="NgayKetThuc" id="NgayKetThuc">
                <small style="color: #64748b;">(Để trống nếu là không xác định thời hạn)</small>
            </div>

            <div class="form-group">
                <label>Ghi chú gia hạn</label>
                <textarea name="GhiChu" style="width: 100%; border-radius: 6px; border: 1px solid #ccc; padding: 8px;" rows="3">Gia hạn từ hợp đồng số <?= $hopdong['SoHopDong'] ?></textarea>
            </div>

            <input type="hidden" name="TrangThai" value="con">

            <div class="form-buttons">
                <button type="submit" class="btn edit">💾 Xác nhận gia hạn</button>
                <a href="index.php?controller=hopdong&action=index" class="btn cancel">↩ Hủy bỏ</a>
            </div>
        </form>
    </main>
    <?php include 'views/layout/footer.php'; ?>