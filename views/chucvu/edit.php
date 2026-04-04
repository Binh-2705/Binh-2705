<?php include 'views/layout/header.php'; ?>
<?php include 'views/layout/sidebar.php'; ?>
<main class="main-content">
<header>
    <h1>✏️ Sửa Chức vụ: <?= htmlspecialchars($chucVu['TenCV']) ?></h1>
</header>

<form action="index.php?controller=chucvu&action=edit&id=<?= $chucVu['MaCV'] ?>"
      method="POST"
      class="form-nv">

<?php if (!empty($message)): ?>
    <p style="color:red; font-weight:bold; margin-bottom:15px;">
        <?= htmlspecialchars($message) ?>
    </p>
<?php endif; ?>

<div class="form-group">
    <label>Mã chức vụ</label>
    <input type="text" value="<?= $chucVu['MaCV'] ?>" disabled>
</div>

<div class="form-group">
    <label for="TenChucVu">Tên chức vụ</label>
    <input type="text"
           id="TenChucVu"
           name="TenChucVu"
           required
           value="<?= htmlspecialchars($chucVu['TenCV']) ?>">
</div>

<div class="form-group">
    <label for="HeSoChucVu">Hệ số chức vụ</label>
    <input type="number"
           step="0.01"
           min="0"
           id="HeSoChucVu"
           name="HeSoChucVu"
           value="<?= htmlspecialchars($chucVu['HeSoChucVu']) ?>">
</div>

<div class="form-group">
    <label for="PhuCap">Phụ cấp (VNĐ)</label>
    <input type="number"
           step="1000"
           min="0"
           id="PhuCap"
           name="PhuCap"
           value="<?= htmlspecialchars($chucVu['PhuCap']) ?>">
</div>

<div class="form-buttons">
    <button type="submit" class="btn edit">💾 Cập nhật</button>
    <a href="index.php?controller=chucvu&action=index" class="btn cancel">↩️ Quay lại</a>
</div>

</form>
</main>
<?php include 'views/layout/footer.php'; ?>
