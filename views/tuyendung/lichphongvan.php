<?php include 'views/layout/header.php'; ?>
<?php include 'views/layout/sidebar.php'; ?>

<!-- MAIN CONTENT -->
<main class="main-content">

<header>
<h1>📅 Lịch phỏng vấn</h1>
</header>

<a href="javascript:history.back()" class="btn cancel">
⬅ Quay lại
</a>

<br><br>

<!-- FORM THÊM LỊCH PHỎNG VẤN -->
<div class="form-box">

<h2>➕ Thêm lịch phỏng vấn</h2>

<form method="post" 
action="index.php?controller=tuyendung&action=themLichPhongVan"
class="form">

<input type="hidden" name="MaHS" value="<?= $_GET['id'] ?>">

<label>Ngày phỏng vấn</label>
<input type="date" name="ngay" required>

<label>Giờ</label>
<input type="time" name="gio" required>

<label>Địa điểm</label>
<input type="text" name="diadiem">

<label>Ghi chú</label>
<textarea name="ghichu"></textarea>

<br>

<div class="form-actions">
<button class="btn add">➕ Thêm lịch</button>
</div>

</form>

</div>

<br><br>

<!-- FORM ĐÁNH GIÁ PHỎNG VẤN -->
<div class="form-box">

<h2>⭐ Đánh giá phỏng vấn</h2>

<form method="post" action="index.php?controller=tuyendung&action=themDanhGia" class="form">

<input type="hidden" name="MaHS" value="<?= $_GET['id'] ?>">

<label>Kỹ năng (1-10)</label>
<input type="number" name="kynang" min="1" max="10" required>

<label>Kinh nghiệm (1-10)</label>
<input type="number" name="kinhnghiem" min="1" max="10" required>

<label>Thái độ (1-10)</label>
<input type="number" name="thaido" min="1" max="10" required>

<label>Nhận xét</label>
<textarea name="nhanxet"></textarea>

<br>

<button class="btn add">⭐ Lưu đánh giá</button>

</form>

</div>

<br><br>

<!-- BẢNG KẾT QUẢ ĐÁNH GIÁ -->
<h2>📊 Kết quả đánh giá</h2>

<table class="table">

<tr>
<th>Kỹ năng</th>
<th>Kinh nghiệm</th>
<th>Thái độ</th>
<th>Điểm TB</th>
<th>Nhận xét</th>
</tr>

<?php if(isset($danhgia) && $danhgia->num_rows > 0): ?>

<?php while($dg = $danhgia->fetch_assoc()): ?>

<?php 
$tb = ($dg['DiemKyNang'] + $dg['DiemKinhNghiem'] + $dg['DiemThaiDo']) / 3;
?>

<tr>

<td><?= $dg['DiemKyNang'] ?></td>
<td><?= $dg['DiemKinhNghiem'] ?></td>
<td><?= $dg['DiemThaiDo'] ?></td>

<td>

<?php

if($tb >= 8)
echo "<span class='badge success'>".number_format($tb,1)."</span>";

elseif($tb >=6)
echo "<span class='badge warning'>".number_format($tb,1)."</span>";

else
echo "<span class='badge danger'>".number_format($tb,1)."</span>";

?>

</td>

<td><?= htmlspecialchars($dg['NhanXet']) ?></td>

</tr>

<?php endwhile ?>

<?php else: ?>

<tr>
<td colspan="5" style="text-align:center">
Chưa có đánh giá
</td>
</tr>

<?php endif ?>

</table>

<br><br>

<!-- BẢNG LỊCH PHỎNG VẤN -->
<h2>📅 Danh sách lịch phỏng vấn</h2>

<table class="table">

<thead>
<tr>
<th>Ngày</th>
<th>Giờ</th>
<th>Địa điểm</th>
<th>Ghi chú</th>
</tr>
</thead>

<tbody>

<?php if(isset($lich) && $lich->num_rows > 0): ?>

<?php while($row = $lich->fetch_assoc()): ?>

<tr>

<td><?= $row['NgayPhongVan'] ?></td>
<td><?= $row['GioPhongVan'] ?></td>
<td><?= htmlspecialchars($row['DiaDiem']) ?></td>
<td><?= htmlspecialchars($row['GhiChu']) ?></td>

</tr>

<?php endwhile ?>

<?php else: ?>

<tr>
<td colspan="4" style="text-align:center">
Chưa có lịch phỏng vấn
</td>
</tr>

<?php endif ?>

</tbody>

</table>

</main>
<?php include 'views/layout/footer.php'; ?>