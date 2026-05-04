<?php
    $title = 'Đào tạo';
    $subtitle = 'Quản trị khóa đào tạo trên hệ thống';
?>


<?php $__env->startSection('content'); ?>
<section class="panel">
    <form method="get" class="filter-grid">
        <div><label for="q" class="wide-search-label">Tìm kiếm</label><input id="q" name="q" value="<?php echo e($filters['q'] ?? ''); ?>" placeholder="Tên khóa hoặc đơn vị tổ chức"></div>
        <div><label for="status" class="wide-search-label">Trạng thái</label><select id="status" name="status"><option value="">Tất cả</option><?php $__currentLoopData = ['Lên kế hoạch','Đang đào tạo','Hoàn thành']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($status); ?>" <?php if(($filters['status'] ?? '') === $status): echo 'selected'; endif; ?>><?php echo e($status); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
        <div class="button-row"><button class="btn" type="submit">Lọc</button><?php if(in_array('them_khoa_dao_tao', session('quyen', []), true)): ?><a class="btn btn-secondary" href="<?php echo e(route('daotao.create')); ?>">Thêm mới</a><?php endif; ?></div>
    </form>
</section>

<section class="panel">
    <div class="table-shell"><table class="data-table table-compact"><thead><tr><th>MãKDT</th><th>Tên khóa</th><th>Đơn vị</th><th>Học viên</th><th>Trạng thái</th><th>Thao tác</th></tr></thead><tbody><?php $__empty_1 = true; $__currentLoopData = $courses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $course): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><tr><td><?php echo e($course->MaKDT); ?></td><td><strong><?php echo e($course->TenKhoaDaoTao); ?></strong></td><td><?php echo e($course->DonViToChuc ?: 'Nội bộ'); ?></td><td><?php echo e($course->SoHocVien); ?></td><td><?php echo e($course->TrangThai); ?></td><td><div class="button-row"><?php if(in_array('xem_tham_gia_dao_tao', session('quyen', []), true)): ?><a class="btn btn-secondary" href="<?php echo e(route('daotao.hocvien', ['training' => $course->MaKDT])); ?>">Học viên</a><?php endif; ?> <?php if(in_array('them_khoa_dao_tao', session('quyen', []), true)): ?><a class="btn btn-secondary" href="<?php echo e(route('daotao.edit', ['training' => $course->MaKDT])); ?>">Sửa</a><?php endif; ?> <?php if(in_array('xoa_khoa_dao_tao', session('quyen', []), true)): ?><form method="post" action="<?php echo e(route('daotao.destroy', ['training' => $course->MaKDT])); ?>" class="inline-form" onsubmit="return confirm('Xóa khóa đào tạo này?');"><?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?><button class="btn btn-danger" type="submit">Xóa</button></form><?php endif; ?></div></td></tr><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td colspan="6" class="muted">Không có dữ liệu đào tạo.</td></tr><?php endif; ?></tbody></table></div>
    <div class="top-gap-lg"><?php echo e($courses->links()); ?></div>
</section>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\du_an2\laravel_app\resources\views/daotao/index.blade.php ENDPATH**/ ?>