<?php

$storageDir = __DIR__ . '/../storage';
$bootstrapDir = __DIR__ . '/../bootstrap';

$dirs = [
    $storageDir . '/framework/sessions',
    $storageDir . '/framework/views',
    $storageDir . '/framework/cache/data',
    $bootstrapDir . '/cache',
];

$output = "<h2>Fixing Laravel Storage Folders</h2>";

foreach ($dirs as $dir) {
    if (!file_exists($dir)) {
        if (@mkdir($dir, 0775, true)) {
            $output .= "<p style='color:green;'>Created $dir</p>";
        } else {
            $output .= "<p style='color:red;'>Failed to create $dir</p>";
        }
    } else {
        if (@chmod($dir, 0775)) {
            $output .= "<p style='color:blue;'>Ensured permissions for $dir</p>";
        } else {
            $output .= "<p style='color:orange;'>Failed to set permissions for $dir (You might not have permission to change this)</p>";
        }
    }
}

// Clear the view cache directly just in case there are corrupted files
$viewsDir = $storageDir . '/framework/views';
if (file_exists($viewsDir)) {
    $files = glob($viewsDir . '/*.php');
    foreach ($files as $file) {
        @unlink($file);
    }
    $output .= "<p style='color:blue;'>Cleared compiled views.</p>";
}

echo $output . "<h3>Done! Try loading your app now.</h3>";
