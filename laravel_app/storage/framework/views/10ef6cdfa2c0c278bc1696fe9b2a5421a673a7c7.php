<?php $title = 'Chi tiết hồ sơ nhân viên' ?>
<?php $subtitle = 'Thông tin chi tiết hồ sơ nhân sự trên hệ thống' ?>


<?php $__env->startSection('content'); ?>
<section class="panel">
    <div class="profile-shell">
        <div>
            <?php if(!empty($profile['Anh'])): ?>
                <img src="<?php echo e(route('legacy.upload', ['path' => ltrim((string) $profile['Anh'], '/')])); ?>" alt="Ảnh nhân viên" class="profile-avatar">
            <?php else: ?>
                <div class="profile-avatar-empty">Chưa có ảnh</div>
            <?php endif; ?>
        </div>
        <div>
            <h3 class="no-top-margin"><?php echo e($profile['HoTen'] ?? 'Chưa cập nhật'); ?></h3>
            <div class="muted top-gap-sm"><?php echo e($profile['TenCV'] ?? 'Chưa có chức vụ'); ?> · <?php echo e($profile['TenPB'] ?? 'Chưa có phòng ban'); ?></div>
            <div class="info-pills">
                <span class="info-pill">MãNV: <?php echo e($profile['MaNV'] ?? '---'); ?></span>
                <span class="info-pill">MãHồSơ: <?php echo e($profile['MaHoSo'] ?? '---'); ?></span>
                <span class="info-pill">Hôn nhân: <?php echo e($profile['TrangThaiHonNhan'] ?? '---'); ?></span>
            </div>
        </div>
    </div>
</section>

<section class="detail-grid">
    <article class="panel"><h3 class="no-top-margin">Giấy tờ</h3><div class="muted">CCCD</div><div><?php echo e($profile['CCCD'] ?? '---'); ?></div><div class="muted top-gap-md">Nơi cấp</div><div><?php echo e($profile['NoiCap'] ?? '---'); ?></div><div class="muted top-gap-md">Ngày cấp</div><div><?php echo e($profile['NgayCap'] ?? '---'); ?></div></article>
    <article class="panel"><h3 class="no-top-margin">Thông tin cá nhân</h3><div class="muted">Địa chỉ</div><div><?php echo e($profile['DiaChi'] ?? '---'); ?></div><div class="muted top-gap-md">Dân tộc</div><div><?php echo e($profile['DanToc'] ?? '---'); ?></div><div class="muted top-gap-md">Tôn giáo</div><div><?php echo e($profile['TonGiao'] ?? '---'); ?></div></article>
    <article class="panel"><h3 class="no-top-margin">Thông tin công việc</h3><div class="muted">Trình độ</div><div><?php echo e($profile['TrinhDo'] ?? '---'); ?></div><div class="muted top-gap-md">Chuyên môn</div><div><?php echo e($profile['ChuyenMon'] ?? '---'); ?></div><div class="muted top-gap-md">Ngày vào làm</div><div><?php echo e($profile['NgayVaoLam'] ?? '---'); ?></div></article>
</section>

<section class="panel">
    <a class="btn btn-secondary" href="<?php echo e(route('hosocanhan.index')); ?>">Quay lại danh sách</a>
</section>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\du_an2\laravel_app\resources\views/hosocanhan/show.blade.php ENDPATH**/ ?>