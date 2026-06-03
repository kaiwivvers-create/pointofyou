<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\MenuCategory;

return new class extends Migration
{
    public function up()
    {
        // Add promos category if it doesn't exist
        MenuCategory::firstOrCreate(
            ['name' => 'promos'],
            [
                'label' => 'Promos',
                'sort_order' => 999,
                'is_visible' => true,
            ]
        );

        // Add packets category if it doesn't exist
        MenuCategory::firstOrCreate(
            ['name' => 'packets'],
            [
                'label' => 'Packets',
                'sort_order' => 998,
                'is_visible' => true,
            ]
        );
    }

    public function down()
    {
        MenuCategory::where('name', 'promos')->delete();
        MenuCategory::where('name', 'packets')->delete();
    }
};
