<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MenuCategory;

class SpecialCategoriesSeeder extends Seeder
{
    public function run()
    {
        // Add promos category if it doesn't exist
        MenuCategory::firstOrCreate(
            ['name' => 'promos'],
            [
                'label' => 'Promos',
                'sort_order' => 0,
                'is_visible' => true,
            ]
        );

        // Add packets category if it doesn't exist
        MenuCategory::firstOrCreate(
            ['name' => 'packets'],
            [
                'label' => 'Packets',
                'sort_order' => 1,
                'is_visible' => true,
            ]
        );
    }
}
