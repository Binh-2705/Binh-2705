<?php $title = 'Lương' ?>
<?php $subtitle = 'Quản trị bảng lương' ?>
<?php $canRun = in_array('tinh_luong_thang', session('quyen', []), true) ?>
<?php $canView = in_array('xem_luong', session('quyen', []), true) ?>
<?php $canLock = in_array('chot_luong', session('quyen', []), true) ?>
<?php $canUnlock = in_array('mo_chot_luong', session('quyen', []), true) ?>
<?php $canEdit = in_array('mo_chot_luong', session('quyen', []), true) || in_array('chot_luong', session('quyen', []), true) ?>
<?php $isSelfView = $isSelfView ?? false ?>


<?php $__env->startSection('content'); ?>
    <?php if($canRun && !$isSelfView): ?>
        <section class="panel">
            <form method="post" action="<?php echo e(route('luong.run-monthly')); ?>">
                <?php echo csrf_field(); ?>
                <div class="field-grid">
                    <div>
                        <label for="run-month">Tháng tính lương</label>
                        <input id="run-month" type="number" name="thang" min="1" max="12" value="<?php echo e(request('month', now()->month)); ?>" required>
                    </div>
                    <div>
                        <label for="run-year">Năm</label>
                        <input id="run-year" type="number" name="nam" value="<?php echo e(request('year', now()->year)); ?>" required>
                    </div>
                    <div class="full-span button-row">
                        <button type="submit" class="btn">Tính lương tháng</button>
                        <a href="<?php echo e(route('luong.create')); ?>" class="btn btn-secondary">Thêm bảng lương</a>
                    </div>
                </div>
            </form>
        </section>
    <?php endif; ?>

    <?php if(!$isSelfView): ?>
    <section class="panel">
        <form method="get" action="<?php echo e(route('luong.index')); ?>">
            <div class="field-grid">
                <div>
                    <label for="q">Nhân viên</label>
                    <input id="q" name="q" value="<?php echo e($filters['q'] ?? ''); ?>" placeholder="Tìm theo họ tên hoặc mã nhân viên">
                </div>
                <div>
                    <label for="month">Tháng</label>
                    <input id="month" name="month" type="number" min="1" max="12" value="<?php echo e($filters['month'] ?? ''); ?>" placeholder="Tháng">
                </div>
                <div>
                    <label for="year">Năm</label>
                    <input id="year" name="year" type="number" min="2000" max="2100" value="<?php echo e($filters['year'] ?? ''); ?>" placeholder="Năm">
                </div>
                <div>
                    <label for="status">Trạng thái</label>
                    <input id="status" name="status" value="<?php echo e($filters['status'] ?? ''); ?>" placeholder="Chưa chốt / Đã chốt">
                </div>
                <div class="full-span button-row">
                    <button class="btn" type="submit">Lọc bảng lương</button>
                </div>
            </div>
        </form>
    </section>
    <?php endif; ?>

    <section class="panel">
        <div class="table-shell">
            <table class="data-table table-compact">
                <thead>
                    <tr>
                        <th>Mã BL</th>
                        <th>Nhân viên</th>
                        <th>Tháng</th>
                        <th>Năm</th>
                        <th>Thực nhận</th>
                        <th>Tổng lương</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $records; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $record): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($record->MaBL); ?></td>
                            <td>
                                <strong><?php echo e($record->HoTen); ?></strong>
                                <div class="muted">Mã NV: <?php echo e($record->MaNV); ?></div>
                            </td>
                            <td><?php echo e($record->Thang); ?></td>
                            <td><?php echo e($record->Nam); ?></td>
                            <td><strong class="metric-value-danger"><?php echo e(number_format((float) $record->TongLuong, 0, ',', '.')); ?></strong></td>
                            <td><strong class="metric-value-danger"><?php echo e(number_format((float) $record->TongLuong, 0, ',', '.')); ?></strong></td>
                            <td>
                                <?php if($record->TrangThai === 'Đã chốt'): ?>
                                    <span class="status-text-ok">Đã chốt</span>
                                <?php else: ?>
                                    <span class="status-text-warn"><?php echo e($record->TrangThai); ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($canView || $canEdit || $canLock || $canUnlock): ?>
                                    <div class="button-row">
                                        <?php if($canView): ?>
                                            <a href="<?php echo e(route('luong.show', ['payroll' => $record->MaBL])); ?>" class="btn btn-secondary">Xem</a>
                                        <?php endif; ?>
                                        <?php if($canEdit): ?>
                                            <a href="<?php echo e(route('luong.edit', ['payroll' => $record->MaBL])); ?>" class="btn btn-secondary">Sửa</a>
                                        <?php endif; ?>
                                        <?php if($record->TrangThai !== 'Đã chốt' && $canLock): ?>
                                            <a href="<?php echo e(route('luong.lock.legacy', ['payroll' => $record->MaBL])); ?>" class="btn">Chốt lương</a>
                                        <?php endif; ?>
                                        <?php if($record->TrangThai === 'Đã chốt' && $canUnlock): ?>
                                            <a href="<?php echo e(route('luong.unlock.legacy', ['payroll' => $record->MaBL])); ?>" class="btn btn-secondary">Mở chốt</a>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <span class="muted-inline-note">Chỉ xem</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="8" class="muted">Chưa có dữ liệu lương.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if($records->lastPage() > 1): ?>
            <div class="top-gap-lg"><?php echo e($records->links()); ?></div>
        <?php endif; ?>
    </section>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\du_an2\laravel_app\resources\views/luong/index.blade.php ENDPATH**/ ?>