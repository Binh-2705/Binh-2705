<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Import Phòng ban</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">

    <!-- ===== SIDEBAR ===== -->
    <nav class="sidebar">
         <h2>HỆ THỐNG <br> QUẢN LÝ NHÂN SỰ</h2>
        <ul>
            <ul>
                <li><a href="index.php?controller=home&action=index" >🏠 Trang chủ</a></li>
                <li><a href="index.php?controller=nhanvien&action=index">👥 Quản lý nhân viên</a></li>
                <li><a href="index.php?controller=phongban&action=index" class="active">🏢 Quản lý phòng ban</a></li>
                <li><a href="index.php?controller=luong&action=index">💰 Quản lý lương</a></li>
                <li><a href="index.php?controller=chamcong&action=index">🕒 Quản lý chấm công</a></li>
                <li><a href="index.php?controller=hopdong&action=index">📄 Quản lý hợp đồng</a></li>
                <li><a href="index.php?controller=nghiphep&action=index">📆 Quản lý nghỉ phép</a></li>
                <li><a href="index.php?controller=khenthuong&action=index">🏅 Khen thưởng - Kỷ luật</a></li>
                <li><a href="index.php?controller=thongke&action=index">📊 Thống kê - Báo cáo</a></li>
                <li><a href="index.php?controller=chucvu&action=index">🙍‍♂️ Quản lý chức vụ</a></li>
                <li><a href="index.php?controller=hoso&action=index">👤 Hồ sơ cá nhân</a></li>
                <li><a href="index.php?controller=tuyendung&action=index">💼 Quản lý tuyển dụng</a></li>
                <li><a href="index.php?controller=daotao&action=index">📚 Quản lý đào tạo</a></li>
                <li><a href="index.php?controller=taikhoan&action=index">🗂 Quản lý tài khoản</a></li>
               
                <li><a href="index.php?controller=dangnhap&action=dangxuat">🚪 Đăng xuất</a></li>
            </ul>
        </ul>
    </nav>

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
</div>

</body>
</html>
