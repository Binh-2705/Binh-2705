<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chi tiết hồ sơ nhân viên</title>
    <link rel="stylesheet" href="style.css"> 
    <style>
        .profile-header-box {
            display: flex;
            align-items: center;
            gap: 20px;
            background: linear-gradient(135deg, #1e3a8a, #3b82f6);
            color: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .avatar-placeholder {
            width: 70px;
            height: 70px;
            background: white;
            color: #1e3a8a;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            font-weight: bold;
            border-radius: 50%;
            text-transform: uppercase;
        }
        .profile-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 15px;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .info-item {
            padding: 10px;
            border-bottom: 1px solid #f1f5f9;
        }
        .info-item label {
            display: block;
            font-size: 12px;
            color: #64748b;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 4px;
        }
        .info-item span {
            font-size: 16px;
            color: #1e293b;
        }
    </style>
</head>
<body>
    <div class="container">
        <nav class="sidebar">
            <h2>HỆ THỐNG <br> QUẢN LÝ NHÂN SỰ</h2>
        <ul>
            <ul>
                <li><a href="index.php?controller=home&action=index" >🏠 Trang chủ</a></li>
                <li><a href="index.php?controller=nhanvien&action=index">👥 Quản lý nhân viên</a></li>
                <li><a href="index.php?controller=phongban&action=index">🏢 Quản lý phòng ban</a></li>
                <li><a href="index.php?controller=luong&action=index">💰 Quản lý lương</a></li>
                <li><a href="index.php?controller=chamcong&action=index">🕒 Quản lý chấm công</a></li>
                <li><a href="index.php?controller=hopdong&action=index">📄 Quản lý hợp đồng</a></li>
                <li><a href="index.php?controller=nghiphep&action=index">📆 Quản lý nghỉ phép</a></li>
                <li><a href="index.php?controller=khenthuong&action=index">🏅 Khen thưởng - Kỷ luật</a></li>
                <li><a href="index.php?controller=thongke&action=index">📊 Thống kê - Báo cáo</a></li>
                <li><a href="index.php?controller=chucvu&action=index">🙍‍♂️ Quản lý chức vụ</a></li>
                <li><a href="index.php?controller=hoso&action=index" class="active">👤 Hồ sơ cá nhân</a></li>
                <li><a href="index.php?controller=tuyendung&action=index">💼 Quản lý tuyển dụng</a></li>
                <li><a href="index.php?controller=daotao&action=index">📚 Quản lý đào tạo</a></li>
                <li><a href="index.php?controller=taikhoan&action=index">🗂 Quản lý tài khoản</a></li>
                <li><a href="index.php?controller=timkiem&action=index">🔎 Tìm kiếm nâng cao</a></li>
                <li><a href="index.php?controller=dangxuat&action=index">🚪 Đăng xuất</a></li>
            </ul>
        </ul>
        </nav>

        <main class="main-content">
            <header>
                <h1>🪪 Chi tiết hồ sơ nhân viên</h1>
            </header>

            <div class="profile-header-box">
                <div class="avatar-placeholder"><?= mb_substr($nv['HoTen'], 0, 1) ?></div>
                <div>
                    <h2 style="margin:0"><?= $nv['HoTen'] ?></h2>
                    <p style="margin:5px 0 0 0; opacity: 0.9;">Mã NV: <?= $nv['MaNV'] ?> | <?= $nv['TenChucVu'] ?? 'Nhân viên' ?></p>
                </div>
            </div>

            <div class="profile-info">
                <div class="info-item"><label>Họ và tên</label><span><?= $nv['HoTen'] ?></span></div>
                <div class="info-item"><label>Giới tính</label><span><?= $nv['GioiTinh'] ?></span></div>
                <div class="info-item"><label>Ngày sinh</label><span><?= date('d/m/Y', strtotime($nv['NgaySinh'])) ?></span></div>
                <div class="info-item"><label>Phòng ban</label><span><?= $nv['TenPB'] ?? 'Chưa phân phối' ?></span></div>
                <div class="info-item"><label>Chức vụ</label><span><?= $nv['TenChucVu'] ?? 'Chưa cập nhật' ?></span></div>
                <div class="info-item"><label>Mức lương</label><span><?= number_format($nv['Luong']) ?> VNĐ</span></div>
            </div>

            <div class="form-buttons" style="justify-content: flex-start; margin-top: 20px; display: flex; gap: 10px;">
                <a href="index.php?controller=hoso&action=index" class="btn add" style="background: #6c757d;">↩️ Quay lại</a>
                <a href="index.php?controller=nhanvien&action=sua&manv=<?= $nv['MaNV'] ?>" class="btn add">✏️ Sửa hồ sơ</a>
            </div>
        </main>
    </div>
</body>
</html>