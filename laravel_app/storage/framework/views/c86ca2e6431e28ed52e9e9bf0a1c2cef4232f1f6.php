<?php $title = 'Lịch sử lương hợp đồng' ?>
<?php $subtitle = 'Theo dõi các lần thay đổi mức lương của hợp đồng' ?>


<?php $__env->startSection('content'); ?>
<section class="panel">
    <div class="summary-card-grid">
        <div><div class="muted">Số hợp đồng</div><div class="metric-strong"><?php echo e($contract['SoHopDong']); ?></div></div>
        <div><div class="muted">Nhân viên</div><div class="metric-strong"><?php echo e($contract['HoTen']); ?></div></div>
        <div><div class="muted">Bậc hiện tại</div><div class="metric-strong"><?php echo e($contract['TenBac']); ?></div></div>
        <div><div class="muted">Lương hiện tại</div><div class="metric-strong"><?php echo e(number_format($contract['LuongThucTe'], 0, ',', '.')); ?> VNĐ</div></div>
    </div>
</section>

<section class="panel">
    <div class="table-shell">
        <table class="data-table table-compact">
            <thead>
                <tr>
                    <th>Ngày áp dụng</th>
                    <th>Lương cũ</th>
                    <th>Lương mới</th>
                    <th>Chênh lệch</th>
                    <th>Lý do</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $history; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e(\Carbon\Carbon::parse($item['NgayApDung'])->format('d/m/Y')); ?></td>
                        <td class="metric-value-danger"><?php echo e(number_format((float) $item['LuongCu'], 0, ',', '.')); ?> VNĐ</td>
                        <td class="metric-value-success"><?php echo e(number_format((float) $item['LuongMoi'], 0, ',', '.')); ?> VNĐ</td>
                        <td><?php echo e(number_format((float) $item['LuongMoi'] - (float) $item['LuongCu'], 0, ',', '.')); ?> VNĐ</td>
                        <td><?php echo e($item['LyDo']); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="5" class="muted">Chưa có thay đổi lương nào cho hợp đồng này.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="top-gap-lg">
        <a class="btn btn-secondary" href="<?php echo e(route('hopdong.index')); ?>">Quay lại danh sách hợp đồng</a>
    </div>
</section>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\du_an2\laravel_app\resources\views/hopdong/salary_history.blade.php ENDPATH**/ ?>