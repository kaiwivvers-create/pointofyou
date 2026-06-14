<?php
$source = __DIR__ . '/../database/database.sqlite';
$target = __DIR__ . '/../storage/app/database.sqlite';

// Ensure storage/app exists
if (!is_dir(__DIR__ . '/../storage/app')) {
    mkdir(__DIR__ . '/../storage/app', 0777, true);
}

// Copy database to storage/app where web server has write permissions
if (file_exists($source)) {
    copy($source, $target);
    chmod($target, 0666);
    echo "Successfully copied database.sqlite to storage/app/ directory.<br>";
} else {
    echo "Source database.sqlite not found in database/ directory. Creating a blank one in storage/app/.<br>";
    file_put_contents($target, '');
    chmod($target, 0666);
}

// Clear cached config
@unlink(__DIR__ . '/../bootstrap/cache/config.php');
@unlink(__DIR__ . '/../bootstrap/cache/routes.php');
@unlink(__DIR__ . '/../bootstrap/cache/services.php');
@unlink(__DIR__ . '/../bootstrap/cache/packages.php');

echo "Cache cleared. SQLite database is now running from the storage folder where permissions are guaranteed by the framework. You can safely delete this file now.";
