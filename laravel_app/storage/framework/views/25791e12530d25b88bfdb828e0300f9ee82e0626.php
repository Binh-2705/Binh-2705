<?php $title = 'Nhân viên' ?>
<?php $subtitle = 'Danh sách và quản trị nhân sự' ?>
<?php $canCreate = in_array('them_nhanvien', session('quyen', []), true) ?>
<?php $canEdit = in_array('sua_nhanvien', session('quyen', []), true) ?>
<?php $canDelete = in_array('xoa_nhanvien', session('quyen', []), true) ?>


<?php $__env->startSection('content'); ?>
    <?php if($errors->any()): ?>
        <div class="flash-alert error">
            <?php echo e($errors->first('form') ?: $errors->first()); ?>

        </div>
    <?php endif; ?>

    <section class="panel">
        <form method="get" action="<?php echo e(route('nhanvien.index')); ?>">
            <div class="field-grid">
                <div>
                    <label for="q">Tìm kiếm nhân viên</label>
                    <input id="q" type="text" name="q" value="<?php echo e($filters['q'] ?? ''); ?>" placeholder="Nhập tên, mã, email hoặc điện thoại...">
                </div>
                <div>
                    <label for="status">Trạng thái</label>
                    <select id="status" name="status">
                        <option value="">Tất cả trạng thái</option>
                        <option value="Đang làm" <?php if(($filters['status'] ?? '') === 'Đang làm'): echo 'selected'; endif; ?>>Đang làm</option>
                        <option value="Nghỉ" <?php if(($filters['status'] ?? '') === 'Nghỉ'): echo 'selected'; endif; ?>>Nghỉ</option>
                    </select>
                </div>
                <div>
                    <label for="department">Phòng ban</label>
                    <select id="department" name="department">
                        <option value="">Tất cả phòng ban</option>
                        <?php $__currentLoopData = $options['departments']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $department): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($department->MaPB); ?>" <?php if((string) ($filters['department'] ?? '') === (string) $department->MaPB): echo 'selected'; endif; ?>><?php echo e($department->TenPB); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="full-span button-row">
                    <button type="submit" class="btn">Lọc danh sách</button>
                    <?php if($canCreate): ?>
                        <a href="<?php echo e(route('nhanvien.create')); ?>" class="btn btn-secondary">Thêm nhân viên</a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </section>

    <section class="panel">
        <div class="table-shell">
            <table class="data-table table-compact">
                <thead>
                    <tr>
                        <th class="nowrap-cell">STT</th>
                        <th>Mã NV</th>
                        <th>Họ tên</th>
                        <th>Giới tính</th>
                        <th>Ngày sinh</th>
                        <th>Email</th>
                        <th>Điện thoại</th>
                        <th>Bậc lương</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><strong><?php echo e(($employees->firstItem() ?? 1) + $loop->index); ?></strong></td>
                            <td><?php echo e(data_get($employee, 'MaNV')); ?></td>
                            <td>
                                <strong><?php echo e(data_get($employee, 'HoTen')); ?></strong>
                                <div class="muted"><?php echo e(data_get($employee, 'TenPB') ?: 'Chưa gán phòng ban'); ?></div>
                            </td>
                            <td><?php echo e(data_get($employee, 'GioiTinh') ?: 'Chưa nhập'); ?></td>
                            <td><?php echo e(data_get($employee, 'NgaySinh') ? \Illuminate\Support\Carbon::parse(data_get($employee, 'NgaySinh'))->format('d/m/Y') : 'Chưa nhập'); ?></td>
                            <td><?php echo e(data_get($employee, 'Email') ?: 'Chưa nhập'); ?></td>
                            <td><?php echo e(data_get($employee, 'DienThoai') ?: 'Chưa nhập'); ?></td>
                            <td><?php echo e(data_get($employee, 'TenBac') ?: 'Chưa có'); ?></td>
                            <td><?php echo e(data_get($employee, 'TrangThai')); ?></td>
                            <td>
                                <div class="button-row">
                                    <?php if($canEdit): ?>
                                        <a href="<?php echo e(route('nhanvien.edit', ['employee' => data_get($employee, 'MaNV')])); ?>" class="btn btn-secondary">Sửa</a>
                                    <?php endif; ?>
                                    <?php if($canDelete): ?>
                                        <form method="post" action="<?php echo e(route('nhanvien.destroy', ['employee' => data_get($employee, 'MaNV')])); ?>" class="inline-form" onsubmit="return confirm('Bạn có chắc muốn xóa nhân viên này?');">
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
                            <td colspan="10" class="muted">Không có nhân viên.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if($employees->lastPage() > 1): ?>
            <div class="top-gap-lg"><?php echo e($employees->links()); ?></div>
        <?php endif; ?>
    </section>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\du_an2\laravel_app\resources\views/nhanvien/index.blade.php ENDPATH**/ ?>