<?php
    $errorBags = session('errors') ? session('errors')->getBags() : [];
?>

<?php if(session('success')): ?>
    <div class="mb-6 rounded-xl bg-emerald-50 px-4 py-3 text-sm text-emerald-800 ring-1 ring-emerald-100" role="alert">
        <?php echo e(session('success')); ?>

    </div>
<?php endif; ?>

<?php if(session('error')): ?>
    <div class="mb-6 rounded-xl bg-red-50 px-4 py-3 text-sm text-red-800 ring-1 ring-red-100" role="alert">
        <?php echo e(session('error')); ?>

    </div>
<?php endif; ?>

<?php if(!empty($errorBags)): ?>
    <div class="mb-6 space-y-3">
        <?php $__currentLoopData = $errorBags; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bagName => $bag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php $__currentLoopData = $bag->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $message): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="rounded-xl bg-red-50 px-4 py-3 text-sm text-red-800 ring-1 ring-red-100" role="alert">
                    <?php echo e($message); ?>

                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
<?php endif; ?>
<?php /**PATH C:\Users\cindy\OneDrive\Documents\tick B\pplg\writing hting\pointofyou\pointofyou\resources\views/components/flash.blade.php ENDPATH**/ ?>