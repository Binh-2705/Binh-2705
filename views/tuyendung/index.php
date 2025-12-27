<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>💼 Quản lý Tuyển dụng</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <nav class="sidebar">
        <h2>HỆ THỐNG <br> QUẢN LÝ NHÂN SỰ</h2>
        <ul>
            <ul>
                <li><a href="index.php?controller=home&action=index" class="active">🏠 Trang chủ</a></li>
                <li><a href="index.php?controller=nhanvien&action=index">👥 Quản lý nhân viên</a></li>
                <li><a href="index.php?controller=phongban&action=index">🏢 Quản lý phòng ban</a></li>
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
                <li><a href="index.php?controller=phanquyen&action=index">🗂 Quản lý đăng nhập – phân quyền</a></li>
                <li><a href="index.php?controller=timkiem&action=index">🔎 Tìm kiếm nâng cao</a></li>
                <li><a href="index.php?controller=dangxuat&action=index">🚪 Đăng xuất</a></li>
            </ul>
        </ul>
    </nav>

    <main class="main-content">
        <header><h1>💼 Danh sách Ứng viên</h1></header>

        <?php if (isset($_GET['msg'])): ?>
            <p style="color: green; font-weight: bold; margin-bottom: 15px; background: #e8f5e9; padding: 10px; border-radius: 5px;">
                <?php echo htmlspecialchars($_GET['msg']); ?>
            </p>
        <?php endif; ?>

        <div class="actions">
            <form action="" method="GET">
                <input type="hidden" name="controller" value="tuyendung">
                <input type="hidden" name="action" value="index">
                <input type="text" name="search" class="search-box" placeholder="Tìm theo tên, vị trí, SĐT..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                <button type="submit" class="btn search">🔍 Tìm</button>
                <a href="index.php?controller=tuyendung&action=xuatexcel" class="btn add" style="background-color: #27ae60;"> Xuất Excel</a>
            </form>

            <a href="index.php?controller=tuyendung&action=add" class="btn add">➕ Thêm ứng viên</a>
        </div>

    <table class="table">
        <thead>
            <tr>
                <th>STT</th> <th>Họ và Tên</th>
                <th>Vị trí ứng tuyển</th>
                <th>SĐT</th>
                <th>Ngày nộp</th>
                <th>Ghi chú</th>
                <th>Trạng thái</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($danhSachUngVien)): ?>
                <?php 
                    $stt = 1; 
                    foreach ($danhSachUngVien as $uv): ?>
                <tr>
                    <td><?php echo $stt++; ?></td>
                    <td><?php echo htmlspecialchars($uv['HoTen']); ?></td>
                    <td><?php echo htmlspecialchars($uv['ViTriUngTuyen']); ?></td>
                    <td><?php echo htmlspecialchars($uv['SoDienThoai']); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($uv['NgayNop'])); ?></td>
                    <td><?php echo htmlspecialchars($uv['GhiChu'] ?? ''); ?></td>
                    <td>
                        <?php 
                                $statusClass = 'badge-pending';
                                if($uv['TrangThai'] == 'Đã duyệt') $statusClass = 'badge-approved';
                                if($uv['TrangThai'] == 'Từ chối') $statusClass = 'badge-rejected';
                        ?>
                        <span class="badge <?php echo $statusClass; ?>"><?php echo $uv['TrangThai']; ?></span>
                    </td>
                    
                    <td>
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <?php if ($uv['TrangThai'] == 'Chờ xét duyệt'): ?>
                                <a href="index.php?controller=tuyendung&action=approve&id=<?php echo $uv['MaUV']; ?>&status=approved" 
                                style="color: #27ae60; text-decoration: none; font-weight: bold;">✔️ Duyệt</a>
                                
                                <a href="index.php?controller=tuyendung&action=approve&id=<?php echo $uv['MaUV']; ?>&status=rejected" 
                                style="color: #c0392b; text-decoration: none; font-weight: bold;">❌ Từ chối</a>
                            <?php endif; ?>

                            <a href="index.php?controller=tuyendung&action=delete&id=<?php echo $uv['MaUV']; ?>" 
                            class="btn delete" 
                            onclick="return confirm('Bạn có chắc chắn muốn xóa ứng viên này?');">
                            🗑️ Xóa
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="7" style="text-align: center;">Không có dữ liệu.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    </main>
</div>
</body>
</html>