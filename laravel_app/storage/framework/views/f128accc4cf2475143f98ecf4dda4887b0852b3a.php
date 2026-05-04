<?php
    $title = 'Hồ sơ ứng tuyển';
    $subtitle = 'Theo dõi hồ sơ theo từng đợt tuyển trong hệ thống';
?>


<?php $__env->startSection('content'); ?>
    <section class="panel">
        <div class="toolbar toolbar-start">
            <div>
                <div><strong>#<?php echo e($campaign['MaDTD']); ?> - <?php echo e($campaign['TenDotTuyenDung']); ?></strong></div>
                <div class="muted top-gap-sm"><?php echo e($campaign['ViTriTuyenDung']); ?> | <?php echo e($campaign['TrangThai']); ?> | Từ <?php echo e($campaign['TuNgay']); ?> đến <?php echo e($campaign['DenNgay'] ?: 'N/A'); ?></div>
            </div>
            <div class="button-row spaced">
                <?php if(in_array('them_ho_so', session('quyen', []), true)): ?>
                    <a class="btn" href="<?php echo e(route('tuyendung.ungvien.index')); ?>">Chọn ứng viên</a>
                <?php endif; ?>
                <a class="btn btn-secondary" href="<?php echo e(route('tuyendung.index')); ?>">Về đợt tuyển</a>
            </div>
        </div>
    </section>

    <section class="panel">
        <form method="get" class="filter-grid">
            <div>
                <label for="q" class="wide-search-label">Tìm hồ sơ</label>
                <input id="q" name="q" value="<?php echo e($filters['q'] ?? ''); ?>" placeholder="Họ tên, email hoặc điện thoại">
            </div>
            <div>
                <label for="status" class="wide-search-label">Trạng thái</label>
                <select id="status" name="status">
                    <option value="">Tất cả</option>
                    <?php $__currentLoopData = ['Nộp hồ sơ', 'Sàng lọc', 'Phỏng vấn', 'Offer', 'Nhận việc', 'Rớt']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($status); ?>" <?php if(($filters['status'] ?? '') === $status): echo 'selected'; endif; ?>><?php echo e($status); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div><button class="btn" type="submit">Lọc</button></div>
        </form>
    </section>

    <section class="panel">
        <div class="table-shell">
            <table class="data-table table-compact">
                <thead><tr>
                    <th>MãHS</th>
                    <th>Ứng viên</th>
                    <th>CV</th>
                    <th>Ngày nộp</th>
                    <th>Trạng thái</th>
                    <th>Phỏng vấn</th>
                    <th>Thao tác</th>
                </tr></thead>
                <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $applications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $application): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e($application->MaHS); ?></td>
                        <td>
                            <strong><?php echo e($application->HoTen); ?></strong>
                            <div class="muted top-gap-sm"><?php echo e($application->Email ?: 'Chưa có email'); ?> | <?php echo e($application->DienThoai ?: 'Chưa có số điện thoại'); ?></div>
                        </td>
                        <td><?php echo e($application->DiemCV); ?>/10</td>
                        <td><?php echo e($application->NgayNop); ?></td>
                        <td class="min-col-240">
                            <?php if(in_array('capnhat_trangthai', session('quyen', []), true)): ?>
                                <form method="post" action="<?php echo e(route('tuyendung.hoso.status', ['application' => $application->MaHS])); ?>" class="review-action-form">
                                    <?php echo csrf_field(); ?>
                                    <select name="TrangThai">
                                        <?php $__currentLoopData = ['Nộp hồ sơ', 'Sàng lọc', 'Phỏng vấn', 'Offer', 'Nhận việc', 'Rớt']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($status); ?>" <?php if($application->TrangThai === $status): echo 'selected'; endif; ?>><?php echo e($status); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                        <textarea name="GhiChu" placeholder="Ghi chú"><?php echo e($application->GhiChu); ?></textarea>
                                        <button class="btn" type="submit">Cập nhật</button>
                                </form>
                            <?php else: ?>
                                <?php echo e($application->TrangThai); ?>

                            <?php endif; ?>
                        </td>
                        <td><?php echo e($application->SoLichPhongVan); ?></td>
                        <td class="nowrap-cell">
                            <?php if(in_array('xem_lich_phong_van', session('quyen', []), true)): ?>
                                <a class="btn btn-secondary" href="<?php echo e(route('tuyendung.hoso.phongvan', ['application' => $application->MaHS])); ?>">Phỏng vấn</a>
                            <?php endif; ?>
                            <?php if(!empty($application->FileCV)): ?>
                                <a class="btn" href="<?php echo e(route('legacy.upload', ['path' => 'cv/' . ltrim((string) $application->FileCV, '/')])); ?>" target="_blank">CV</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="7" class="muted">Không có hồ sơ ứng tuyển trong đợt này.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="top-gap-lg"><?php echo e($applications->links()); ?></div>
    </section>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\du_an2\laravel_app\resources\views/tuyendung/applications.blade.php ENDPATH**/ ?>