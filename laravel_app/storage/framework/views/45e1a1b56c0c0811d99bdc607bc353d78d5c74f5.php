<?php
    $title = 'Phỏng vấn và đánh giá';
    $subtitle = 'Quản lý lịch phỏng vấn và nhận xét ứng viên trong hệ thống';
?>


<?php $__env->startSection('content'); ?>
    <section class="panel">
        <div class="toolbar toolbar-start">
            <div>
                <div><strong><?php echo e($application['HoTen']); ?></strong> - <?php echo e($application['TenDotTuyenDung']); ?></div>
                <div class="muted top-gap-sm">MãHS: <?php echo e($application['MaHS']); ?> | Trạng thái: <?php echo e($application['TrangThai']); ?> | Điểm CV: <?php echo e($application['DiemCV']); ?>/10</div>
            </div>
            <div class="button-row spaced">
                <a class="btn btn-secondary" href="<?php echo e(route('tuyendung.hoso.index', ['recruitment' => $application['MaDTD']])); ?>">Về hồ sơ</a>
                <?php if(!empty($application['FileCV'])): ?>
                    <a class="btn" href="<?php echo e(route('legacy.upload', ['path' => 'cv/' . ltrim((string) $application['FileCV'], '/')])); ?>" target="_blank">Mở CV</a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="split-two">
        <div class="panel">
            <h3 class="no-top-margin">Thêm lịch phỏng vấn</h3>
            <form method="post" action="<?php echo e(route('tuyendung.hoso.phongvan.store', ['application' => $application['MaHS']])); ?>">
                <?php echo csrf_field(); ?>
                <div class="field-stack">
                    <div><label for="NgayPhongVan">Ngày phỏng vấn</label><input id="NgayPhongVan" name="NgayPhongVan" type="date" required></div>
                    <div><label for="GioPhongVan">Giờ phỏng vấn</label><input id="GioPhongVan" name="GioPhongVan" type="time" required></div>
                    <div><label for="DiaDiem">Địa điểm</label><input id="DiaDiem" name="DiaDiem"></div>
                    <div><label for="GhiChu">Ghi chú</label><textarea id="GhiChu" name="GhiChu"></textarea></div>
                    <div><label for="KetQua">Kết quả</label><input id="KetQua" name="KetQua"></div>
                    <button class="btn" type="submit">Thêm lịch</button>
                </div>
            </form>
        </div>

        <div class="panel">
            <h3 class="no-top-margin">Thêm đánh giá</h3>
            <form method="post" action="<?php echo e(route('tuyendung.hoso.danhgia.store', ['application' => $application['MaHS']])); ?>">
                <?php echo csrf_field(); ?>
                <div class="field-stack">
                    <div><label for="DiemKyNang">Kỹ năng</label><input id="DiemKyNang" name="DiemKyNang" type="number" min="1" max="10" required></div>
                    <div><label for="DiemKinhNghiem">Kinh nghiệm</label><input id="DiemKinhNghiem" name="DiemKinhNghiem" type="number" min="1" max="10" required></div>
                    <div><label for="DiemThaiDo">Thái độ</label><input id="DiemThaiDo" name="DiemThaiDo" type="number" min="1" max="10" required></div>
                    <div><label for="NhanXet">Nhận xét</label><textarea id="NhanXet" name="NhanXet"></textarea></div>
                    <button class="btn" type="submit">Lưu đánh giá</button>
                </div>
            </form>
        </div>
    </section>

    <section class="split-two">
        <div class="panel">
            <h3 class="no-top-margin">Danh sách lịch phỏng vấn</h3>
            <div class="stack-list">
                <?php $__empty_1 = true; $__currentLoopData = $interviews; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $interview): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="stack-card-soft">
                        <div><strong><?php echo e($interview->NgayPhongVan); ?></strong> lúc <?php echo e($interview->GioPhongVan); ?></div>
                        <div class="muted top-gap-sm"><?php echo e($interview->DiaDiem ?: 'Chưa có địa điểm'); ?></div>
                        <?php if(!empty($interview->GhiChu)): ?>
                            <div class="muted top-gap-sm"><?php echo e($interview->GhiChu); ?></div>
                        <?php endif; ?>
                        <?php if(!empty($interview->KetQua)): ?>
                            <div class="top-gap-sm"><strong>Kết quả:</strong> <?php echo e($interview->KetQua); ?></div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="muted">Chưa có lịch phỏng vấn.</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="panel">
            <h3 class="no-top-margin">Đánh giá đã lưu</h3>
            <div class="stack-list">
                <?php $__empty_1 = true; $__currentLoopData = $reviews; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $review): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $average = ($review->DiemKyNang + $review->DiemKinhNghiem + $review->DiemThaiDo) / 3;
                    ?>
                    <div class="stack-card-soft">
                        <div><strong>Điểm TB:</strong> <?php echo e(number_format($average, 1)); ?>/10</div>
                        <div class="muted top-gap-sm">Kỹ năng: <?php echo e($review->DiemKyNang); ?> | Kinh nghiệm: <?php echo e($review->DiemKinhNghiem); ?> | Thái độ: <?php echo e($review->DiemThaiDo); ?></div>
                        <?php if(!empty($review->NhanXet)): ?>
                            <div class="top-gap-sm"><?php echo e($review->NhanXet); ?></div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="muted">Chưa có đánh giá phỏng vấn.</div>
                <?php endif; ?>
            </div>
        </div>
    </section>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\du_an2\laravel_app\resources\views/tuyendung/interviews.blade.php ENDPATH**/ ?>