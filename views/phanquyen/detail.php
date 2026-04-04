<?php include 'views/layout/header.php'; ?>
<?php include 'views/layout/sidebar.php'; ?>

<div class="main-content">
    <header>
        <h1>CHI TIẾT PHÂN QUYỀN TÀI KHOẢN</h1>
    </header>

    <div class="permission-card">
        <h3>🔹 Vai trò</h3>
        <div class="permission-content">
            <ul class="permission-chip-list">
                <?php while($r = mysqli_fetch_assoc($roles)): ?>
                    <li class="permission-chip"><?= htmlspecialchars($r['TenVaiTro']) ?></li>
                <?php endwhile; ?>
            </ul>
        </div>
    </div>

    <div class="permission-card" style="margin-top: 14px;">
        <h3>🔹 Chức năng</h3>
        <div class="permission-content">
            <ul class="permission-chip-list">
                <?php while($p = mysqli_fetch_assoc($permissions)): ?>
                    <li class="permission-chip"><?= htmlspecialchars($p['TenChucNang']) ?></li>
                <?php endwhile; ?>
            </ul>
        </div>
    </div>

    <div style="margin-top:14px;">
        <a class="btn cancel" href="index.php?controller=phanquyen&action=index">↩ Quay lại</a>
    </div>
</div>

</div>
<?php include 'views/layout/footer.php'; ?>