<?php $title = $moduleConfig['title'] ?>
<?php $subtitle = $moduleConfig['subtitle'] ?>


<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('resource_modules.partials.index_content', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\du_an2\laravel_app\resources\views/nghiphep/index.blade.php ENDPATH**/ ?>