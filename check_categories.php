<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\InventoryCategory;

echo "Updating category types...\n";

// Update Takeout Items, Takeout Supplies, and Packaging to 'supply'
$categories = InventoryCategory::whereIn('name', ['Takeout Items', 'Takeout Supplies', 'Packaging'])->get();

foreach ($categories as $cat) {
    $cat->type = 'supply';
    $cat->save();
    echo "Updated {$cat->name} to type: supply\n";
}

echo "\nDone!\n";
