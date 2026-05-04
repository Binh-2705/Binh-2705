<?php $title = 'Bang dich vu' ?>
<?php $subtitle = 'Theo doi va quan tri cac dich vu co so du lieu da duoc ket noi' ?>


<?php $__env->startSection('content'); ?>
    <section class="panel">
        <div class="toolbar">
            <div>
                <h2 class="no-top-margin">Danh muc dich vu</h2>
                <p class="page-note">Moi dich vu duoc map den dung ket noi va tai nguyen, phuc vu giao dien va API tuong thich.</p>
            </div>
            <div class="inline-actions">
                <a class="btn btn-secondary" href="<?php echo e(route('dashboard')); ?>">Ve bang dieu khien</a>
            </div>
        </div>
    </section>

    <section class="console-grid">
        <?php $__currentLoopData = $services; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $serviceName => $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <article class="panel console-card">
                <span class="eyebrow"><?php echo e($serviceName); ?></span>
                <h3 class="no-top-margin"><?php echo e($serviceName); ?></h3>
                <div class="page-note">Ket noi: <strong><?php echo e($service['connection']); ?></strong></div>
                <div class="chip-list top-gap-lg">
                    <?php $__currentLoopData = $service['resources']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $resource): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a class="chip-link" href="<?php echo e(route('services.show', ['service' => $serviceName, 'resource' => $resource])); ?>"><?php echo e($resource); ?></a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </article>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </section>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\du_an2\laravel_app\resources\views/services/index.blade.php ENDPATH**/ ?>