<?php
    $title = 'Danh sách ứng viên';
    $subtitle = 'Quản lý ứng viên, đánh giá CV và nộp hồ sơ theo đợt tuyển';
?>


<?php $__env->startSection('content'); ?>
    <?php
        $permissions = (array) session('quyen', []);
    ?>
    <section class="panel">
        <div class="button-row">
            <?php if(in_array('them_ung_vien', $permissions, true)): ?>
                <a class="btn" href="<?php echo e(route('tuyendung.ungvien.create')); ?>">+ Thêm ứng viên</a>
            <?php endif; ?>
            <a class="btn btn-secondary" href="<?php echo e(route('tuyendung.index')); ?>">Đợt tuyển</a>
        </div>
        <form method="get" class="top-gap-md">
            <div class="toolbar toolbar-start">
                <div>
                    <input id="q" name="q" value="<?php echo e($filters['q'] ?? ''); ?>" placeholder="Nhập tên, email hoặc số điện thoại" style="max-width:360px;">
                </div>
                <div class="button-row">
                    <button class="btn" type="submit">Tìm</button>
                </div>
            </div>
        </form>
    </section>

    <section class="panel">
        <div class="table-shell">
            <table class="data-table table-compact">
                <thead><tr>
                    <th>Mã</th>
                    <th>Họ tên</th>
                    <th>Ngày sinh</th>
                    <th>Email</th>
                    <th>Điện thoại</th>
                    <th>Trình độ</th>
                    <th>CV</th>
                    <th>Điểm CV</th>
                    <th>Trạng thái</th>
                    <th>Ứng tuyển</th>
                </tr></thead>
                <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $candidates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $candidate): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $candidateId = data_get($candidate, 'MaUV')
                            ?? data_get($candidate, 'ma_uv')
                            ?? data_get($candidate, 'id')
                            ?? data_get($candidate, 'MaUngVien');
                        $name = data_get($candidate, 'HoTen', 'N/A');
                        $birthDate = data_get($candidate, 'NgaySinh');
                        $email = data_get($candidate, 'Email');
                        $phone = data_get($candidate, 'DienThoai');
                        $degree = data_get($candidate, 'TrinhDo');
                        $cvFile = data_get($candidate, 'FileCV');
                        $score = (int) data_get($candidate, 'DiemCV', 0);
                        $candidateStatus = $score >= 8 ? 'Rất tiềm năng' : ($score >= 6 ? 'Khá' : 'Cần xem lại');
                        $scoreClass = $score >= 8 ? 'score-high' : ($score >= 6 ? 'score-mid' : 'score-low');
                    ?>
                    <tr <?php if($score >= 8): ?> style="background:#edf8ef;" <?php endif; ?>>
                        <td><?php echo e($candidateId); ?></td>
                        <td><?php echo e($name); ?></td>
                        <td><?php echo e($birthDate ?: 'N/A'); ?></td>
                        <td><?php echo e($email ?: 'Chưa có email'); ?></td>
                        <td><?php echo e($phone ?: 'Chưa có số điện thoại'); ?></td>
                        <td><?php echo e($degree ?: 'Chưa cập nhật'); ?></td>
                        <td>
                            <?php if(!empty($cvFile)): ?>
                                <a class="btn btn-secondary" href="<?php echo e(route('legacy.upload', ['path' => 'cv/' . ltrim((string) $cvFile, '/')])); ?>" target="_blank">Xem CV</a>
                            <?php else: ?>
                                <span class="muted">Chưa có CV</span>
                            <?php endif; ?>
                        </td>
                        <td><strong class="<?php echo e($scoreClass ?? ''); ?>"><?php echo e($score ?? 0); ?></strong></td>
                        <td><strong class="<?php echo e($scoreClass ?? ''); ?>"><?php echo e($candidateStatus ?? ''); ?></strong></td>
                        <td class="nowrap-cell">
                            <?php if(in_array('them_ho_so', $permissions, true) && !empty($candidateId)): ?>
                                <a class="btn" href="<?php echo e(route('tuyendung.ungvien.apply', ['candidate' => $candidateId])); ?>">Nộp hồ sơ</a>
                            <?php elseif(in_array('them_ho_so', $permissions, true)): ?>
                                <span class="muted">Thiếu mã ứng viên</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="10" class="muted">Không có ứng viên phù hợp.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="top-gap-lg"><?php echo e($candidates->links()); ?></div>
    </section>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\du_an2\laravel_app\resources\views/tuyendung/candidates.blade.php ENDPATH**/ ?>