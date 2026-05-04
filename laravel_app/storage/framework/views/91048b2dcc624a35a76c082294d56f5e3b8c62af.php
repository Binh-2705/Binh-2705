<?php
    $title = 'Học viên đào tạo';
    $subtitle = 'Quản lý danh sách tham gia và kết quả khóa đào tạo trên hệ thống';
?>


<?php $__env->startSection('content'); ?>
<section class="panel">
    <div class="entity-head">
        <div>
            <div class="muted">Khóa đào tạo</div>
            <h3 class="top-gap-sm"><?php echo e($course['TenKhoaDaoTao']); ?></h3>
            <div class="muted"><?php echo e($course['TuNgay']); ?> đến <?php echo e($course['DenNgay']); ?> · <?php echo e($course['DonViToChuc'] ?: 'Nội bộ'); ?></div>
        </div>
        <div class="button-row spaced">
            <a class="btn btn-secondary" href="<?php echo e(route('daotao.edit', ['training' => $course['MaKDT']])); ?>">Sửa khóa</a>
            <a class="btn btn-secondary" href="<?php echo e(route('daotao.index')); ?>">Về danh sách</a>
        </div>
    </div>
    <?php if(!$canEvaluate): ?>
        <div class="muted top-gap-md">Khóa học chưa qua ngày kết thúc, bạn vẫn có thể gán học viên. Kết quả có thể được cập nhật sớm nếu cần.</div>
    <?php endif; ?>
</section>

<?php if(in_array('them_tham_gia_dao_tao', session('quyen', []), true)): ?>
<section class="panel">
    <form method="post" action="<?php echo e(route('daotao.hocvien.store', ['training' => $course['MaKDT']])); ?>" class="filter-grid compact-wide">
        <?php echo csrf_field(); ?>
        <div>
            <label for="MaNV" class="wide-search-label">Thêm nhân viên vào khóa</label>
            <select id="MaNV" name="MaNV" required>
                <option value="">Chọn nhân viên</option>
                <?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($employee['MaNV']); ?>"><?php echo e($employee['HoTen']); ?> (#<?php echo e($employee['MaNV']); ?>)</option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div>
            <button class="btn" type="submit">Thêm học viên</button>
        </div>
    </form>
</section>
<?php endif; ?>

<section class="panel">
    <div class="table-shell">
        <table class="data-table table-compact">
            <thead>
                <tr>
                    <th>Nhân viên</th>
                    <th>Kết quả</th>
                    <th>Điểm</th>
                    <th>Ghi chú</th>
                    <th>Cập nhật</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $participants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $participant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td>
                            <strong><?php echo e($participant['HoTen']); ?></strong>
                            <div class="muted">MãNV: <?php echo e($participant['MaNV']); ?></div>
                        </td>
                        <td>
                            <form method="post" action="<?php echo e(route('daotao.hocvien.ketqua', ['participant' => $participant['MaTGDT']])); ?>" class="participants-form-grid">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="MaKDT" value="<?php echo e($course['MaKDT']); ?>">
                                <select name="KetQua">
                                    <?php $__currentLoopData = ['Đạt', 'Không đạt', 'Chưa đánh giá']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($status); ?>" <?php if($participant['KetQua'] === $status): echo 'selected'; endif; ?>><?php echo e($status); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <input name="DiemDanhGia" type="number" min="0" max="10" step="0.1" value="<?php echo e($participant['DiemDanhGia']); ?>">
                                <input name="GhiChu" value="<?php echo e($participant['GhiChu']); ?>" placeholder="Ghi chú">
                                <button class="btn" type="submit" <?php if(!in_array('capnhat_ketqua_dao_tao', session('quyen', []), true)): echo 'disabled'; endif; ?>>Lưu</button>
                            </form>
                        </td>
                        <td class="hidden-cell"></td>
                        <td class="hidden-cell"></td>
                        <td class="hidden-cell"></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="5" class="muted">Chưa có nhân viên tham gia khóa đào tạo này.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\du_an2\laravel_app\resources\views/daotao/participants.blade.php ENDPATH**/ ?>