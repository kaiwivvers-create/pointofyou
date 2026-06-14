<?php
    $active = $active ?? false;
    $brandSettings = \App\Models\BrandSettings::getSettings();
?>

<a
    href="<?php echo e($href); ?>"
    class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-all duration-200"
    style="
        <?php if($active): ?>
            background-color: <?php echo e($brandSettings->primary_color); ?>22;
            color: <?php echo e($brandSettings->primary_font_color); ?>;
        <?php else: ?>
            color: <?php echo e($brandSettings->primary_font_color); ?>cc;
        <?php endif; ?>
    "
    onmouseenter="this.style.backgroundColor='<?php echo e($brandSettings->primary_color); ?>18'; this.style.color='<?php echo e($brandSettings->primary_font_color); ?>';"
    onmouseleave="this.style.backgroundColor='<?php echo e($active ? $brandSettings->primary_color . '22' : 'transparent'); ?>'; this.style.color='<?php echo e($active ? $brandSettings->primary_font_color : $brandSettings->primary_font_color . 'cc'); ?>';"
>
    <?php if(! empty($icon)): ?>
        <span class="flex size-5 shrink-0 items-center justify-center text-base transition-colors"
            style="color: <?php echo e($active ? $brandSettings->primary_font_color : $brandSettings->primary_font_color . 'aa'); ?>;"
        ><?php echo $icon; ?></span>
    <?php endif; ?>
    <span><?php echo e($label); ?></span>
</a>
<?php /**PATH C:\Users\cindy\OneDrive\Documents\tick B\pplg\writing hting\pointofyou\pointofyou\resources\views/partials/staff-nav-link.blade.php ENDPATH**/ ?>