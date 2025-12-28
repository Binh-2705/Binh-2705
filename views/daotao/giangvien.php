<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý giảng viên</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <nav class="sidebar">
            <h2>HỆ THỐNG<br>QUẢN LÝ NHÂN SỰ</h2>
            <ul>
                <li><a href="index.php?controller=home&action=index">🏠 Trang chủ</a></li>
                <li><a href="index.php?controller=daotao&action=index">📚 Quản lý đào tạo</a></li>
                <li><a href="" class="active">👨‍🏫 Quản lý giảng viên</a></li>
                <li><a href="index.php?controller=daotao&action=baocao">📊 Báo cáo đào tạo</a></li>
            </ul>
        </nav>

        <main class="main-content">
            <header>
                <h1>👨‍🏫 Quản lý giảng viên</h1>
                
                <!-- Thống kê nhanh -->
                <?php if ($thongKeGV): ?>
                <div class="dashboard" style="margin-bottom: 20px;">
                    <div class="card">
                        <h3>Tổng giảng viên</h3>
                        <p style="font-size: 24px; font-weight: bold; color: #8b5cf6;">
                            <?= $thongKeGV['tongGiangVien'] ?? 0 ?>
                        </p>
                    </div>
                    <div class="card">
                        <h3>Kinh nghiệm TB</h3>
                        <p style="font-size: 24px; font-weight: bold; color: #10b981;">
                            <?= round($thongKeGV['kinhNghiemTB'] ?? 0, 1) ?> năm
                        </p>
                    </div>
                    <div class="card">
                        <h3>Chuyên môn</h3>
                        <p style="font-size: 24px; font-weight: bold; color: #ef4444;">
                            <?= $thongKeGV['soChuyenMon'] ?? 0 ?>
                        </p>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Nút chức năng -->
                <div style="display: flex; gap: 10px; margin-bottom: 15px;">
                    <a href="index.php?controller=daotao&action=themgiangvien" class="btn add">➕ Thêm giảng viên</a>
                    <a href="index.php?controller=daotao&action=index" class="btn back">↩️ Quay lại</a>
                </div>

                <!-- Tìm kiếm -->
                <form method="GET" action="index.php" style="margin-bottom: 15px; display: flex; gap: 10px;">
                    <input type="hidden" name="controller" value="daotao">
                    <input type="hidden" name="action" value="giangvien">
                    <input type="text" name="keyword" placeholder="🔎 Tìm kiếm giảng viên..." 
                           value="<?= htmlspecialchars($_GET['keyword'] ?? '') ?>" 
                           style="flex: 1; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                    <button type="submit" class="btn search">Tìm kiếm</button>
                    <?php if (!empty($_GET['keyword'])): ?>
                        <a href="index.php?controller=daotao&action=giangvien" class="btn" style="background: #6c757d;">Xóa tìm kiếm</a>
                    <?php endif; ?>
                </form>

                <!-- Top giảng viên -->
                <?php if ($topGiangVien && $topGiangVien->num_rows > 0): ?>
                <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #8b5cf6;">
                    <h3 style="color: #8b5cf6; margin-bottom: 10px;">🏆 Top giảng viên tích cực</h3>
                    <div style="display: flex; gap: 15px; overflow-x: auto;">
                        <?php while ($gv = $topGiangVien->fetch_assoc()): ?>
                        <div style="background: white; padding: 10px; border-radius: 6px; min-width: 200px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                            <div style="font-weight: bold; color: #495057;"><?= $gv['HoTen'] ?></div>
                            <div style="font-size: 12px; color: #6c757d;"><?= $gv['ChuyenMon'] ?></div>
                            <div style="display: flex; justify-content: space-between; margin-top: 8px;">
                                <span style="font-size: 12px;">📚 <?= $gv['soKhoaHoc'] ?> khóa</span>
                                <span style="font-size: 12px;">👥 <?= $gv['tongHocVien'] ?? 0 ?> HV</span>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
                <?php endif; ?>
            </header>

            <!-- Danh sách giảng viên -->
            <table class="table">
                <thead>
                    <tr>
                        <th>Mã GV</th>
                        <th>Họ tên</th>
                        <th>Chuyên môn</th>
                        <th>Kinh nghiệm</th>
                        <th>Email</th>
                        <th>SĐT</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($giangVienList && $giangVienList->num_rows > 0): ?>
                        <?php while ($gv = $giangVienList->fetch_assoc()): ?>
                            <tr>
                                <td><?= $gv['MaGV'] ?></td>
                                <td><?= $gv['HoTen'] ?></td>
                                <td><?= $gv['ChuyenMon'] ?></td>
                                <td>
                                    <?php if ($gv['KinhNghiem'] > 0): ?>
                                        <span style="padding: 3px 8px; background: #e3f2fd; border-radius: 12px; color: #1976d2;">
                                            <?= $gv['KinhNghiem'] ?> năm
                                        </span>
                                    <?php else: ?>
                                        <span style="color: #6c757d;">Mới</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= $gv['Email'] ?></td>
                                <td><?= $gv['SDT'] ?></td>
                                <td>
                                    <a href="index.php?controller=daotao&action=suagiangvien&magv=<?= $gv['MaGV'] ?>" 
                                       class="btn edit">✏️ Sửa</a>
                                    <a href="index.php?controller=daotao&action=xoagiangvien&magv=<?= $gv['MaGV'] ?>" 
                                       class="btn delete" onclick="return confirm('Xóa giảng viên này?');">🗑️ Xóa</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7">Không tìm thấy giảng viên nào.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </main>
    </div>
</body>
</html>