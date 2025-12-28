<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Báo cáo đào tạo</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-number {
            font-size: 36px;
            font-weight: bold;
            margin: 10px 0;
        }
        .stat-label {
            color: #6c757d;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <nav class="sidebar">
            <h2>HỆ THỐNG<br>QUẢN LÝ NHÂN SỰ</h2>
            <ul>
                <li><a href="index.php?controller=home&action=index">🏠 Trang chủ</a></li>
                <li><a href="index.php?controller=daotao&action=index">📚 Quản lý đào tạo</a></li>
                <li><a href="index.php?controller=daotao&action=giangvien">👨‍🏫 Quản lý giảng viên</a></li>
                <li><a href="" class="active">📊 Báo cáo đào tạo</a></li>
            </ul>
        </nav>

        <main class="main-content">
            <header>
                <h1>📊 Báo cáo đào tạo</h1>
                <div style="display: flex; gap: 10px; margin-top: 10px;">
                    <a href="index.php?controller=daotao&action=exportbaocao" class="btn export">📥 Xuất Excel Báo cáo</a>
                    <a href="index.php?controller=daotao&action=index" class="btn back">↩️ Quay lại</a>
                </div>
            </header>

            <!-- Thống kê tổng quan -->
            <div style="margin-bottom: 30px;">
                <h2 style="margin-bottom: 20px;">📈 Tổng quan đào tạo</h2>
                <div class="dashboard">
                    <div class="card">
                        <h3>Tổng số khóa học</h3>
                        <p style="font-size: 32px; font-weight: bold; color: #3b82f6;">
                            <?= number_format($thongKe['tongKhoaHoc'] ?? 0) ?>
                        </p>
                    </div>
                    <div class="card">
                        <h3>Tổng chi phí</h3>
                        <p style="font-size: 32px; font-weight: bold; color: #10b981;">
                            <?= number_format($thongKe['tongChiPhi'] ?? 0) ?> VNĐ
                        </p>
                    </div>
                    <div class="card">
                        <h3>Tổng giảng viên</h3>
                        <p style="font-size: 32px; font-weight: bold; color: #8b5cf6;">
                            <?= $thongKe['tongGiangVien'] ?? 0 ?>
                        </p>
                    </div>
                    <div class="card">
                        <h3>Tổng học viên</h3>
                        <p style="font-size: 32px; font-weight: bold; color: #ef4444;">
                            <?= $thongKe['tongHocVien'] ?? 0 ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Khóa học theo tháng -->
            <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.1); margin-bottom: 30px;">
                <h3 style="margin-bottom: 15px;">📅 Khóa học theo tháng (6 tháng gần nhất)</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Tháng</th>
                            <th>Số lượng khóa học</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (isset($thongKe['theoThang']) && $thongKe['theoThang']->num_rows > 0): ?>
                            <?php while ($row = $thongKe['theoThang']->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $row['Thang'] ?></td>
                                    <td><?= $row['soLuong'] ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="2">Không có dữ liệu</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Top khóa học -->
            <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.1); margin-bottom: 30px;">
                <h3 style="margin-bottom: 15px;">🏆 Top khóa học nhiều học viên</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>STT</th>
                            <th>Mã khóa</th>
                            <th>Tên khóa học</th>
                            <th>Giảng viên</th>
                            <th>Số học viên</th>
                            <th>Tỷ lệ hoàn thành</th>
                            <th>Chi phí</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($topKhoaHoc && $topKhoaHoc->num_rows > 0): ?>
                            <?php $stt = 1; while ($row = $topKhoaHoc->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $stt++ ?></td>
                                    <td><?= $row['MaDT'] ?></td>
                                    <td><?= $row['TenKhoaHoc'] ?></td>
                                    <td><?= $row['GiangVien'] ?></td>
                                    <td>
                                        <span style="font-weight: bold; color: #3b82f6;"><?= $row['soHocVien'] ?></span>
                                    </td>
                                    <td>
                                        <?php 
                                            $tyLe = $row['hoanThanh'] > 0 ? round(($row['hoanThanh'] / $row['soHocVien']) * 100, 1) : 0;
                                            $color = $tyLe >= 80 ? 'green' : ($tyLe >= 60 ? 'orange' : 'red');
                                        ?>
                                        <span style="color: <?= $color ?>; font-weight: bold;">
                                            <?= $tyLe ?>%
                                        </span>
                                    </td>
                                    <td><?= number_format($row['ChiPhi']) ?> VNĐ</td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="7">Không có dữ liệu</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Top giảng viên -->
            <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.1);">
                <h3 style="margin-bottom: 15px;">👨‍🏫 Top giảng viên tích cực</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>STT</th>
                            <th>Mã GV</th>
                            <th>Họ tên</th>
                            <th>Số khóa học</th>
                            <th>Chuyên môn</th>
                            <th>Kinh nghiệm</th>
                            <th>Tổng học viên</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($topGiangVien && $topGiangVien->num_rows > 0): ?>
                            <?php $stt = 1; while ($row = $topGiangVien->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $stt++ ?></td>
                                    <td><?= $row['MaGV'] ?></td>
                                    <td><?= $row['HoTen'] ?></td>
                                    <td>
                                        <span style="font-weight: bold; color: #8b5cf6;"><?= $row['soKhoaHoc'] ?></span>
                                    </td>
                                    <td><?= $row['ChuyenMon'] ?></td>
                                    <td><?= $row['KinhNghiem'] ?> năm</td>
                                    <td><?= $row['tongHocVien'] ?? 0 ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="7">Không có dữ liệu</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Thống kê học viên -->
            <?php if (isset($thongKeHocVien) && $thongKeHocVien->num_rows > 0): ?>
            <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.1); margin-top: 30px;">
                <h3 style="margin-bottom: 15px;">📊 Thống kê học viên</h3>
                <table class="table">
                    <thead>
                        <tr><th>Chỉ tiêu</th><th>Giá trị</th></tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $thongKeHocVien->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['ChiTieu'] ?></td>
                                <td>
                                    <span style="font-weight: bold; 
                                          color: <?= $row['ChiTieu'] == 'Học viên đạt' ? '#28a745' : 
                                                 ($row['ChiTieu'] == 'Học viên không đạt' ? '#dc3545' : '#495057') ?>;">
                                        <?= $row['GiaTri'] ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>