<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Product;
use App\Models\InventoryCategory;

echo "Debugging menu ingredients...\n";

// Check all products
$allProducts = Product::with('category')->get();
echo "\nAll products: " . $allProducts->count() . "\n";
foreach ($allProducts as $prod) {
    $catName = $prod->category ? $prod->category->name : 'none';
    $catType = $prod->category ? $prod->category->type : 'none';
    echo "  - {$prod->name} (Category: {$catName}, Type: {$catType})\n";
}

// Check products with ingredient categories
$ingredientProducts = Product::with('category')->whereHas('category', function($query) {
    $query->where('type', 'ingredient');
})->get();

echo "\nProducts with ingredient type categories: " . $ingredientProducts->count() . "\n";
foreach ($ingredientProducts as $prod) {
    echo "  - {$prod->name} (Category: {$prod->category->name})\n";
}
