<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📄 Quản lý Hợp đồng</title>
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
                <li><a href="index.php?controller=hopdong&action=index" class="active">📄 Quản lý hợp đồng</a></li>
                <li><a href="index.php?controller=nghiphep&action=index">📆 Quản lý nghỉ phép</a></li>
                <li><a href="index.php?controller=khenthuong&action=index">🏅 Khen thưởng - Kỷ luật</a></li>
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
        <header>
            <h1>📄 Quản lý Hợp đồng</h1>
        </header>

        <div class="actions">
            <a href="index.php?controller=hopdong&action=them" class="btn add">➕ Thêm Hợp đồng</a>
            
            <form action="index.php" method="GET" style="display: flex; gap: 10px;">
                <input type="hidden" name="controller" value="hopdong">
                <input type="hidden" name="action" value="index">
                <?php $keyword = $_GET['search'] ?? ''; // Lấy keyword để hiển thị lại ?>
                <input type="text" name="search" class="search-box" 
                        placeholder="🔍 Tìm Mã HĐ, Tên NV, Loại HĐ..." 
                        value="<?php echo htmlspecialchars($keyword); ?>">
                <button type="submit" class="btn search">Tìm</button>
            </form>
            </div>

        <table class="table">
            <thead>
                <tr>
                    <th>Mã HĐ</th>
                    <th>Mã NV</th>
                    <th>Họ tên NV</th>
                    <th>Loại HĐ</th>
                    <th>Ngày bắt đầu</th> 
                    <th>Ngày kết thúc</th> 
                    <th>Lương CB</th>
                    <th>Trạng thái</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (isset($result) && mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $status_class = '';
                        if ($row['TrangThai'] == 'Còn hiệu lực') {
                            $status_class = 'success';
                        } elseif ($row['TrangThai'] == 'Đã hết hạn') {
                            $status_class = 'warning';
                        } else {
                            $status_class = 'danger';
                        }
                        
                        // Định dạng tiền tệ VND (tùy chọn)
                        $luong_cb_formatted = number_format($row['LuongCoBan'], 0, ',', '.') . ' VNĐ';

                        // Xử lý Ngày kết thúc là NULL
                        $ngay_ket_thuc_display = $row['NgayKetThuc'] 
                                                 ? date('d/m/Y', strtotime($row['NgayKetThuc'])) 
                                                 : 'Vô thời hạn';
                        
                        echo "<tr>
                                <td>{$row['MaHD']}</td>
                                <td>{$row['MaNV']}</td>
                                <td>{$row['HoTen']}</td>
                                <td>{$row['LoaiHopDong']}</td> 
                                <td>" . date('d/m/Y', strtotime($row['NgayBatDau'])) . "</td> 
                                <td>" . $ngay_ket_thuc_display . "</td> 
                                <td>" . $luong_cb_formatted . "</td>
                                <td><span class='btn {$status_class}'>{$row['TrangThai']}</span></td>
                                <td>
                                    <a href='index.php?controller=hopdong&action=sua&maHD={$row['MaHD']}' class='btn edit'>✏️ Sửa</a>
                                    <a href='index.php?controller=hopdong&action=xoa&maHD={$row['MaHD']}' 
                                        class='btn delete' onclick='return confirm(\"Xóa hợp đồng {$row['MaHD']}?\");'>🗑️ Xóa</a>
                                </td>
                              </tr>";
                    }
                } else {
                    $search_msg = $keyword ? "Không tìm thấy hợp đồng nào cho từ khóa '{$keyword}'." : "Chưa có hợp đồng nào được ký kết.";
                    echo "<tr><td colspan='9'>{$search_msg}</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </main>
</div>
</body>
</html>