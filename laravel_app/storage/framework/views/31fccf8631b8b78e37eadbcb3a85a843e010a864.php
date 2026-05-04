<?php $title = 'Chấm công' ?>
<?php $subtitle = 'Quản trị bảng chấm công' ?>
<?php $canCreate = in_array('them_chamcong', session('quyen', []), true) ?>
<?php $canEdit = in_array('sua_chamcong', session('quyen', []), true) ?>
<?php $canDelete = in_array('xoa_chamcong', session('quyen', []), true) ?>
<?php $canExport = in_array('xuat_bang_cham_cong', session('quyen', []), true) ?>
<?php $isSelfView = $isSelfView ?? false ?>


<?php $__env->startSection('content'); ?>
    <section class="panel">
        <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px; margin-bottom:12px">
            <div></div>
            <div style="display:flex;gap:6px">
                <a href="<?php echo e(route('chamcong.index')); ?>"
                   style="padding:5px 14px;border-radius:6px;font-size:0.82rem;border:1px solid #3b4cb8;background:#3b4cb8;color:#fff;text-decoration:none">Danh sách</a>
                <a href="<?php echo e(route('chamcong.matrix', ['thang' => now()->month, 'nam' => now()->year])); ?>"
                   style="padding:5px 14px;border-radius:6px;font-size:0.82rem;border:1px solid #d1d5db;color:#374151;text-decoration:none">Bảng tháng</a>
            </div>
        </div>
        <?php if(!$isSelfView): ?>
        <form method="get" action="<?php echo e(route('chamcong.index')); ?>">
            <div class="field-grid">
                <div>
                    <label for="q">Nhân viên</label>
                    <input id="q" type="text" name="q" value="<?php echo e($filters['q'] ?? ''); ?>" placeholder="Tìm theo họ tên hoặc mã nhân viên">
                </div>
                <div>
                    <label for="status">Trạng thái</label>
                    <select id="status" name="status">
                        <option value="">Tất cả trạng thái</option>
                        <?php $__currentLoopData = ['Di lam' => 'Đi làm', 'Nghi phep' => 'Nghỉ phép', 'Nghi khong luong' => 'Nghỉ không lương', 'Cong tac' => 'Công tác', 'Le' => 'Lễ']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $statusValue => $statusLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($statusValue); ?>" <?php if(($filters['status'] ?? '') === $statusValue): echo 'selected'; endif; ?>><?php echo e($statusLabel); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div>
                    <label for="date">Ngày</label>
                    <input id="date" name="date" type="date" value="<?php echo e($filters['date'] ?? ''); ?>">
                </div>
                <div class="full-span button-row">
                    <button type="submit" class="btn">Xem chấm công</button>
                    <?php if($canCreate): ?>
                        <a href="<?php echo e(route('chamcong.create')); ?>" class="btn btn-secondary">Thêm chấm công</a>
                    <?php endif; ?>
                    <?php if($canExport): ?>
                        <a href="<?php echo e(route('chamcong.export-excel', ['thang' => request('thang', now()->month), 'nam' => request('nam', now()->year)])); ?>" class="btn btn-secondary">Xuất Excel</a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
        <?php endif; ?>
    </section>

    <section class="panel">
        <div class="table-shell">
            <table class="data-table table-compact">
                <thead>
                    <tr>
                        <th>Mã CC</th>
                        <th>Nhân viên</th>
                        <th>Ngày</th>
                        <th>Giờ vào</th>
                        <th>Giờ ra</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $records; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $record): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($record->MaCC); ?></td>
                            <td>
                                <strong><?php echo e($record->HoTen); ?></strong>
                                <div class="muted"><?php echo e($record->MaNV); ?><?php echo e($record->TenPB ? ' - ' . $record->TenPB : ''); ?></div>
                            </td>
                            <td><?php echo e($record->Ngay ? \Illuminate\Support\Carbon::parse($record->Ngay)->format('d/m/Y') : 'Chưa nhập'); ?></td>
                            <td><?php echo e($record->GioVao ?: '-'); ?></td>
                            <td><?php echo e($record->GioRa ?: '-'); ?></td>
                            <td><?php echo e($record->TrangThai); ?></td>
                            <td>
                                <div class="button-row">
                                    <?php if($canEdit): ?>
                                        <a href="<?php echo e(route('chamcong.edit', ['attendance' => $record->MaCC])); ?>" class="btn btn-secondary">Sửa</a>
                                    <?php endif; ?>
                                    <?php if($canDelete): ?>
                                        <form method="post" action="<?php echo e(route('chamcong.destroy', ['attendance' => $record->MaCC])); ?>" class="inline-form" onsubmit="return confirm('Bạn có chắc muốn xóa bản ghi chấm công này?');">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="btn btn-danger">Xóa</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" class="muted">Không có dữ liệu chấm công.</td>
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
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\du_an2\laravel_app\resources\views/chamcong/index.blade.php ENDPATH**/ ?>