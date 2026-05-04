<?php $title = 'Tra cứu hệ thống' ?>
<?php $subtitle = 'Tìm nhanh trên các phân hệ đã đưa về hệ thống hiện tại' ?>


<?php $__env->startSection('content'); ?>
    <section class="panel">
        <form method="get" class="filter-grid single-wide">
            <div>
                <label for="q" class="wide-search-label">Từ khóa</label>
                <input id="q" name="q" value="<?php echo e($keyword); ?>" placeholder="Họ tên, phòng ban, đợt tuyển, báo cáo...">
            </div>
            <div><button class="btn" type="submit">Tìm</button></div>
        </form>
    </section>

    <?php $__currentLoopData = $results; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section => $items): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <section class="panel">
            <h2 class="no-top-margin"><?php echo e(str_replace('-', ' ', $section)); ?></h2>
            <?php if($items->isEmpty()): ?>
                <div class="muted">Không có kết quả.</div>
            <?php else: ?>
                <div class="table-shell">
                    <table class="data-table table-compact">
                        <tbody>
                            <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <?php $__currentLoopData = (array) $item; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <td><?php echo e($value); ?></td>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\du_an2\laravel_app\resources\views/search/index.blade.php ENDPATH**/ ?>