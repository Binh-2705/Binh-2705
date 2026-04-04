<?php include 'views/layout/header.php'; ?>
<?php include 'views/layout/sidebar.php'; ?>
    <!-- MAIN CONTENT -->
    <main class="main-content">

        <header>
            <h1>➕ Thêm khóa đào tạo</h1>
        </header>

        <div class="form-container">
            <form method="post" class="form">

                <div class="form-group">
                    <label>Tên khóa đào tạo</label>
                    <input type="text" name="ten" required>
                </div>

                <div class="form-group">
                    <label>Từ ngày</label>
                    <input type="date" name="tu" required>
                </div>

                <div class="form-group">
                    <label>Đến ngày</label>
                    <input type="date" name="den" required>
                </div>

                <div class="form-group">
                    <label>Nội dung</label>
                    <textarea name="noidung" rows="4"></textarea>
                </div>

                <div class="form-group">
                    <label>Đơn vị tổ chức</label>
                    <input type="text" name="donvi">
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn add">💾 Lưu</button>
                    <a href="index.php?controller=daotao&action=index" class="btn delete">↩ Quay lại</a>
                </div>

            </form>
        </div>

    </main>
<?php include 'views/layout/footer.php'; ?>