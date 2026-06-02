<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('menu_categories')->insert([
            'name' => 'packets',
            'label' => 'Packets',
            'sort_order' => 5,
            'is_visible' => true,
            'icon_url' => 'https://images.unsplash.com/photo-1565958011703-44f9829ba187?w=120&auto=format&fit=crop&q=80',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('menu_categories')->where('name', 'packets')->delete();
    }
};
