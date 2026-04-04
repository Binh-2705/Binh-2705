<?php include 'views/layout/header.php'; ?>
<?php include 'views/layout/sidebar.php'; ?>
    <!-- MAIN CONTENT -->
    <main class="main-content">

        <header>
            <h1>👥 Nhân viên tham gia đào tạo</h1>
        </header>

        <!-- FORM THÊM NHÂN VIÊN -->
        <div class="form-container">
            <form method="post"
                  action="index.php?controller=daotao&action=themNhanVien"
                  class="form-inline">

                <input type="hidden" name="MaKDT" value="<?= $maKDT ?>">

                <label>Chọn nhân viên:</label>

                <select name="MaNV" required>
                    <option value="">-- Chọn nhân viên --</option>
                    <?php while($nv = $dsNV->fetch_assoc()): ?>
                        <option value="<?= $nv['MaNV'] ?>">
                            <?= $nv['HoTen'] ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <button type="submit" class="btn add">➕ Thêm</button>

                <a href="index.php?controller=daotao&action=index"
                   class="btn delete">↩ Quay lại</a>
            </form>
        </div>

        <hr>

        <!-- DANH SÁCH THAM GIA -->
        <table class="table">
            <thead>
                <tr>
                    <th>Họ tên</th>
                    <th>Kết quả</th>
                    <th>Điểm</th>
                    <th>Cập nhật</th>
                </tr>
            </thead>
            <tbody>

            <?php if ($thamgia && mysqli_num_rows($thamgia) > 0): ?>
                <?php while($tg = mysqli_fetch_assoc($thamgia)): ?>
                <tr>
                    <form method="post"
                          action="index.php?controller=daotao&action=capNhatKetQua">

                        <td><?= $tg['HoTen'] ?></td>

                        <td>
                            <select name="KetQua">
                                <option value="Đạt"
                                    <?= $tg['KetQua']=='Đạt'?'selected':'' ?>>
                                    Đạt
                                </option>
                                <option value="Không đạt"
                                    <?= $tg['KetQua']=='Không đạt'?'selected':'' ?>>
                                    Không đạt
                                </option>
                                <option value="Chưa đánh giá"
                                    <?= $tg['KetQua']=='Chưa đánh giá'?'selected':'' ?>>
                                    Chưa đánh giá
                                </option>
                            </select>
                        </td>

                        <td>
                            <input type="number"
                                   step="0.1"
                                   min="0"
                                   max="10"
                                   name="DiemDanhGia"
                                   value="<?= $tg['DiemDanhGia'] ?>">
                        </td>

                        <td>
                            <input type="hidden"
                                   name="MaTGDT"
                                   value="<?= $tg['MaTGDT'] ?>">

                            <button type="submit"
                                    class="btn edit">💾</button>
                        </td>

                    </form>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4">Chưa có nhân viên tham gia</td>
                </tr>
            <?php endif; ?>

            </tbody>
        </table>

    </main>
<?php include 'views/layout/footer.php'; ?>