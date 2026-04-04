<?php include 'views/layout/header.php'; ?>
<?php include 'views/layout/sidebar.php'; ?>

<?php
$allFunctions = [];
while ($func = mysqli_fetch_assoc($functions)) {
    $allFunctions[] = $func;
}

if (!function_exists('permissionGroupLabel')) {
    function permissionGroupLabel($tenChucNang) {
        $key = strtolower($tenChucNang);

        if (strpos($key, 'nhanvien') !== false) return 'Nhân viên';
        if (strpos($key, 'hoso') !== false || strpos($key, 'ho_so') !== false) return 'Hồ sơ';
        if (strpos($key, 'phancong') !== false) return 'Phân công';
        if (strpos($key, 'hopdong') !== false || strpos($key, 'lich_su_luong') !== false) return 'Hợp đồng';
        if (strpos($key, 'dot_tuyen') !== false || strpos($key, 'ung_vien') !== false || strpos($key, 'phong_van') !== false || strpos($key, 'danh_gia') !== false) return 'Tuyển dụng';
        if (strpos($key, 'dao_tao') !== false) return 'Đào tạo';
        if (strpos($key, 'chamcong') !== false || strpos($key, 'cham_cong') !== false) return 'Chấm công';
        if (strpos($key, 'nghiphep') !== false) return 'Nghỉ phép';
        if (strpos($key, 'baohiem') !== false) return 'Bảo hiểm';
        if (strpos($key, 'khenthuong') !== false) return 'Khen thưởng';
        if (strpos($key, 'luong') !== false || strpos($key, 'ngachluong') !== false || strpos($key, 'bacluong') !== false) return 'Lương';
        if (strpos($key, 'phongban') !== false) return 'Phòng ban';
        if (strpos($key, 'chucvu') !== false) return 'Chức vụ';
        if (strpos($key, 'taikhoan') !== false) return 'Tài khoản';
        if (strpos($key, 'baocao') !== false) return 'Báo cáo';
        return 'Khác';
    }
}

$groupOrder = [
    'Nhân viên', 'Hồ sơ', 'Phân công', 'Hợp đồng', 'Tuyển dụng', 'Đào tạo',
    'Chấm công', 'Nghỉ phép', 'Bảo hiểm', 'Khen thưởng', 'Lương',
    'Phòng ban', 'Chức vụ', 'Tài khoản', 'Báo cáo', 'Khác'
];
?>

<div class="main-content">
    <header>
        <h1>QUẢN LÝ PHÂN QUYỀN</h1>
    </header>

    <?php if (!empty($_GET['msg'])): ?>
        <?php if ($_GET['msg'] === 'reset_success'): ?>
            <div class="permission-alert success">✅ Đã khôi phục quyền mặc định cho vai trò.</div>
        <?php elseif ($_GET['msg'] === 'no_seed_default'): ?>
            <div class="permission-alert error">❌ Không tìm thấy bộ quyền mặc định trong file seed.</div>
        <?php elseif ($_GET['msg'] === 'invalid_role'): ?>
            <div class="permission-alert error">❌ Vai trò không hợp lệ.</div>
        <?php endif; ?>
    <?php endif; ?>

    <div class="permission-wrap">
        <?php while($role = mysqli_fetch_assoc($roles)): ?>
            <div class="permission-card">
                <h3>👤 Vai trò: <?= htmlspecialchars($role['TenVaiTro']) ?></h3>

                <div class="permission-content">
                    <form method="POST" action="index.php?controller=phanquyen&action=capNhat">
                        <input type="hidden" name="MaVaiTro" value="<?= (int)$role['MaVaiTro'] ?>">

                        <?php
                            $currentPermissions = $this->model->getPermissionByRole($role['MaVaiTro']);
                            $groupedFunctions = [];
                            foreach ($allFunctions as $fn) {
                                $label = permissionGroupLabel($fn['TenChucNang']);
                                if (!isset($groupedFunctions[$label])) {
                                    $groupedFunctions[$label] = [];
                                }
                                $groupedFunctions[$label][] = $fn;
                            }
                        ?>

                        <div class="permission-toolbar">
                            <input
                                type="text"
                                class="search-box permission-search"
                                placeholder="Tìm quyền trong vai trò này..."
                            >
                            <div class="permission-bulk-actions">
                                <button type="button" class="btn search permission-select-all">Chọn tất cả</button>
                                <button type="button" class="btn cancel permission-clear-all">Bỏ chọn</button>
                            </div>
                        </div>

                        <?php foreach ($groupOrder as $groupName): ?>
                            <?php if (empty($groupedFunctions[$groupName])) continue; ?>
                            <div class="permission-group">
                                <h4 class="section-title"><?= htmlspecialchars($groupName) ?> (<?= count($groupedFunctions[$groupName]) ?>)</h4>
                                <div class="permission-grid">
                                    <?php foreach($groupedFunctions[$groupName] as $func): ?>
                                        <label class="permission-item" data-name="<?= htmlspecialchars(strtolower($func['TenChucNang'])) ?>">
                                            <input
                                                type="checkbox"
                                                name="chucnang[]"
                                                value="<?= (int)$func['MaCN'] ?>"
                                                <?= in_array($func['MaCN'], $currentPermissions) ? 'checked' : '' ?>
                                            >
                                            <span><?= htmlspecialchars($func['TenChucNang']) ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <div class="permission-actions">
                            <button
                                type="submit"
                                class="btn reset"
                                formaction="index.php?controller=phanquyen&action=khoiPhucMacDinh"
                                onclick="return confirm('Khôi phục vai trò này về quyền mặc định?')"
                            >↺ Khôi phục mặc định</button>
                            <button type="submit" class="btn add">💾 Lưu quyền</button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

</div>

<script>
document.querySelectorAll('.permission-card').forEach(function(card) {
    var searchInput = card.querySelector('.permission-search');
    var selectAllBtn = card.querySelector('.permission-select-all');
    var clearAllBtn = card.querySelector('.permission-clear-all');

    function visibleItems() {
        return Array.from(card.querySelectorAll('.permission-item')).filter(function(item) {
            return item.style.display !== 'none';
        });
    }

    function refreshGroups() {
        card.querySelectorAll('.permission-group').forEach(function(group) {
            var hasVisible = Array.from(group.querySelectorAll('.permission-item')).some(function(item) {
                return item.style.display !== 'none';
            });
            group.style.display = hasVisible ? '' : 'none';
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            var term = this.value.trim().toLowerCase();
            card.querySelectorAll('.permission-item').forEach(function(item) {
                var value = item.getAttribute('data-name') || '';
                item.style.display = (!term || value.indexOf(term) !== -1) ? '' : 'none';
            });
            refreshGroups();
        });
    }

    if (selectAllBtn) {
        selectAllBtn.addEventListener('click', function() {
            visibleItems().forEach(function(item) {
                var cb = item.querySelector('input[type="checkbox"]');
                if (cb) cb.checked = true;
            });
        });
    }

    if (clearAllBtn) {
        clearAllBtn.addEventListener('click', function() {
            visibleItems().forEach(function(item) {
                var cb = item.querySelector('input[type="checkbox"]');
                if (cb) cb.checked = false;
            });
        });
    }
});
</script>

<?php include 'views/layout/footer.php'; ?>