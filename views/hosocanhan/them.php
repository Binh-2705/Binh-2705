
<?php include 'views/layout/header.php'; ?>
<?php include 'views/layout/sidebar.php'; ?>
    <!-- CONTENT -->
    <main class="main-content">
    <h1>➕ Thêm hồ sơ nhân viên</h1>

    <form method="POST" enctype="multipart/form-data" action="index.php?controller=hosocanhan&action=luu">
        <div class="form-grid"> <div class="form-group">
                <label>Ảnh đại diện:</label>
                <input type="file" name="Anh" accept="image/*">
            </div>

            <div class="form-group">
                <label>Nhân viên: <span style="color:red">*</span></label>
                <select name="MaNV" id="selectNhanVien" required>
                    <option value="">-- Chọn nhân viên --</option>
                    <?php while($nv = mysqli_fetch_assoc($nhanvien)): ?>
                        <option value="<?= $nv['MaNV'] ?>"><?= $nv['MaNV'] ?> - <?= $nv['HoTen'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Số CCCD/Passport:</label>
                <input type="text" name="CCCD" placeholder="Nhập số định danh" required>
            </div>

            <div class="form-group">
                <label>Ngày cấp:</label>
                <input type="date" name="NgayCap">
            </div>
            
            <div class="form-group">
                <label>Nơi cấp:</label>
                <input type="text" name="NoiCap" placeholder="Ví dụ: Cục CS QLHC về TTXH">
            </div>

            <div class="form-group">
                <label>Dân tộc:</label>
                <input type="text" name="DanToc" value="Kinh">
            </div>

            <div class="form-group">
                <label>Tôn giáo:</label>
                <input type="text" name="TonGiao" value="Không">
            </div>

            <div class="form-group full-width"> <label>Địa chỉ thường trú:</label>
                <textarea name="DiaChi" rows="2"></textarea>
            </div>

            <div class="form-group">
                <label>Trình độ học vấn:</label>
                <input type="text" name="TrinhDo" placeholder="Ví dụ: Đại học">
            </div>

            <div class="form-group">
                <label>Chuyên môn:</label>
                <input type="text" name="ChuyenMon" placeholder="Ví dụ: Công nghệ thông tin">
            </div>

            <div class="form-group">
                <label>Phòng ban:</label>
                <select name="MaPB" id="selectPhongBan" readonly style="background-color: #f0f0f0;">
                    <option value="">-- Tự động theo nhân viên --</option>
                </select>
            </div>

            <div class="form-group">
                <label>Chức vụ:</label>
                <select name="MaCV" id="selectChucVu" readonly style="background-color: #f0f0f0;">
                    <option value="">-- Tự động theo nhân viên --</option>
                </select>
            </div>

            <div class="form-group">
                <label>Ngày vào làm:</label>
                <input type="date" name="NgayVaoLam" value="<?= date('Y-m-d') ?>">
            </div>

            <div class="form-group">
                <label>Trạng thái hôn nhân:</label>
                <select name="TrangThaiHonNhan">
                    <option value="Độc thân">Độc thân</option>
                    <option value="Đã kết hôn">Đã kết hôn</option>
                    <option value="Khác">Khác</option>
                </select>
            </div>
        </div>

        <div class="form-actions" style="margin-top: 30px;">
            <button type="submit" class="btn add">💾 Lưu hồ sơ</button>
            <a href="index.php?controller=hosocanhan&action=index" class="btn">⬅ Quay lại</a>
        </div>
    </form>
</main>
  <?php include 'views/layout/footer.php'; ?>

