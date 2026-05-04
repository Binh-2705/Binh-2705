<?php $title = 'Giám sát chatbot' ?>
<?php $subtitle = 'Theo dõi phiên sử dụng chatbot, tin nhắn và action draft' ?>


<?php $__env->startSection('content'); ?>
<section class="panel">
    <div class="table-shell">
        <table class="data-table table-compact">
            <thead>
                <tr>
                    <th>Session</th>
                    <th>Người dùng</th>
                    <th>Vai trò</th>
                    <th>Tin nhắn</th>
                    <th>Draft</th>
                    <th>Hoạt động cuối</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $sessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $session): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e($session->id); ?></td>
                        <td>
                            <div><strong><?php echo e($session->username); ?></strong></div>
                            <div class="muted"><?php echo e($session->session_key); ?></div>
                        </td>
                        <td><?php echo e($session->role_name); ?></td>
                        <td><?php echo e($session->MessageCount); ?></td>
                        <td><?php echo e($session->DraftCount); ?></td>
                        <td><?php echo e($session->last_interaction_at); ?></td>
                        <td><a class="btn btn-secondary" href="<?php echo e(route('chatbot.show', ['session' => $session->id])); ?>">Xem chi tiết</a></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="7" class="muted">Không có dữ liệu chatbot.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="top-gap-lg"><?php echo e($sessions->links()); ?></div>
</section>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\du_an2\laravel_app\resources\views/chatbot/index.blade.php ENDPATH**/ ?>