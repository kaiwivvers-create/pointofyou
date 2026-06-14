<?php
    $settings = \App\Models\BrandSettings::getSettings();
    $favicon = $settings->logo ? asset('app-storage/' . $settings->logo) : asset('favicon.ico');
?>
<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $__env->yieldContent('title', $settings->app_name); ?></title>
    <link rel="icon" type="image/x-icon" href="<?php echo e($favicon); ?>">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=fredoka:400,500,600,700|nunito:400,500,600,700" rel="stylesheet">
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
</head>
<body class="font-sans antialiased text-stone-800 min-h-screen <?php echo $__env->yieldContent('body-class'); ?>" style="background-color: <?php echo e($settings->secondary_color); ?>;">
    <?php echo $__env->yieldContent('content'); ?>
    <?php echo $__env->make('partials.translator', ['isFloating' => true], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</body>
</html>
<?php /**PATH C:\Users\cindy\OneDrive\Documents\tick B\pplg\writing hting\pointofyou\pointofyou\resources\views/layouts/bakery.blade.php ENDPATH**/ ?>