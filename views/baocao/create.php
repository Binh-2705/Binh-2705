<?php include 'views/layout/header.php'; ?>
<?php include 'views/layout/sidebar.php'; ?>

        <main class="main-content">
            <header>
                <h1>➕ Thêm báo cáo mới</h1>
            </header>

            <div class="form-nv">
                <form method="POST" action="index.php?controller=baocao&action=store">
                    
                    <div class="form-group">
                        <label>Tên báo cáo</label>
                        <input type="text" name="TenBaoCao" placeholder="Nhập tên báo cáo..." required>
                    </div>

                    <div class="form-group">
                        <label>Loại báo cáo</label>
                        <select name="LoaiBaoCao">
                            <option value="Nhân sự">Nhân sự</option>
                            <option value="Chấm công">Chấm công</option>
                            <option value="Nghỉ phép">Nghỉ phép</option>
                            <option value="Hợp đồng">Hợp đồng</option>
                            <option value="Lương">Lương</option>
                        </select>
                    </div>

                    <div style="display: flex; gap: 20px;">
                        <div class="form-group" style="flex: 1;">
                            <label>Từ ngày</label>
                            <input type="date" name="TuNgay">
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label>Đến ngày</label>
                            <input type="date" name="DenNgay">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Người tạo</label>
                        <input type="text" name="NguoiTao" placeholder="Tên người lập báo cáo">
                    </div>

                    <div class="form-group">
                        <label>Ghi chú</label>
                        <textarea name="GhiChu" rows="4" style="width: 100%; border-radius: 6px; border: 1px solid #ccc; padding: 8px;"></textarea>
                    </div>

                    <div class="form-buttons">
                        <button type="submit" class="btn add">💾 Lưu báo cáo</button>
                        <a href="index.php?controller=baocao&action=index" class="btn cancel">
                            ⬅ Quay lại
                        </a>
                    </div>
                </form>
            </div>
        </main>
    <?php include 'views/layout/footer.php'; ?>