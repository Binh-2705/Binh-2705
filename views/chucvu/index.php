<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>🙍‍♂️ Quản lý Chức vụ</title>
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
    <header><h1>🙍‍♂️ Quản lý Chức vụ</h1></header>

    <?php 
        $keyword = $keyword ?? '';
        $danhSachChucVu = $danhSachChucVu ?? [];
        if (isset($_GET['msg'])): ?>
            <p style="color: green; font-weight: bold; margin-bottom: 15px;"><?php echo htmlspecialchars($_GET['msg']); ?></p>
    <?php endif; ?>

    <div class="actions">
        <div class="btn-group">
            <a href="index.php?controller=chucvu&action=add" class="btn add">➕ Thêm Chức vụ mới</a>
            <a href="index.php?controller=chucvu&action=exportExcel" class="btn export">⬇️ Xuất Excel </a>
        </div>
        
        <form action="index.php" method="GET" style="display: flex; gap: 10px;">
            <input type="hidden" name="controller" value="chucvu">
            <input type="hidden" name="action" value="index">
            <input type="text" name="search" class="search-box" placeholder="🔍 Tìm theo Mã/Tên chức vụ..." 
                   value="<?php echo htmlspecialchars($keyword); ?>">
            <button type="submit" class="btn search">Tìm</button>
        </form>
    </div>
    
    <table class="table">
        <thead>
            <tr>
                <th>Mã CV</th>
                <th>Tên Chức vụ</th>
                <th>Số lượng NV</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($danhSachChucVu)): ?>
                <tr><td colspan="4">Không có dữ liệu chức vụ nào.</td></tr>
            <?php else: ?>
                <?php foreach ($danhSachChucVu as $cv): ?>
                    <tr>
                        <td><?php echo $cv['MaCV']; ?></td>
                        <td><?php echo $cv['TenChucVu']; ?></td> 
                        <td><?php echo $cv['SoLuongNV']; ?></td>
                        <td>
                            <div class="btn-group">
                                <a href="index.php?controller=chucvu&action=edit&id=<?php echo $cv['MaCV']; ?>" class="btn edit">✏️ Sửa</a>
                                <a href="index.php?controller=chucvu&action=delete&id=<?php echo $cv['MaCV']; ?>" 
                                   class="btn delete" 
                                   onclick="return confirm('Xác nhận xóa Chức vụ Mã: <?php echo $cv['MaCV']; ?>?');">🗑️ Xóa</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</main>
</div>
</body>
</html>