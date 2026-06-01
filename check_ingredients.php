<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Product;
use App\Models\InventoryCategory;

echo "Checking products and categories...\n";

$categories = InventoryCategory::where('type', 'ingredient')->get();
echo "Ingredient categories: " . $categories->count() . "\n";
foreach ($categories as $cat) {
    echo "  - {$cat->name}\n";
}

$products = Product::with('category')->whereHas('category', function($query) {
    $query->where('type', 'ingredient');
})->get();

echo "\nProducts with ingredient categories: " . $products->count() . "\n";
foreach ($products as $prod) {
    $catName = $prod->category ? $prod->category->name : 'none';
    echo "  - {$prod->name} (Category: {$catName})\n";
}
