<?php $title = 'Duyệt yêu cầu sửa hồ sơ' ?>
<?php $subtitle = 'Hàng đợi cập nhật hồ sơ cho admin và quản lý' ?>


<?php $__env->startSection('content'); ?>
<section class="panel">
    <div class="muted">Kiểm tra thông tin đề nghị và ghi chú phê duyệt hoặc từ chối để truy vết sau này.</div>
</section>

<section class="panel">
    <div class="table-shell">
        <table class="data-table table-compact">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nhân viên</th>
                    <th>Thông tin đề nghị</th>
                    <th>Ghi chú</th>
                    <th>Xử lý</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $requests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $request): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td>#<?php echo e($request['id']); ?></td>
                        <td>
                            <strong><?php echo e($request['HoTen'] ?? ''); ?></strong><br>
                            MãNV: <?php echo e($request['MaNV']); ?><br>
                            Điện thoại: <?php echo e($request['DienThoai'] ?? ''); ?>

                        </td>
                        <td>
                            <div>CCCD: <?php echo e(data_get($request, 'payload.CCCD', '')); ?></div>
                            <div>Địa chỉ: <?php echo e(data_get($request, 'payload.DiaChi', '')); ?></div>
                            <div>Trình độ: <?php echo e(data_get($request, 'payload.TrinhDo', '')); ?></div>
                            <div>Chuyên môn: <?php echo e(data_get($request, 'payload.ChuyenMon', '')); ?></div>
                        </td>
                        <td><?php echo e($request['note'] ?? ''); ?></td>
                        <td>
                            <form method="post" action="<?php echo e(route('hosocanhan.review-requests.resolve', ['requestId' => $request['id']])); ?>" class="review-action-form">
                                <?php echo csrf_field(); ?>
                                <textarea name="review_note" rows="2" placeholder="Ghi chu khi duyet hoac tu choi"></textarea>
                                <div class="button-row">
                                    <button class="btn" type="submit" name="decision" value="approve">Duyet</button>
                                    <button class="btn btn-danger" type="submit" name="decision" value="reject">Tu choi</button>
                                </div>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="5" class="muted">Khong co yeu cau dang cho duyet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\du_an2\laravel_app\resources\views/hosocanhan/review_requests.blade.php ENDPATH**/ ?>