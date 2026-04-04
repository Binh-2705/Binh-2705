<?php include 'views/layout/header.php'; ?>
<?php include 'views/layout/sidebar.php'; ?>

<main class="main-content">

<header>
<h1>➕ Thêm ứng viên</h1>
</header>

<form method="post" enctype="multipart/form-data" class="form-box">

<div class="form-group">
<label>Họ tên</label>
<input type="text" name="hoten" required minlength="2" maxlength="120" placeholder="VD: Nguyễn Văn A">
</div>

<div class="form-group">
<label>Ngày sinh</label>
<input type="date" name="ngaysinh">
</div>

<div class="form-group">
<label>Giới tính</label>
<select name="gioitinh">
<option value="">-- Chọn --</option>
<option value="Nam">Nam</option>
<option value="Nữ">Nữ</option>
</select>
</div>

<div class="form-group">
<label>Email</label>
<input type="email" name="email" maxlength="150" placeholder="VD: email@example.com">
</div>

<div class="form-group">
<label>Điện thoại</label>
<input type="tel" name="dienthoai" pattern="[0-9]{9,11}" maxlength="11" placeholder="VD: 0901234567">
</div>

<div class="form-group">
<label>Trình độ</label>
<input type="text" name="trinhdo" maxlength="100" placeholder="VD: Đại học, Cao đẳng,...">
</div>

<div class="form-group">
<label>Kinh nghiệm</label>
<textarea name="kinhnghiem" maxlength="2000" placeholder="Kinh nghiệm làm việc (tối đa 2000 ký tự)"></textarea>
</div>

<div class="form-group">
<label>CV (PDF)</label>
<input type="file" name="cv" accept=".pdf" title="Chỉ chấp nhận file PDF">
</div>

<div class="form-actions">
<button class="btn add">Lưu</button>
<a href="index.php?controller=tuyendung&action=ungvien" class="btn cancel">Quay lại</a>
</div>

</form>

</main>

<?php include 'views/layout/footer.php'; ?>