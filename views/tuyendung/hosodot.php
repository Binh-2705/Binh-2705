<?php include 'views/layout/header.php'; ?>
<?php include 'views/layout/sidebar.php'; ?>

<main class="main-content">

<header>
<h1>📄 Hồ sơ ứng tuyển</h1>
</header>

<a href="index.php?controller=tuyendung" class="btn">⬅ Quay lại</a>

<br><br>

<table class="table">

<thead>
<tr>
<th>Họ tên</th>
<th>CV</th>
<th>Trạng thái</th>
<th>Lịch PV</th>
<th>Hành động</th>
</tr>
</thead>

<tbody>

<?php if(isset($hoso) && $hoso->num_rows > 0): ?>

<?php while($row = $hoso->fetch_assoc()): ?>

<tr>

<td>
<?= htmlspecialchars($row['HoTen']) ?>
</td>

<td>
<a class="btn view"
href="uploads/cv/<?= $row['FileCV'] ?>"
target="_blank">
📄 CV
</a>
</td>

<td>

<form method="post" action="index.php?controller=tuyendung&action=capNhatTrangThai">

<input type="hidden" name="MaHS" value="<?= $row['MaHS'] ?>">

<select name="TrangThai">

<option value="Nộp hồ sơ" <?= $row['TrangThai']=='Nộp hồ sơ'?'selected':'' ?>>Nộp hồ sơ</option>

<option value="Sàng lọc" <?= $row['TrangThai']=='Sàng lọc'?'selected':'' ?>>Sàng lọc</option>

<option value="Phỏng vấn" <?= $row['TrangThai']=='Phỏng vấn'?'selected':'' ?>>Phỏng vấn</option>

<option value="Offer" <?= $row['TrangThai']=='Offer'?'selected':'' ?>>Offer</option>

<option value="Nhận việc" <?= $row['TrangThai']=='Nhận việc'?'selected':'' ?>>Nhận việc</option>

<option value="Rớt" <?= $row['TrangThai']=='Rớt'?'selected':'' ?>>Rớt</option>

</select>

</td>

<td>

<button class="btn edit">💾 Lưu</button>

</form>

</td>

<td>

<a class="btn edit"
href="index.php?controller=tuyendung&action=lichphongvan&id=<?= $row['MaHS'] ?>">
📅 PV
</a>

</td>

</tr>

<?php endwhile ?>

<?php else: ?>

<tr>
<td colspan="5" style="text-align:center">
Chưa có hồ sơ ứng tuyển
</td>
</tr>

<?php endif ?>

</tbody>

</table>

</main>
<?php include 'views/layout/footer.php'; ?>