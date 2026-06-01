<?php
$target = __DIR__.'/../storage/app/public'; // Points to your storage folder
$shortcut = __DIR__.'/storage'; // The shortcut that will be created

if (file_exists($shortcut)) {
    echo "The shortcut already exists!";
} else if (symlink($target, $shortcut)) {
    echo "Success! The storage folder has been linked!";
} else {
    echo "Failed. Your hosting provider might have disabled symlinks for security.";
}