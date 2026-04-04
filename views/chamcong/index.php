<?php include 'views/layout/header.php'; ?>
<?php include 'views/layout/sidebar.php'; ?>
<main class="main-content">

<header>
<h1>📅 Bảng chấm công theo tháng</h1>
</header>

<form method="GET" class="filter-form">
<input type="hidden" name="controller" value="chamcong">

<div style="display: flex; gap: 8px; align-items: center;">
  <label style="font-weight: 600;">Tháng:</label>
  <input type="number" name="thang" value="<?= $thang ?>" min="1" max="12" style="width: 80px;">
</div>

<div style="display: flex; gap: 8px; align-items: center;">
  <label style="font-weight: 600;">Năm:</label>
  <input type="number" name="nam" value="<?= $nam ?>" style="width: 100px;">
</div>

<button type="submit" class="btn search">🔍 Xem</button>
<a href="index.php?controller=chamcong&action=exportExcel&thang=<?= $thang ?>&nam=<?= $nam ?>"
   class="btn add">📥 Xuất Excel</a>

</form>

<table class="table">

<thead>
<tr>
<th rowspan="2">Mã NV</th>
<th rowspan="2" style="text-align:left">Nhân viên</th>

<?php for($d=1;$d<=$soNgay;$d++): ?>

<th><?= $d ?></th>
<?php endfor; ?>

<th rowspan="2">Tổng</th>
</tr>

<tr>
<?php for($d=1;$d<=$soNgay;$d++):
$time = strtotime("$nam-$thang-$d");
$thu = date('N',$time);
?>
<th class="<?= ($thu>=6)?'weekend':'' ?>">
<?= ['T2','T3','T4','T5','T6','T7','CN'][$thu-1] ?>
</th>
<?php endfor; ?>
</tr>
</thead>

<tbody>

<?php if(empty($data)): ?>
<tr>
<td colspan="<?= $soNgay+3 ?>">Không có dữ liệu</td>
</tr>
<?php else: ?>

<?php foreach($data as $tenPB => $dsNV): ?>

<tr class="pb-row">
<td colspan="<?= $soNgay+3 ?>">🏢 <?= $tenPB ?></td>
</tr>

<?php foreach($dsNV as $nv): ?>
<tr>

<td><?= $nv['MaNV'] ?></td>
<td style="text-align:left"><?= $nv['HoTen'] ?></td>

<?php for($d=1;$d<=$soNgay;$d++):
$key = sprintf('%02d',$d);
$tt = $nv['Ngay'][$key] ?? '';
?>

<td class="cell"
    data-manv="<?= $nv['MaNV'] ?>"
    data-day="<?= $d ?>"
    style="cursor:pointer">

<?php
if($tt=='X') echo '<span class="status dilam">✔</span>';
elseif($tt=='P') echo '<span class="status nghi">P</span>';
elseif($tt=='M') echo '<span class="warn">M</span>';
elseif($tt=='V') echo '<span style="color:red;font-weight:bold">V</span>';
else echo '<span style="color:#bbb">+</span>';
?>

</td>

<?php endfor; ?>

<td><b><?= $nv['TongCong'] ?? 0 ?></b></td>

</tr>
<?php endforeach; ?>

<?php endforeach; ?>
<?php endif; ?>

</tbody>
</table>

<br>
<b>Chú thích:</b>
✔ Đi làm |
P Nghỉ phép |
M Đi muộn

</main>
</div>
<script>
const CHAMCONG_CONFIG = {
    thang: <?= $thang ?>,
    nam: <?= $nam ?>
};
</script>
<?php include 'views/layout/footer.php'; ?>