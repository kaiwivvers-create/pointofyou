<?php
    $user = $user ?? null;
    $isEdit = $user !== null;
?>

<div class="space-y-5">
    <div>
        <label for="name" class="staff-label">Name</label>
        <input id="name" name="name" type="text" required maxlength="255" value="<?php echo e(old('name', $user?->name)); ?>" class="staff-input">
    </div>
    <div>
        <label for="email" class="staff-label">Email</label>
        <input id="email" name="email" type="email" required maxlength="255" value="<?php echo e(old('email', $user?->email)); ?>" class="staff-input">
    </div>
    <div>
        <label for="password" class="staff-label">
            <?php echo e($isEdit ? 'New password' : 'Password'); ?>

            <?php if($isEdit): ?>
                <span class="font-normal text-slate-500">(leave blank to keep current)</span>
            <?php endif; ?>
        </label>
        <input id="password" name="password" type="password" maxlength="255" <?php if(! $isEdit): ?> required <?php endif; ?> class="staff-input" autocomplete="new-password">
    </div>
    <?php if(! $isEdit): ?>
    <div>
        <label for="password_confirmation" class="staff-label">Confirm Password</label>
        <input id="password_confirmation" name="password_confirmation" type="password" required maxlength="255" class="staff-input" autocomplete="new-password">
    </div>
    <?php endif; ?>
    <div>
        <label for="role" class="staff-label">Role</label>
        <select id="role" name="role" required class="staff-input">
            <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($role->id); ?>" <?php if(old('role', $user?->role_id) == $role->id): echo 'selected'; endif; ?>><?php echo e($role->name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
</div>
<?php /**PATH C:\Users\cindy\OneDrive\Documents\tick B\pplg\writing hting\pointofyou\pointofyou\resources\views/super-admin/users/_form.blade.php ENDPATH**/ ?>