<?php include 'views/layout/header.php'; ?>
<?php include 'views/layout/sidebar.php'; ?>
<!-- MAIN CONTENT -->
<main class="main-content">

<header>
<h1>📄 Chọn đợt tuyển</h1>
</header>

<a href="index.php?controller=tuyendung&action=ungvien" class="btn cancel">
⬅ Quay lại
</a>

<br><br>

<table class="table">

<thead>
<tr>
<th>Tên đợt tuyển</th>
<th>Vị trí tuyển</th>
<th>Thao tác</th>
</tr>
</thead>

<tbody>

<?php if($dot && $dot->num_rows > 0): ?>

<?php while($row = $dot->fetch_assoc()): ?>

<tr>

<td><?= htmlspecialchars($row['TenDotTuyenDung']) ?></td>

<td><?= htmlspecialchars($row['ViTriTuyenDung']) ?></td>

<td>

<form method="post"
action="index.php?controller=tuyendung&action=themHoSo">

<input type="hidden" name="MaUV" value="<?= $maUV ?>">
<input type="hidden" name="MaDTD" value="<?= $row['MaDTD'] ?>">

<button class="btn add">📤 Nộp hồ sơ</button>

</form>

</td>

</tr>

<?php endwhile ?>

<?php else: ?>

<tr>
<td colspan="3">Không có đợt tuyển</td>
</tr>

<?php endif ?>

</tbody>

</table>

</main>
<?php include 'views/layout/footer.php'; ?>