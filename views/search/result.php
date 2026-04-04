<h2>Kết quả tìm kiếm</h2>

<h3>👨‍💼 Nhân viên</h3>

<?php if($nhanvien instanceof mysqli_result && $nhanvien->num_rows > 0){ ?>
<?php while($row = $nhanvien->fetch_assoc()){ ?>
<p><?php echo htmlspecialchars($row['HoTen'], ENT_QUOTES, 'UTF-8'); ?></p>
<?php } ?>
<?php } else { ?>
<p>Không có kết quả.</p>
<?php } ?>



<h3>🏢 Phòng ban</h3>

<?php if($phongban instanceof mysqli_result && $phongban->num_rows > 0){ ?>
<?php while($row = $phongban->fetch_assoc()){ ?>
<p><?php echo htmlspecialchars($row['TenPB'], ENT_QUOTES, 'UTF-8'); ?></p>
<?php } ?>
<?php } else { ?>
<p>Không có kết quả.</p>
<?php } ?>



<h3>📄 Hợp đồng</h3>

<?php if($hopdong instanceof mysqli_result && $hopdong->num_rows > 0){ ?>
<?php while($row = $hopdong->fetch_assoc()){ ?>
<p><?php echo htmlspecialchars($row['MaHD'], ENT_QUOTES, 'UTF-8'); ?></p>
<?php } ?>
<?php } else { ?>
<p>Không có kết quả.</p>
<?php } ?>