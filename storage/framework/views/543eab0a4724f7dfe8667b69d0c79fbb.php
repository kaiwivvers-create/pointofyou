<?php
    $settings = \App\Models\BrandSettings::getSettings();
?>


<?php $__env->startSection('title', 'Staff Login — ' . ($settings->app_name ?? 'Golden Crumb')); ?>

<?php $__env->startSection('body-class', 'flex flex-col'); ?>

<?php $__env->startSection('content'); ?>
    <style>
        .focus-primary:focus {
            border-color: <?php echo e($settings->primary_color); ?> !important;
            --tw-ring-color: <?php echo e($settings->primary_color); ?>33 !important;
        }
    </style>

    <div class="flex-1 flex items-center justify-center px-4 py-12 sm:py-16">
        <div class="w-full max-w-md">
            <div class="text-center mb-8">
                <a href="<?php echo e(url('/')); ?>" class="inline-flex items-center gap-2 hover:opacity-80 transition-opacity">
                    <?php if($settings->logo): ?>
                        <img src="<?php echo e(asset('app-storage/' . $settings->logo)); ?>" alt="<?php echo e($settings->app_name); ?>" class="w-10 h-10 object-cover rounded-lg">
                    <?php else: ?>
                        <div class="flex w-10 h-10 items-center justify-center rounded-lg text-lg font-semibold" style="background-color: <?php echo e($settings->primary_color); ?>30; color: <?php echo e($settings->primary_font_color); ?>;">
                            <?php echo e($settings->logo_fallback); ?>

                        </div>
                    <?php endif; ?>
                    <span class="font-display text-2xl font-medium" style="color: <?php echo e($settings->primary_font_color); ?>;"><?php echo e($settings->app_name); ?></span>
                </a>
                <p class="mt-3 text-sm text-stone-500">Staff portal</p>
            </div>

            <div class="rounded-3xl bg-white p-8 sm:p-10 shadow-lg shadow-amber-900/5 ring-1 ring-amber-100">
                <h1 class="font-display text-2xl font-semibold text-stone-900 text-center mb-2">Welcome back</h1>
                <p class="text-stone-500 text-center text-sm mb-8">Sign in to manage the bakery</p>

                <?php if (isset($component)) { $__componentOriginal5168fdb0c14fd91c6598264bc4be63f2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5168fdb0c14fd91c6598264bc4be63f2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.flash','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flash'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5168fdb0c14fd91c6598264bc4be63f2)): ?>
<?php $attributes = $__attributesOriginal5168fdb0c14fd91c6598264bc4be63f2; ?>
<?php unset($__attributesOriginal5168fdb0c14fd91c6598264bc4be63f2); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5168fdb0c14fd91c6598264bc4be63f2)): ?>
<?php $component = $__componentOriginal5168fdb0c14fd91c6598264bc4be63f2; ?>
<?php unset($__componentOriginal5168fdb0c14fd91c6598264bc4be63f2); ?>
<?php endif; ?>

                <form method="POST" action="<?php echo e(route('admin.login.store')); ?>" class="space-y-5">
                    <?php echo csrf_field(); ?>

                    <div>
                        <label for="email" class="block text-sm font-medium text-stone-700 mb-1.5">Email</label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="<?php echo e(old('email')); ?>"
                            required
                            autofocus
                            autocomplete="email"
                            maxlength="255"
                            class="w-full rounded-xl border px-4 py-3 text-stone-800 placeholder:text-stone-400 focus:ring-2 outline-none transition-shadow focus-primary"
                            style="border-color: <?php echo e($settings->primary_color); ?>40; background-color: <?php echo e($settings->secondary_color); ?>40;"
                            placeholder="you@example.com"
                        >
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-stone-700 mb-1.5">Password</label>
                        <input
                            id="password"
                            type="password"
                            name="password"
                            required
                            autocomplete="current-password"
                            maxlength="255"
                            class="w-full rounded-xl border px-4 py-3 text-stone-800 placeholder:text-stone-400 focus:ring-2 outline-none transition-shadow focus-primary"
                            style="border-color: <?php echo e($settings->primary_color); ?>40; background-color: <?php echo e($settings->secondary_color); ?>40;"
                            placeholder="••••••••"
                        >
                    </div>

                    <label class="flex items-center gap-2.5 cursor-pointer select-none">
                        <input
                            type="checkbox"
                            name="remember"
                            value="1"
                            class="size-4 rounded text-stone-800 focus:ring-amber-300"
                            <?php if(old('remember')): echo 'checked'; endif; ?>
                        >
                        <span class="text-sm text-stone-600">Remember me</span>
                    </label>

                    <button
                        type="submit"
                        class="w-full rounded-full py-3.5 text-sm font-semibold transition-colors"
                        style="background-color: <?php echo e($settings->primary_color); ?>; color: <?php echo e($settings->primary_font_color); ?>; filter: brightness(1);"
                        onmouseenter="this.style.filter='brightness(0.95)'"
                        onmouseleave="this.style.filter='brightness(1)'"
                    >
                        Sign in
                    </button>
                </form>
            </div>

            <p class="mt-8 text-center">
                <a href="<?php echo e(url('/')); ?>" class="text-sm text-stone-500 hover:opacity-80 transition-colors" style="color: <?php echo e($settings->primary_color); ?>;">
                    ← Back to bakery
                </a>
            </p>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.bakery', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\cindy\OneDrive\Documents\tick B\pplg\writing hting\pointofyou\pointofyou\resources\views/admin/login.blade.php ENDPATH**/ ?>