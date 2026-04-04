<?php include 'views/layout/header.php'; ?>
<?php include 'views/layout/sidebar.php'; ?>

    <!-- ===== MAIN CONTENT ===== -->
    <main class="main-content">

        <header>
            <h1>📂 Import Phòng ban từ CSV</h1>
        </header>

        <div class="form-nv">

            <form method="post"
                  enctype="multipart/form-data"
                  action="index.php?controller=phongban&action=docFile">

                <div class="form-group">
                    <label>Chọn file CSV</label>
                    <input type="file" name="filecsv" accept=".csv" required>
                </div>

                <div class="form-buttons">
                    <button type="submit" class="btn add">⬆️ Import</button>
                    <a href="index.php?controller=phongban&action=index"
                       class="btn cancel">↩️ Quay lại</a>
                </div>

            </form>

            <p style="margin-top:15px;color:#666;font-size:14px">
                📌 File CSV gồm các cột: <b>mapb, tenpb, mota</b>
            </p>

        </div>

    </main>
<?php include 'views/layout/footer.php'; ?>