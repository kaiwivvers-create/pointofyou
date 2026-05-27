<?php

namespace App\Console\Commands;

use App\Models\InventoryCategory;
use App\Models\MenuItem;
use App\Models\Product;
use Illuminate\Console\Command;

class SyncMenuItemsToInventory extends Command
{
    protected $signature = 'app:sync-menu-items-to-inventory';

    protected $description = 'Sync menu items to inventory products';

    public function handle()
    {
        $this->info('Syncing menu items to inventory products...');

        // Create a default category if it doesn't exist
        $defaultCategory = InventoryCategory::firstOrCreate(
            ['name' => 'Menu Items'],
            [
                'description' => 'Products synced from menu items',
                'color' => '#6366f1'
            ]
        );

        $menuItems = MenuItem::all();
        $synced = 0;
        $skipped = 0;

        foreach ($menuItems as $menuItem) {
            // Check if product already exists
            $existingProduct = Product::where('sku', 'MENU-' . $menuItem->id)->first();

            if ($existingProduct) {
                $skipped++;
                $this->line("Skipped: {$menuItem->name} (already exists)");
                continue;
            }

            Product::create([
                'sku' => 'MENU-' . $menuItem->id,
                'name' => $menuItem->name,
                'description' => $menuItem->description ?? 'Synced from menu',
                'inventory_category_id' => $defaultCategory->id,
                'purchase_price' => $menuItem->price * 0.6, // Estimate 60% of price as cost
                'selling_price' => $menuItem->price,
                'stock_quantity' => 0,
                'min_stock_level' => 10,
                'unit' => 'pcs',
            ]);

            $synced++;
            $this->info("Synced: {$menuItem->name}");
        }

        $this->info("Synced {$synced} menu items to inventory products.");
        $this->info("Skipped {$skipped} items (already exist).");

        return Command::SUCCESS;
    }
}
