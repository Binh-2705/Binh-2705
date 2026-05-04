<?php $title = 'Phòng ban' ?>
<?php $subtitle = 'Danh sách phòng ban' ?>


<?php $__env->startSection('content'); ?>
    <section class="panel">
        <div class="table-shell">
            <table class="data-table table-compact">
                <thead>
                    <tr>
                        <th>Mã PB</th>
                        <th>Tên phòng ban</th>
                        <th>Mô tả</th>
                        <th>Số nhân viên</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $department): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($department->MaPB); ?></td>
                            <td><strong><?php echo e($department->TenPB); ?></strong></td>
                            <td><?php echo e($department->MoTa ?: 'Không có mô tả'); ?></td>
                            <td><?php echo e($department->SoNhanVien); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="4" class="muted">Không có phòng ban.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if($departments->lastPage() > 1): ?>
            <div class="top-gap-lg"><?php echo e($departments->links()); ?></div>
        <?php endif; ?>
    </section>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\du_an2\laravel_app\resources\views/phongban/index.blade.php ENDPATH**/ ?>