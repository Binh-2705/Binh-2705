<h1>🏆 Top ứng viên</h1>

<table class="table">

<tr>
<th>Họ tên</th>
<th>Điểm trung bình</th>
</tr>

<?php while($row=$data->fetch_assoc()): ?>

<tr>

<td><?= $row['HoTen'] ?></td>
<td><?= number_format($row['DiemTB'],1) ?></td>

</tr>

<?php endwhile ?>

</table>