<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🏅 Quản lý Khen thưởng - Kỷ luật</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <nav class="sidebar">
        <h2>HỆ THỐNG <br> QUẢN LÝ NHÂN SỰ</h2>
        <ul>
            <ul>
                <li><a href="index.php?controller=home&action=index">🏠 Trang chủ</a></li>
                <li><a href="index.php?controller=nhanvien&action=index">👥 Quản lý nhân viên</a></li>
                <li><a href="index.php?controller=phongban&action=index">🏢 Quản lý phòng ban</a></li>
                <li><a href="index.php?controller=luong&action=index">💰 Quản lý lương</a></li>
                <li><a href="index.php?controller=chamcong&action=index">🕒 Quản lý chấm công</a></li>
                <li><a href="index.php?controller=hopdong&action=index">📄 Quản lý hợp đồng</a></li> 
                <li><a href="index.php?controller=nghiphep&action=index">📆 Quản lý nghỉ phép</a></li> 
                <li><a href="index.php?controller=khenthuong&action=index" class="active">🏅 Khen thưởng - Kỷ luật</a></li>
                <li><a href="index.php?controller=thongke&action=index">📊 Thống kê - Báo cáo</a></li>
                <li><a href="index.php?controller=chucvu&action=index">🙍‍♂️ Quản lý chức vụ</a></li>
                <li><a href="index.php?controller=hoso&action=index">👤 Hồ sơ cá nhân</a></li>
                <li><a href="">💼 Quản lý tuyển dụng</a></li>
                <li><a href="">📚 Quản lý đào tạo</a></li>
                <li><a href="">🗂 Quản lý đăng nhập – phân quyền</a></li>
                <li><a href="index.php?controller=timkiem&action=index">🔎 Tìm kiếm nâng cao</a></li>
                <li><a href="index.php?controller=dangxuat&action=index">🚪 Đăng xuất</a></li>
            </ul>
        </ul>
    </nav>
    <main class="main-content">
        <header><h1>🏅 Quản lý Khen thưởng - Kỷ luật</h1></header>

        <div class="actions">
            <a href="index.php?controller=khenthuong&action=them" class="btn add">➕ Thêm Quyết định</a>
            
            <form action="index.php" method="GET" style="display: flex; gap: 10px;">
                <input type="hidden" name="controller" value="khenthuong">
                <input type="hidden" name="action" value="index">
                <?php $keyword = $_GET['search'] ?? ''; ?>
                <input type="text" name="search" class="search-box" 
                        placeholder="🔍 Tìm Mã QD, Tên NV, Tiêu đề..." 
                        value="<?php echo htmlspecialchars($keyword); ?>">
                <button type="submit" class="btn search">Tìm</button>
            </form>
            </div>

        <table class="table">
            <thead>
                <tr>
                    <th>Mã QĐ</th>
                    <th>Mã NV</th>
                    <th>Họ tên NV</th>
                    <th>Loại QĐ</th>
                    <th>Ngày QĐ</th>
                    <th>Tiêu đề</th>
                    <th>Giá trị (VNĐ)</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (isset($result) && mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $loaiQD_class = ($row['LoaiQD'] == 'Khen thưởng') ? 'success' : 'danger';
                        
                        echo "<tr>
                                <td>{$row['MaQuyetDinh']}</td>
                                <td>{$row['MaNV']}</td>
                                <td>{$row['HoTen']}</td>
                                <td><span class='btn {$loaiQD_class}'>{$row['LoaiQD']}</span></td>
                                <td>" . date('d/m/Y', strtotime($row['NgayRaQD'])) . "</td>
                                <td>{$row['TieuDe']}</td>
                                <td>" . number_format($row['GiaTri'], 0, ',', '.') . "</td>
                                <td>
                                    <a href='index.php?controller=khenthuong&action=sua&maQD={$row['MaQuyetDinh']}' class='btn edit'>✏️ Sửa</a>
                                    <a href='index.php?controller=khenthuong&action=xoa&maQD={$row['MaQuyetDinh']}' 
                                       class='btn delete' onclick='return confirm(\"Xóa Quyết định {$row['MaQuyetDinh']}?\");'>🗑️ Xóa</a>
                                </td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='8'>Không có Quyết định nào được tìm thấy.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </main>
</div>
</body>
</html>