<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý học viên</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }
        .status-dangthamgia { background: #fff3cd; color: #856404; }
        .status-hoanthanh { background: #d4edda; color: #155724; }
        .status-vang { background: #f8d7da; color: #721c24; }
        
        .result-dat { background: #d4edda; color: #155724; }
        .result-khongdat { background: #f8d7da; color: #721c24; }
        
        .btn-small {
            padding: 4px 8px;
            font-size: 12px;
            margin: 2px;
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
                <li><a href="index.php?controller=daotao&action=baocao">📊 Báo cáo đào tạo</a></li>
                <li><a href="" class="active">👥 Quản lý học viên</a></li>
            </ul>
        </nav>

        <main class="main-content">
            <header>
                <h1>👥 Quản lý học viên</h1>
                <div style="margin: 10px 0; padding: 10px; background: #e8f4fc; border-radius: 6px; border-left: 4px solid #3b82f6;">
                    <strong>Khóa học:</strong> <?= $daotao['TenKhoaHoc'] ?? 'Chưa chọn khóa học' ?><br>
                    <strong>Mã khóa:</strong> <?= $daotao['MaDT'] ?? '' ?> | 
                    <strong>Giảng viên:</strong> <?= $daotao['GiangVien'] ?? '' ?>
                </div>
                
                <div style="display: flex; gap: 10px; margin-top: 10px;">
                    <a href="index.php?controller=daotao&action=index" class="btn back">↩️ Quay lại danh sách</a>
                    <?php if (isset($daotao['MaDT'])): ?>
                        <a href="index.php?controller=daotao&action=xuatdiem&madt=<?= $daotao['MaDT'] ?>" 
                           class="btn export">📥 Xuất Excel Điểm</a>
                    <?php endif; ?>
                </div>
            </header>

            <?php if (isset($daotao['MaDT'])): ?>
            <!-- Thêm học viên -->
            <div class="card" style="margin-bottom: 20px; padding: 15px; background: #f8f9fa;">
                <h3 style="margin-bottom: 15px; color: #2b3d51;">➕ Thêm học viên mới</h3>
                <form method="POST" action="index.php?controller=daotao&action=themhocvien">
                    <input type="hidden" name="MaDT" value="<?= $daotao['MaDT'] ?>">
                    <div style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 10px; align-items: flex-end;">
                        <div>
                            <label style="display: block; margin-bottom: 5px; font-weight: 500;">Chọn nhân viên:</label>
                            <select name="MaNV" class="search-box" required style="width: 100%;">
                                <option value="">-- Chọn nhân viên --</option>
                                <?php if ($dsNhanVien && $dsNhanVien->num_rows > 0): ?>
                                    <?php while ($nv = $dsNhanVien->fetch_assoc()): ?>
                                        <option value="<?= $nv['MaNV'] ?>">
                                            <?= $nv['MaNV'] ?> - <?= $nv['HoTen'] ?> (<?= $nv['PhongBan'] ?>)
                                        </option>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <option value="">Không có nhân viên nào để thêm</option>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 5px; font-weight: 500;">Ghi chú:</label>
                            <input type="text" name="GhiChu" class="search-box" 
                                   placeholder="Nhập ghi chú..." style="width: 100%;">
                        </div>
                        <div>
                            <button type="submit" class="btn add" style="height: 38px;">➕ Thêm học viên</button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Danh sách học viên -->
            <div style="margin-top: 20px;">
                <h3 style="margin-bottom: 15px; color: #2b3d51;">📋 Danh sách học viên</h3>
                
                <?php if ($hocVienList && $hocVienList->num_rows > 0): ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>STT</th>
                                <th>Mã NV</th>
                                <th>Họ tên</th>
                                <th>Phòng ban</th>
                                <th>Chức vụ</th>
                                <th>Điểm</th>
                                <th>Kết quả</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $stt = 1; while ($hv = $hocVienList->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $stt++ ?></td>
                                    <td><strong><?= $hv['MaNV'] ?></strong></td>
                                    <td><?= $hv['HoTen'] ?></td>
                                    <td><?= $hv['PhongBan'] ?></td>
                                    <td><?= $hv['ChucVu'] ?></td>
                                    <td>
                                        <?php if ($hv['Diem'] !== null): ?>
                                            <span style="font-weight: bold; font-size: 16px; 
                                                  color: <?= $hv['Diem'] >= 5 ? '#28a745' : '#dc3545' ?>;">
                                                <?= $hv['Diem'] ?>
                                            </span>
                                        <?php else: ?>
                                            <span style="color: #6c757d; font-style: italic;">Chưa chấm</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($hv['KetQua']): ?>
                                            <span class="status-badge <?= $hv['KetQua'] == 'Đạt' ? 'result-dat' : 'result-khongdat' ?>">
                                                <?= $hv['KetQua'] ?>
                                            </span>
                                        <?php else: ?>
                                            <span style="color: #6c757d;">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="status-badge <?= 
                                            $hv['TrangThai'] == 'Hoàn thành' ? 'status-hoanthanh' : 
                                            ($hv['TrangThai'] == 'Vắng' ? 'status-vang' : 'status-dangthamgia') 
                                        ?>">
                                            <?= $hv['TrangThai'] ?>
                                        </span>
                                        <div style="margin-top: 5px; display: flex; gap: 3px;">
                                            <a href="index.php?controller=daotao&action=diemdanh&id=<?= $hv['ID'] ?>&madt=<?= $daotao['MaDT'] ?>&trangthai=Đang tham gia" 
                                               class="btn-small" style="background: #fff3cd; color: #856404;" 
                                               title="Đang tham gia">✓</a>
                                            <a href="index.php?controller=daotao&action=diemdanh&id=<?= $hv['ID'] ?>&madt=<?= $daotao['MaDT'] ?>&trangthai=Vắng" 
                                               class="btn-small" style="background: #f8d7da; color: #721c24;" 
                                               title="Vắng">✗</a>
                                            <a href="index.php?controller=daotao&action=diemdanh&id=<?= $hv['ID'] ?>&madt=<?= $daotao['MaDT'] ?>&trangthai=Hoàn thành" 
                                               class="btn-small" style="background: #d4edda; color: #155724;" 
                                               title="Hoàn thành">✓✓</a>
                                        </div>
                                    </td>
                                    <td style="white-space: nowrap;">
                                        <!-- Form chấm điểm -->
                                        <form method="POST" action="index.php?controller=daotao&action=chamdiem" 
                                              style="display: flex; gap: 5px; margin-bottom: 5px; align-items: center;">
                                            <input type="hidden" name="ID" value="<?= $hv['ID'] ?>">
                                            <input type="hidden" name="MaDT" value="<?= $daotao['MaDT'] ?>">
                                            <input type="number" name="Diem" min="0" max="10" step="0.1" 
                                                   placeholder="Điểm" style="width: 70px; padding: 5px;" 
                                                   value="<?= $hv['Diem'] ?? '' ?>">
                                            <input type="text" name="GhiChu" placeholder="Ghi chú" 
                                                   style="width: 120px; padding: 5px;" 
                                                   value="<?= htmlspecialchars($hv['GhiChu'] ?? '') ?>">
                                            <button type="submit" class="btn edit btn-small" title="Lưu điểm">
                                                💾
                                            </button>
                                        </form>
                                        <a href="index.php?controller=daotao&action=xoahocvien&id=<?= $hv['ID'] ?>&madt=<?= $daotao['MaDT'] ?>" 
                                           class="btn delete btn-small" 
                                           onclick="return confirm('Xóa học viên này khỏi khóa học?');" 
                                           title="Xóa học viên">
                                            🗑️
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    
                    <!-- Thống kê nhanh -->
                    <?php 
                        // Reset pointer để tính thống kê
                        mysqli_data_seek($hocVienList, 0);
                        $total = 0;
                        $passed = 0;
                        $completed = 0;
                        $hasScore = 0;
                        $totalScore = 0;
                        
                        while ($hv = $hocVienList->fetch_assoc()) {
                            $total++;
                            if ($hv['KetQua'] == 'Đạt') $passed++;
                            if ($hv['TrangThai'] == 'Hoàn thành') $completed++;
                            if ($hv['Diem'] !== null) {
                                $hasScore++;
                                $totalScore += $hv['Diem'];
                            }
                        }
                        $avgScore = $hasScore > 0 ? $totalScore / $hasScore : 0;
                    ?>
                    
                    <div style="margin-top: 20px; display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px;">
                        <div style="background: white; padding: 15px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center;">
                            <div style="font-size: 12px; color: #6c757d;">Tổng học viên</div>
                            <div style="font-size: 24px; font-weight: bold; color: #3b82f6;"><?= $total ?></div>
                        </div>
                        <div style="background: white; padding: 15px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center;">
                            <div style="font-size: 12px; color: #6c757d;">Điểm trung bình</div>
                            <div style="font-size: 24px; font-weight: bold; color: <?= $avgScore >= 5 ? '#28a745' : '#dc3545' ?>;">
                                <?= number_format($avgScore, 1) ?>
                            </div>
                        </div>
                        <div style="background: white; padding: 15px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center;">
                            <div style="font-size: 12px; color: #6c757d;">Tỷ lệ đạt</div>
                            <div style="font-size: 24px; font-weight: bold; color: #28a745;">
                                <?= $total > 0 ? round(($passed / $total) * 100, 1) : 0 ?>%
                            </div>
                        </div>
                        <div style="background: white; padding: 15px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center;">
                            <div style="font-size: 12px; color: #6c757d;">Hoàn thành</div>
                            <div style="font-size: 24px; font-weight: bold; color: #17a2b8;">
                                <?= $completed ?>/<?= $total ?>
                            </div>
                        </div>
                    </div>
                    
                <?php else: ?>
                    <div style="text-align: center; padding: 40px; background: #f8f9fa; border-radius: 8px; margin-top: 20px;">
                        <div style="font-size: 48px; color: #adb5bd;">📝</div>
                        <h3 style="color: #6c757d; margin: 15px 0;">Chưa có học viên nào</h3>
                        <p style="color: #6c757d;">Hãy thêm học viên vào khóa học này</p>
                    </div>
                <?php endif; ?>
            </div>
            <?php else: ?>
                <div style="text-align: center; padding: 40px;">
                    <div style="font-size: 48px; color: #dc3545;">⚠️</div>
                    <h3 style="color: #dc3545; margin: 15px 0;">Không tìm thấy khóa học</h3>
                    <p>Vui lòng chọn khóa học từ danh sách đào tạo</p>
                    <a href="index.php?controller=daotao&action=index" class="btn add">📚 Quay lại danh sách khóa học</a>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>