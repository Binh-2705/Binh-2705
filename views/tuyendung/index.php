<?php include 'views/layout/header.php'; ?>
<?php include 'views/layout/sidebar.php'; ?>

<main class="main-content">

<header>
<h1>💼 Quản lý Tuyển dụng</h1>
</header>

<div class="actions">

<a href="index.php?controller=tuyendung&action=themDot" class="btn add">
➕ Thêm đợt tuyển
</a>

<a href="index.php?controller=tuyendung&action=ungvien" class="btn">
👤 Quản lý ứng viên
</a>

<li>
<a href="index.php?controller=tuyendung&action=dashboard">
📊 Dashboard
</a>
</li>

</div>

<table class="table">

<thead>
<tr>
<th>Mã</th>
<th>Tên đợt</th>
<th>Vị trí</th>
<th>Số lượng</th>
<th>Từ ngày</th>
<th>Đến ngày</th>
<th>Trạng thái</th>
<th>Thao tác</th>
</tr>
</thead>

<tbody>

<?php if($dot && $dot->num_rows>0): ?>
<?php while($row=$dot->fetch_assoc()): ?>

<tr>

<td><?= $row['MaDTD'] ?></td>

<td><?= htmlspecialchars($row['TenDotTuyenDung']) ?></td>

<td><?= htmlspecialchars($row['ViTriTuyenDung']) ?></td>

<td><?= $row['SoLuong'] ?></td>

<td><?= date('d/m/Y',strtotime($row['TuNgay'])) ?></td>

<td><?= $row['DenNgay'] ? date('d/m/Y',strtotime($row['DenNgay'])) : '' ?></td>

<td>

<?php
$tt=$row['TrangThai'];

if($tt=="Đang tuyển")
echo "<span class='badge success'>Đang tuyển</span>";

elseif($tt=="Sắp mở")
echo "<span class='badge warning'>Sắp mở</span>";

else
echo "<span class='badge danger'>Đã đóng</span>";
?>

</td>

<td>
<div class="table-actions">
<a href="index.php?controller=tuyendung&action=hosodot&id=<?= $row['MaDTD'] ?>" 
   class="btn search"
   title="Hồ sơ">📄</a>

<a href="index.php?controller=tuyendung&action=xoaDot&id=<?= $row['MaDTD'] ?>" 
   class="btn delete"
   title="Xóa"
   onclick="return confirm('Bạn có chắc muốn xóa?')">🗑️</a>
</div>
</td>

</tr>

<?php endwhile ?>
<?php else: ?>

<tr>
<td colspan="8">Chưa có đợt tuyển</td>
</tr>

<?php endif ?>

</tbody>
</table>

<?php if (($totalPages ?? 1) > 1): ?>
<div class="pagination-wrap">
   <a class="page-link <?= (int)$page <= 1 ? 'disabled' : '' ?>" href="index.php?controller=tuyendung&action=index&page=<?= max(1, (int)$page - 1) ?>">← Trước</a>
   <span class="page-indicator">Trang <?= (int)$page ?> / <?= (int)$totalPages ?></span>
   <a class="page-link <?= (int)$page >= (int)$totalPages ? 'disabled' : '' ?>" href="index.php?controller=tuyendung&action=index&page=<?= min((int)$totalPages, (int)$page + 1) ?>">Sau →</a>
</div>
<?php endif; ?>

</main>
<?php include 'views/layout/footer.php'; ?>