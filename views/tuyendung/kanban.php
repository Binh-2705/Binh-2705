<?php include 'views/layout/header.php'; ?>
<?php include 'views/layout/sidebar.php'; ?>
<h2>Pipeline tuyển dụng</h2>

<div class="kanban">

<div class="column" data-status="Nộp hồ sơ">
<h3>Nộp hồ sơ</h3>

<?php foreach($data as $row): ?>
<?php if($row['TrangThai']=="Nộp hồ sơ"): ?>

<div class="card"
draggable="true"
data-id="<?= $row['MaHS'] ?>">
<?= $row['HoTen'] ?>
</div>

<?php endif ?>
<?php endforeach ?>

</div>


<div class="column" data-status="Phỏng vấn">
<h3>Phỏng vấn</h3>

<?php foreach($data as $row): ?>
<?php if($row['TrangThai']=="Phỏng vấn"): ?>

<div class="card"
draggable="true"
data-id="<?= $row['MaHS'] ?>">
<?= $row['HoTen'] ?>
</div>

<?php endif ?>
<?php endforeach ?>

</div>


<div class="column" data-status="Offer">
<h3>Offer</h3>

<?php foreach($data as $row): ?>
<?php if($row['TrangThai']=="Offer"): ?>

<div class="card"
draggable="true"
data-id="<?= $row['MaHS'] ?>">
<?= $row['HoTen'] ?>
</div>

<?php endif ?>
<?php endforeach ?>

</div>


<div class="column" data-status="Nhận việc">
<h3>Nhận việc</h3>

<?php foreach($data as $row): ?>
<?php if($row['TrangThai']=="Nhận việc"): ?>

<div class="card"
draggable="true"
data-id="<?= $row['MaHS'] ?>">
<?= $row['HoTen'] ?>
</div>

<?php endif ?>
<?php endforeach ?>

</div>

</div>
<?php include 'views/layout/footer.php'; ?>