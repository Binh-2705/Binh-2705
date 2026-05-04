<?php $fileFields = $moduleConfig['file_fields'] ?? []; ?>
<section class="panel">
    <form method="post" action="<?php echo e($mode === 'create' ? route(($routeKey ?? $moduleKey) . '.store') : route(($routeKey ?? $moduleKey) . '.update', ['record' => $recordId])); ?>" <?php echo e(count($fileFields) ? 'enctype="multipart/form-data"' : ''); ?>>
        <?php echo csrf_field(); ?>
        <?php if($mode === 'edit'): ?>
            <?php echo method_field('PUT'); ?>
        <?php endif; ?>

        <?php $primaryKeys = is_array($resourceConfig['primary_key']) ? $resourceConfig['primary_key'] : [$resourceConfig['primary_key']] ?>
        <div class="field-grid">
            <?php $__currentLoopData = $resourceConfig['columns']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $column): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $field = $column['field'];
                    $isPrimaryKey = in_array($field, $primaryKeys, true);
                    $isAutoIncrement = str_contains($column['extra'], 'auto_increment');
                    $shouldDisable = ($mode === 'edit' && $isPrimaryKey) || $isAutoIncrement;
                    $value = old($field, data_get($record, $field, $column['default']));
                    $isTextarea = str_contains($column['type'], 'text');
                    $inputType = 'text';
                    if (str_contains($column['type'], 'date') && !str_contains($column['type'], 'datetime')) {
                        $inputType = 'date';
                    } elseif (str_contains($column['type'], 'time')) {
                        $inputType = 'time';
                    } elseif (str_contains($column['type'], 'int') || str_contains($column['type'], 'decimal') || str_contains($column['type'], 'float') || str_contains($column['type'], 'double')) {
                        $inputType = 'number';
                    }
                ?>
<div class="<?php echo e($isTextarea ? 'full-span' : ''); ?>">
                    <label for="<?php echo e($field); ?>"><?php echo e($field); ?></label>
                    <?php if(in_array($field, $fileFields, true)): ?>
                        <?php if($value): ?>
                            <div class="top-gap-sm" style="margin-bottom:6px">
                                <img src="<?php echo e(route('legacy.upload', ['path' => 'photos/' . $value])); ?>" alt="Ảnh hiện tại" style="max-height:80px;border-radius:4px;border:1px solid #e5e7eb;">
                            </div>
                        <?php endif; ?>
                        <input id="<?php echo e($field); ?>" name="<?php echo e($field); ?>" type="file" accept="image/*" <?php echo e($shouldDisable ? 'disabled' : ''); ?>>
                    <?php elseif($isTextarea): ?>
                        <textarea id="<?php echo e($field); ?>" name="<?php echo e($field); ?>" <?php echo e($shouldDisable ? 'disabled' : ''); ?>><?php echo e($value); ?></textarea>
                    <?php else: ?>
                        <input id="<?php echo e($field); ?>" name="<?php echo e($field); ?>" type="<?php echo e($inputType); ?>" value="<?php echo e($value); ?>" <?php echo e($shouldDisable ? 'disabled' : ''); ?> <?php echo e(!$column['nullable'] && !$shouldDisable ? 'required' : ''); ?>>
                    <?php endif; ?>
                    <div class="muted top-gap-sm"><?php echo e($column['type']); ?><?php echo e($column['extra'] ? ' | ' . $column['extra'] : ''); ?></div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <div class="form-actions-bar">
            <button class="btn" type="submit"><?php echo e($mode === 'create' ? 'Tạo bản ghi' : 'Cập nhật bản ghi'); ?></button>
            <a class="btn btn-secondary" href="<?php echo e(route(($routeKey ?? $moduleKey) . '.index')); ?>">Về danh sách</a>
        </div>
    </form>
</section>
<?php /**PATH C:\xampp\htdocs\du_an2\laravel_app\resources\views/resource_modules/partials/form_content.blade.php ENDPATH**/ ?>