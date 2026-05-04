<?php
    $title = 'Tuyển dụng';
    $subtitle = 'Quản trị đợt tuyển dụng trên hệ thống';
?>


<?php $__env->startSection('content'); ?>
    <section class="panel">
        <div class="toolbar toolbar-start" style="margin-bottom:12px;">
            <?php if(in_array('them_dot_tuyen', session('quyen', []), true)): ?>
                <a class="btn" href="<?php echo e(route('tuyendung.create')); ?>">+ Thêm đợt tuyển</a>
            <?php endif; ?>
            <?php if(in_array('xem_ung_vien', session('quyen', []), true)): ?>
                <a class="btn btn-secondary" href="<?php echo e(route('tuyendung.ungvien.index')); ?>">👤 Quản lý ứng viên</a>
            <?php endif; ?>
        </div>
        <form method="get" class="filter-grid">
            <div>
                <label for="q" class="wide-search-label">Tìm kiếm</label>
                <input id="q" name="q" value="<?php echo e($filters['q'] ?? ''); ?>" placeholder="Tên đợt hoặc vị trí">
            </div>
            <div>
                <label for="status" class="wide-search-label">Trạng thái</label>
                <select id="status" name="status">
                    <option value="">Tất cả</option>
                    <option value="Đang tuyển" <?php if(($filters['status'] ?? '') === 'Đang tuyển'): echo 'selected'; endif; ?>>Đang tuyển</option>
                    <option value="Đã kết thúc" <?php if(($filters['status'] ?? '') === 'Đã kết thúc'): echo 'selected'; endif; ?>>Đã kết thúc</option>
                </select>
            </div>
            <div class="button-row">
                <button class="btn" type="submit">Lọc</button>
                <?php if(in_array('them_dot_tuyen', session('quyen', []), true)): ?>
                    <a class="btn btn-secondary" href="<?php echo e(route('tuyendung.create')); ?>">Thêm mới</a>
                <?php endif; ?>
            </div>
        </form>
    </section>

    <section class="panel">
        <div class="table-shell">
            <table class="data-table table-compact">
                <thead><tr>
                    <th>MãDTD</th>
                    <th>Tên đợt</th>
                    <th>Vị trí</th>
                    <th>Số lượng</th>
                    <th>Hồ sơ</th>
                    <th>Trạng thái</th>
                    <th>Thao tác</th>
                </tr></thead>
                <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $campaigns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $campaign): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e($campaign->MaDTD); ?></td>
                        <td><strong><?php echo e($campaign->TenDotTuyenDung); ?></strong></td>
                        <td><?php echo e($campaign->ViTriTuyenDung); ?></td>
                        <td><?php echo e($campaign->SoLuong); ?></td>
                        <td><?php echo e($campaign->SoHoSo); ?></td>
                        <td><?php echo e($campaign->TrangThai); ?></td>
                        <td>
                            <div class="button-row">
                            <?php if(in_array('xem_ho_so', session('quyen', []), true)): ?>
                                <a class="btn" href="<?php echo e(route('tuyendung.hoso.index', ['recruitment' => $campaign->MaDTD])); ?>">Hồ sơ</a>
                            <?php endif; ?>
                            <?php if(in_array('them_dot_tuyen', session('quyen', []), true)): ?>
                                <a class="btn btn-secondary" href="<?php echo e(route('tuyendung.edit', ['recruitment' => $campaign->MaDTD])); ?>">Sửa</a>
                            <?php endif; ?>
                            <?php if(in_array('xoa_dot_tuyen', session('quyen', []), true)): ?>
                                <form method="post" action="<?php echo e(route('tuyendung.destroy', ['recruitment' => $campaign->MaDTD])); ?>" class="inline-form" onsubmit="return confirm('Xóa đợt tuyển dụng này?');">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button class="btn btn-danger" type="submit">Xóa</button>
                                </form>
                            <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="7" class="muted">Không có dữ liệu tuyển dụng.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="top-gap-lg"><?php echo e($campaigns->links()); ?></div>
    </section>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\du_an2\laravel_app\resources\views/tuyendung/index.blade.php ENDPATH**/ ?>