<?php $title = 'Phân quyền' ?>
<?php $subtitle = 'Quản lý quyền theo vai trò trên hệ thống, thay cho màn legacy' ?>


<?php $__env->startSection('content'); ?>
    <section class="panel">
        <div class="muted">Cập nhật bộ quyền theo vai trò và đồng bộ lại quyền mặc định từ file seed gốc khi cần.</div>
    </section>

    <section class="permission-role-grid">
        <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php $rolePermissions = array_map('intval', $permissionsByRole[$role['MaVaiTro']] ?? []) ?>
            <article class="panel" data-role-card>
                <div class="permission-role-head">
                    <div>
                        <h3 class="no-top-margin">Vai trò: <?php echo e($role['TenVaiTro']); ?></h3>
                        <div class="muted top-gap-sm">Mã vai trò: <?php echo e($role['MaVaiTro']); ?></div>
                    </div>
                    <input type="text" placeholder="Tìm quyền trong vai trò này" data-permission-search class="compact-input permission-search">
                </div>

                <div class="button-row spaced top-gap-lg">
                    <button type="button" class="btn btn-secondary" data-select-all>Chọn tất cả đang hiện</button>
                    <button type="button" class="btn btn-secondary" data-clear-all>Bỏ chọn đang hiện</button>
                </div>

                <form method="post" action="<?php echo e(route('phanquyen.update', ['role' => $role['MaVaiTro']])); ?>">
                    <?php echo csrf_field(); ?>
                    <?php $__currentLoopData = $groupOrder; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $groupName): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if(empty($groupedFunctions[$groupName])): ?>
                            <?php continue; ?>
                        <?php endif; ?>
                        <div data-permission-group class="permission-group">
                            <h4 class="no-top-margin"><?php echo e($groupName); ?> (<?php echo e(count($groupedFunctions[$groupName])); ?>)</h4>
                            <div class="permission-grid top-gap-md">
                                <?php $__currentLoopData = $groupedFunctions[$groupName]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $function): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <label data-permission-item data-name="<?php echo e(strtolower($function['TenChucNang'])); ?>" class="permission-item">
                                        <input type="checkbox" name="chucnang[]" value="<?php echo e($function['MaCN']); ?>" <?php echo e(in_array($function['MaCN'], $rolePermissions, true) ? 'checked' : ''); ?>>
                                        <span><?php echo e($function['TenChucNang']); ?></span>
                                    </label>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                    <div class="button-row spaced">
                        <button class="btn" type="submit">Lưu quyền</button>
                    </div>
                </form>

                <form method="post" action="<?php echo e(route('phanquyen.restore-defaults', ['role' => $role['MaVaiTro']])); ?>" class="top-gap-md">
                    <?php echo csrf_field(); ?>
                    <button class="btn btn-danger" type="submit" onclick="return confirm('Khôi phục quyền mặc định cho vai trò này?');">Khôi phục mặc định</button>
                </form>
            </article>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </section>

    <script>
        document.querySelectorAll('[data-role-card]').forEach(function (card) {
            var searchInput = card.querySelector('[data-permission-search]');
            var selectAllButton = card.querySelector('[data-select-all]');
            var clearAllButton = card.querySelector('[data-clear-all]');

            function visibleItems() {
                return Array.from(card.querySelectorAll('[data-permission-item]')).filter(function (item) {
                    return item.style.display !== 'none';
                });
            }

            function refreshGroups() {
                card.querySelectorAll('[data-permission-group]').forEach(function (group) {
                    var hasVisibleItem = Array.from(group.querySelectorAll('[data-permission-item]')).some(function (item) {
                        return item.style.display !== 'none';
                    });
                    group.style.display = hasVisibleItem ? '' : 'none';
                });
            }

            if (searchInput) {
                searchInput.addEventListener('input', function () {
                    var term = this.value.trim().toLowerCase();
                    card.querySelectorAll('[data-permission-item]').forEach(function (item) {
                        var value = item.getAttribute('data-name') || '';
                        item.style.display = (!term || value.indexOf(term) !== -1) ? '' : 'none';
                    });
                    refreshGroups();
                });
            }

            if (selectAllButton) {
                selectAllButton.addEventListener('click', function () {
                    visibleItems().forEach(function (item) {
                        var checkbox = item.querySelector('input[type="checkbox"]');
                        if (checkbox) {
                            checkbox.checked = true;
                        }
                    });
                });
            }

            if (clearAllButton) {
                clearAllButton.addEventListener('click', function () {
                    visibleItems().forEach(function (item) {
                        var checkbox = item.querySelector('input[type="checkbox"]');
                        if (checkbox) {
                            checkbox.checked = false;
                        }
                    });
                });
            }
        });
    </script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\du_an2\laravel_app\resources\views/phanquyen/index.blade.php ENDPATH**/ ?>