<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            // First add the new columns
            $table->foreignId('gift_id')->nullable()->after('menu_item_id')->constrained('gifts')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->after('gift_id')->constrained('products')->cascadeOnDelete();
            
            // Make menu_item_id nullable
            $table->unsignedBigInteger('menu_item_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            // Note: Reverting menu_item_id to not nullable might fail if there are nulls.
            $table->dropForeign(['gift_id']);
            $table->dropForeign(['product_id']);
            $table->dropColumn(['gift_id', 'product_id']);
            $table->unsignedBigInteger('menu_item_id')->nullable(false)->change();
        });
    }
};
