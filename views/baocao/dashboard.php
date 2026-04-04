<?php include 'views/layout/header.php'; ?>
<?php include 'views/layout/sidebar.php'; ?>
<div class="main">

<h1>📊 Dashboard quản lý nhân sự</h1>

<div class="filter">

<form method="GET">

<input type="hidden" name="controller" value="baocao">
<input type="hidden" name="action" value="dashboard">

<select name="year">

<?php
$year = isset($_GET['year']) ? $_GET['year'] : date("Y");

for($i=2020;$i<=2030;$i++){
$selected = ($year==$i) ? "selected" : "";
echo "<option value='$i' $selected>$i</option>";
}
?>

</select>

<button class="btn">Lọc</button>

<a href="index.php?controller=baocao&action=exportExcel" class="btn">
📥 Xuất Excel
</a>

</form>

</div>

<div class="cards">

<div class="card">
<p>👥 Nhân viên</p>
<h2><?php echo $thongke['nhanvien']; ?></h2>
</div>

<div class="card">
<p>🏢 Phòng ban</p>
<h2><?php echo $thongke['phongban']; ?></h2>
</div>

<div class="card">
<p>📄 Hợp đồng</p>
<h2><?php echo $thongke['hopdong']; ?></h2>
</div>

<div class="card">
<p>💼 Tuyển dụng</p>
<h2><?php echo $thongke['tuyendung']; ?></h2>
</div>

</div>

<div class="chart-box">
<h3>📊 Nhân viên theo phòng ban</h3>
<canvas id="chartPhongBan"></canvas>
</div>

<div class="chart-box">
<h3>👨‍💼 Nhân viên theo giới tính</h3>
<canvas id="chartGioiTinh"></canvas>
</div>

<div class="chart-box">
<h3>🕒 Chấm công theo tháng</h3>
<canvas id="chartChamCong"></canvas>
</div>

<div class="chart-box">
<h3>📄 Hợp đồng theo loại</h3>
<canvas id="chartHopDong"></canvas>
</div>

<div class="chart-box">
<h3>📈 Tuyển dụng theo tháng</h3>
<canvas id="chartTuyenDung"></canvas>
</div>

<div class="chart-box">
<h3>💰 Tổng lương theo tháng</h3>
<canvas id="chartLuong"></canvas>
</div>

<h3>🏆 Top nhân viên nghỉ nhiều</h3>

<table>

<tr>
<th>Nhân viên</th>
<th>Số lần nghỉ</th>
</tr>

<?php while($row=mysqli_fetch_assoc($topnghi)){ ?>

<tr>
<td><?php echo $row['HoTen']; ?></td>
<td><?php echo $row['tong']; ?></td>
</tr>

<?php } ?>

</table>

</div>

</div>

<?php include 'views/layout/footer.php'; ?>