<?php include 'views/layout/header.php'; ?>
<?php include 'views/layout/sidebar.php'; ?>
        <main class="main-content">
            <header>
                <h1>✏ Chỉnh sửa thông tin báo cáo</h1>
            </header>

            <div class="form-nv">
                <form method="POST" action="index.php?controller=baocao&action=update">
                    <input type="hidden" name="MaBC" value="<?php echo $baocao['MaBC'] ?>">

                    <div class="form-group">
                        <label>Tên báo cáo</label>
                        <input type="text" name="TenBaoCao" value="<?php echo $baocao['TenBaoCao'] ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Loại báo cáo</label>
                        <select name="LoaiBaoCao">
                            <option value="Nhân sự" <?php if($baocao['LoaiBaoCao']=="Nhân sự") echo "selected" ?>>Nhân sự</option>
                            <option value="Chấm công" <?php if($baocao['LoaiBaoCao']=="Chấm công") echo "selected" ?>>Chấm công</option>
                            <option value="Nghỉ phép" <?php if($baocao['LoaiBaoCao']=="Nghỉ phép") echo "selected" ?>>Nghỉ phép</option>
                            <option value="Hợp đồng" <?php if($baocao['LoaiBaoCao']=="Hợp đồng") echo "selected" ?>>Hợp đồng</option>
                            <option value="Lương" <?php if($baocao['LoaiBaoCao']=="Lương") echo "selected" ?>>Lương</option>
                        </select>
                    </div>

                    <div style="display: flex; gap: 20px;">
                        <div class="form-group" style="flex: 1;">
                            <label>Từ ngày</label>
                            <input type="date" name="TuNgay" value="<?php echo $baocao['TuNgay'] ?>">
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label>Đến ngày</label>
                            <input type="date" name="DenNgay" value="<?php echo $baocao['DenNgay'] ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Người tạo</label>
                        <input type="text" name="NguoiTao" value="<?php echo $baocao['NguoiTao'] ?>">
                    </div>

                    <div class="form-group">
                        <label>Ghi chú</label>
                        <textarea name="GhiChu" rows="4" style="width: 100%; border-radius: 6px; border: 1px solid #ccc; padding: 8px;"><?php echo $baocao['GhiChu'] ?></textarea>
                    </div>

                    <div class="form-buttons">
                        <button type="submit" class="btn edit">💾 Cập nhật thay đổi</button>
                        <a href="index.php?controller=baocao&action=index" class="btn cancel">
                            ⬅ Quay lại
                        </a>
                    </div>
                </form>
            </div>
        </main>
    <?php include 'views/layout/footer.php'; ?>