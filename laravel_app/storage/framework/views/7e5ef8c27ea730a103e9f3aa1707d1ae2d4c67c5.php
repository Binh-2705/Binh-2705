<?php
    $columns = collect($resourceConfig['columns'])->pluck('field')->filter(fn ($field) => $field !== '__resource_id')->take(8)->values();
?>
<section class="panel">
    <form method="get" class="filter-grid single-wide">
        <div>
            <label for="q" class="wide-search-label">Tìm kiếm</label>
            <input id="q" name="q" value="<?php echo e($filters['q'] ?? ''); ?>" placeholder="Tìm trong danh mục này">
        </div>
        <div class="button-row">
            <button class="btn" type="submit">Lọc</button>
            <?php if(in_array($moduleConfig['permission']['view'], session('quyen', []), true)): ?>
                <a class="btn btn-secondary" href="<?php echo e(route(($routeKey ?? $moduleKey) . '.export-excel', request()->only(['q']))); ?>">Xuất Excel</a>
            <?php endif; ?>
            <?php if($moduleKey === 'employee-profiles'): ?>
                <?php
                    $viewerRole = strtolower(trim((string) data_get(session('taikhoan', []), 'VaiTro', '')));
                ?>
                <?php if(in_array($viewerRole, ['admin', 'quanly'], true)): ?>
                    <a class="btn btn-secondary" href="<?php echo e(route('hosocanhan.review-requests')); ?>">Duyệt yêu cầu</a>
                <?php endif; ?>
            <?php endif; ?>
            <?php if(!($resourceConfig['read_only'] ?? false) && in_array($moduleConfig['permission']['create'], session('quyen', []), true)): ?>
                <a class="btn btn-secondary" href="<?php echo e(route(($routeKey ?? $moduleKey) . '.create')); ?>">Thêm mới</a>
            <?php endif; ?>
        </div>
    </form>
</section>

<section class="panel">
    <div class="table-shell">
        <table class="data-table table-compact">
            <thead>
                <tr>
                    <?php $__currentLoopData = $columns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $column): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <th><?php echo e($column); ?></th>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <?php $__currentLoopData = $columns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $column): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <td><?php echo e(data_get($item, $column)); ?></td>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <td>
                            <?php if($resourceConfig['read_only'] ?? false): ?>
                                <span class="muted">Chỉ xem</span>
                            <?php else: ?>
                                <div class="button-row">
                                <?php if($moduleKey === 'employee-profiles' && in_array($moduleConfig['permission']['view'], session('quyen', []), true)): ?>
                                    <a class="btn btn-secondary" href="<?php echo e(route('hosocanhan.show', ['profile' => data_get($item, '__resource_id')])); ?>">Xem</a>
                                <?php endif; ?>
                                <?php if(in_array($moduleConfig['permission']['update'], session('quyen', []), true)): ?>
                                    <a class="btn btn-secondary" href="<?php echo e(route(($routeKey ?? $moduleKey) . '.edit', ['record' => data_get($item, '__resource_id')])); ?>">Sửa</a>
                                    <?php if($moduleKey === 'accounts'): ?>
                                        <form method="post" action="<?php echo e(route('taikhoan.reset-temporary', ['account' => data_get($item, '__resource_id')])); ?>" class="inline-form" onsubmit="return confirm('Cấp mật khẩu tạm cho tài khoản này?');">
                                            <?php echo csrf_field(); ?>
                                            <button class="btn btn-secondary" type="submit">Cấp lại mật khẩu tạm</button>
                                        </form>
                                    <?php endif; ?>
                                <?php endif; ?>
                                <?php if($moduleKey === 'contracts'): ?>
                                    <?php if(in_array('giahan_hopdong', session('quyen', []), true)): ?>
                                        <a class="btn btn-secondary" href="<?php echo e(route('hopdong.renew', ['contract' => data_get($item, '__resource_id')])); ?>">Gia hạn</a>
                                    <?php endif; ?>
                                    <?php if(in_array('chamdut_hopdong', session('quyen', []), true)): ?>
                                        <form method="post" action="<?php echo e(route('hopdong.terminate', ['contract' => data_get($item, '__resource_id')])); ?>" class="inline-form" onsubmit="return confirm('Chấm dứt hợp đồng này?');">
                                            <?php echo csrf_field(); ?>
                                            <button class="btn btn-danger" type="submit">Chấm dứt</button>
                                        </form>
                                    <?php endif; ?>
                                    <?php if(in_array('xem_lich_su_luong', session('quyen', []), true)): ?>
                                        <a class="btn btn-secondary" href="<?php echo e(route('hopdong.salary-history', ['contract' => data_get($item, '__resource_id')])); ?>">Lịch sử lương</a>
                                    <?php endif; ?>
                                <?php endif; ?>
                                <?php if(in_array($moduleConfig['permission']['delete'], session('quyen', []), true)): ?>
                                    <form method="post" action="<?php echo e(route(($routeKey ?? $moduleKey) . '.destroy', ['record' => data_get($item, '__resource_id')])); ?>" class="inline-form" onsubmit="return confirm('Xóa bản ghi này?');">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button class="btn btn-danger" type="submit">Xóa</button>
                                    </form>
                                <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="<?php echo e($columns->count() + 1); ?>" class="muted">Không có dữ liệu.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="top-gap-lg"><?php echo e($items->links()); ?></div>
</section>
<?php /**PATH C:\xampp\htdocs\du_an2\laravel_app\resources\views/resource_modules/partials/index_content.blade.php ENDPATH**/ ?>