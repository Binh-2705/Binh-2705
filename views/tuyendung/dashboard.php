<!DOCTYPE html>
<html lang="vi">
<head>

<meta charset="UTF-8">
<title>Dashboard tuyển dụng</title>

<link rel="stylesheet" href="style.css">

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>

.cards{
display:flex;
flex-wrap:wrap;
gap:20px;
margin-bottom:30px;
}

.card{
flex:1;
min-width:180px;
background:white;
padding:20px;
border-radius:10px;
box-shadow:0 2px 10px rgba(0,0,0,0.1);
text-align:center;
}

.card h3{
margin-bottom:10px;
font-size:16px;
}

.card p{
font-size:28px;
font-weight:bold;
color:#2c3e50;
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
<h1>📊 Dashboard tuyển dụng</h1>
</header>

<div class="cards">

<div class="card">
<h3>Tổng hồ sơ</h3>
<p><?= $thongke['Tong'] ?></p>
</div>

<div class="card">
<h3>Nộp hồ sơ</h3>
<p><?= $thongke['NopHoSo'] ?></p>
</div>

<div class="card">
<h3>Sàng lọc</h3>
<p><?= $thongke['SangLoc'] ?></p>
</div>

<div class="card">
<h3>Phỏng vấn</h3>
<p><?= $thongke['PhongVan'] ?></p>
</div>

<div class="card">
<h3>Offer</h3>
<p><?= $thongke['Offer'] ?></p>
</div>

<div class="card">
<h3>Nhận việc</h3>
<p><?= $thongke['NhanViec'] ?></p>
</div>

<div class="card">
<h3>Rớt</h3>
<p><?= $thongke['Rot'] ?></p>
</div>

</div>

<canvas id="chart" height="100"></canvas>

<script>

const ctx = document.getElementById('chart');

new Chart(ctx, {

type: 'bar',

data: {
labels: ['Nộp hồ sơ','Phỏng vấn','Offer','Nhận việc','Rớt'],

datasets: [{
label: 'Thống kê tuyển dụng',

data: [
<?= $thongke['NopHoSo'] ?>,
<?= $thongke['PhongVan'] ?>,
<?= $thongke['Offer'] ?>,
<?= $thongke['NhanViec'] ?>,
<?= $thongke['Rot'] ?>
],

backgroundColor:[
'#3498db',
'#f1c40f',
'#9b59b6',
'#2ecc71',
'#e74c3c'
]

}]
},

options:{
responsive:true,
plugins:{
legend:{
display:false
}
}
}

});

</script>

</main>
</div>

</body>
</html>