<?php
    $title = 'Báo cáo';
    $subtitle = 'Quản trị danh mục báo cáo trên hệ thống';
?>


<?php $__env->startSection('content'); ?>
<section class="panel">
    <form method="get" class="filter-grid">
        <div>
            <label for="q" class="wide-search-label">Tìm kiếm</label>
            <input id="q" name="q" value="<?php echo e($filters['q'] ?? ''); ?>" placeholder="Tên báo cáo hoặc người tạo">
        </div>
        <div>
            <label for="type" class="wide-search-label">Loại báo cáo</label>
            <select id="type" name="type">
                <option value="">Tất cả</option>
                <?php $__currentLoopData = ['Nhân sự','Chấm công','Nghỉ phép','Hợp đồng','Lương']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($type); ?>" <?php if(($filters['type'] ?? '') === $type): echo 'selected'; endif; ?>><?php echo e($type); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div class="button-row">
            <button class="btn" type="submit">Lọc</button>
            <?php if(in_array('them_baocao', session('quyen', []), true)): ?>
                <a class="btn btn-secondary" href="<?php echo e(route('baocao.create')); ?>">Thêm mới</a>
            <?php endif; ?>
            <?php if(in_array('xuatex_baocao', session('quyen', []), true)): ?>
                <a class="btn btn-secondary" href="<?php echo e(route('baocao.export-excel', request()->only(['q', 'type']))); ?>">Xuất Excel</a>
                <a class="btn btn-secondary" href="<?php echo e(route('baocao.export-json', request()->only(['q', 'type']))); ?>">Xuất JSON</a>
            <?php endif; ?>
        </div>
    </form>
</section>

<section class="panel">
    <div class="table-shell">
        <table class="data-table table-compact">
            <thead>
                <tr>
                    <th>MãBC</th>
                    <th>Tên báo cáo</th>
                    <th>Loại</th>
                    <th>Người tạo</th>
                    <th>Thời điểm tạo</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $reports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $report): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e($report->MaBC); ?></td>
                        <td><strong><?php echo e($report->TenBaoCao); ?></strong></td>
                        <td><?php echo e($report->LoaiBaoCao); ?></td>
                        <td><?php echo e($report->NguoiTao ?: 'system'); ?></td>
                        <td><?php echo e($report->ThoiDiemTao); ?></td>
                        <td>
                            <div class="button-row">
                                <?php if(in_array('sua_baocao', session('quyen', []), true) || in_array('them_baocao', session('quyen', []), true)): ?>
                                    <a class="btn btn-secondary" href="<?php echo e(route('baocao.edit', ['report' => $report->MaBC])); ?>">Sửa</a>
                                <?php endif; ?>
                                <?php if(in_array('xoa_baocao', session('quyen', []), true)): ?>
                                    <form method="post" action="<?php echo e(route('baocao.destroy', ['report' => $report->MaBC])); ?>" class="inline-form" onsubmit="return confirm('Xóa báo cáo này?');">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button class="btn btn-danger" type="submit">Xóa</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="6" class="muted">Không có dữ liệu báo cáo.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="top-gap-lg"><?php echo e($reports->links()); ?></div>
</section>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\du_an2\laravel_app\resources\views/baocao/index.blade.php ENDPATH**/ ?>