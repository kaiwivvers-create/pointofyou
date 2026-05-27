<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // matches menu_items.category value
            $table->string('label');          // display label e.g. "Food"
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->timestamps();
        });

        // Seed defaults
        DB::table('menu_categories')->insert([
            ['name' => 'promo',  'label' => 'Promo',  'sort_order' => 1, 'is_visible' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'food',   'label' => 'Food',   'sort_order' => 2, 'is_visible' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'drinks', 'label' => 'Drinks', 'sort_order' => 3, 'is_visible' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'pastry', 'label' => 'Pastry', 'sort_order' => 4, 'is_visible' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_categories');
    }
};
