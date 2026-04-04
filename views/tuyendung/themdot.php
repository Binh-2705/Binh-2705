<?php include 'views/layout/header.php'; ?>
<?php include 'views/layout/sidebar.php'; ?>

<main class="main-content">

<header>
<h1>➕ Thêm đợt tuyển dụng</h1>
</header>

<a href="index.php?controller=tuyendung" class="btn cancel">⬅ Quay lại</a>

<br><br>

<form method="post" class="form-box">

<div class="form-group">
<label>Tên đợt tuyển</label>
<input type="text" name="ten" required>
</div>

<div class="form-group">
<label>Vị trí tuyển</label>
<input type="text" name="vitri" required>
</div>

<div class="form-group">
<label>Số lượng</label>
<input type="number" name="soluong" required>
</div>

<div class="form-group">
<label>Từ ngày</label>
<input type="date" name="tu" required>
</div>

<div class="form-group">
<label>Đến ngày</label>
<input type="date" name="den">
</div>

<div class="form-group">
<label>Mô tả</label>
<textarea name="mota"></textarea>
</div>

<div class="form-actions">
<button class="btn add">💾 Lưu</button>
</div>

</form>

</main>

<?php include 'views/layout/footer.php'; ?>