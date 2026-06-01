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
        Schema::table('menu_items', function (Blueprint $table) {
            $table->boolean('is_promo')->default(false)->after('is_available');
            $table->string('promo_type')->nullable()->after('is_promo');
            $table->decimal('promo_discount_percentage', 5, 2)->nullable()->after('promo_type');
            $table->decimal('promo_discount_amount', 8, 2)->nullable()->after('promo_discount_percentage');
            $table->unsignedBigInteger('promo_buy_item_id')->nullable()->after('promo_discount_amount');
            $table->unsignedBigInteger('promo_get_item_id')->nullable()->after('promo_buy_item_id');
            $table->integer('promo_min_quantity')->default(1)->after('promo_get_item_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropColumn([
                'is_promo',
                'promo_type',
                'promo_discount_percentage',
                'promo_discount_amount',
                'promo_buy_item_id',
                'promo_get_item_id',
                'promo_min_quantity',
            ]);
        });
    }
};
