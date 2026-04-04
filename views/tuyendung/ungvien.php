<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Ứng viên</title>

<link rel="stylesheet" href="style.css">

<style>

.search-box{
margin:15px 0;
display:flex;
gap:10px;
}

.search-box input{
padding:8px;
width:250px;
border:1px solid #ccc;
border-radius:4px;
}

.search-box button{
padding:8px 15px;
background:#007bff;
color:white;
border:none;
border-radius:4px;
cursor:pointer;
}

.search-box button:hover{
background:#0056b3;
}

.table tr:hover{
background:#f5f5f5;
}

.high-score{
background:#e8ffe8;
}

</style>

</head>

<body>

<div class="container">

<nav class="sidebar">
<h2>HỆ THỐNG <br> QUẢN LÝ NHÂN SỰ</h2>

<ul>
<li><a href="index.php?controller=home">🏠 Trang chủ</a></li>
<li><a href="index.php?controller=nhanvien">👥 Nhân viên</a></li>
<li><a href="index.php?controller=phongban">🏢 Phòng ban</a></li>
<li><a href="index.php?controller=tuyendung" class="active">💼 Tuyển dụng</a></li>
<li><a href="index.php?controller=daotao">📚 Đào tạo</a></li>
<li><a href="index.php?controller=dangnhap&action=dangxuat">🚪 Đăng xuất</a></li>
</ul>
</nav>

<main class="main-content">

<header>
<h1>Danh sách ứng viên</h1>
</header>

<a href="index.php?controller=tuyendung&action=themungvien" class="btn add">
➕ Thêm ứng viên
</a>

<!-- tìm kiếm -->

<form method="GET" action="index.php" class="search-box">

<input type="hidden" name="controller" value="tuyendung">
<input type="hidden" name="action" value="timUngVien">

<input type="text" name="keyword" placeholder="🔍 Nhập tên, email hoặc số điện thoại">

<button type="submit">Tìm</button>

</form>

<table class="table">

<tr>
<th>Mã</th>
<th>Họ tên</th>
<th>Ngày sinh</th>
<th>Email</th>
<th>Điện thoại</th>
<th>Trình độ</th>
<th>CV</th>
<th>Điểm CV</th>
<th>Trạng thái</th>
<th>Ứng tuyển</th>
</tr>

<?php if($ungvien && $ungvien->num_rows > 0): ?>

<?php while($row = $ungvien->fetch_assoc()): ?>

<tr class="<?= ($row['DiemCV'] >= 8) ? 'high-score' : '' ?>">

<td><?= $row['MaUV'] ?></td>

<td><?= $row['HoTen'] ?></td>

<td><?= $row['NgaySinh'] ?></td>

<td><?= $row['Email'] ?></td>

<td><?= $row['DienThoai'] ?></td>

<td><?= $row['TrinhDo'] ?></td>

<td>

<?php if(!empty($row['FileCV'])): ?>

<a class="btn edit"
href="uploads/cv/<?= $row['FileCV'] ?>"
target="_blank">
📄 Xem CV
</a>

<?php else: ?>

Không có

<?php endif; ?>

</td>

<td>

<?php

$diem = $row['DiemCV'];

if($diem >= 8){
echo "<span style='color:green;font-weight:bold'>$diem</span>";
}
elseif($diem >= 5){
echo "<span style='color:orange'>$diem</span>";
}
else{
echo "<span style='color:red'>$diem</span>";
}

?>

</td>

<td>

<?php

if($diem >= 8){
echo "⭐ Rất tiềm năng";
}
elseif($diem >= 5){
echo "👍 Khá";
}
else{
echo "⚠ Cần xem lại";
}

?>

</td>

<td>

<a class="btn add"
href="index.php?controller=tuyendung&action=chonDot&id=<?= $row['MaUV'] ?>">
📤 Nộp hồ sơ
</a>

</td>

</tr>

<?php endwhile; ?>

<?php else: ?>

<tr>
<td colspan="10" style="text-align:center;padding:20px;">
Không tìm thấy ứng viên
</td>
</tr>

<?php endif; ?>

</table>

</main>

</div>

</body>
</html>