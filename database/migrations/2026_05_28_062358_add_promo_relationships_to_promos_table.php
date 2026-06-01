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
        Schema::table('promos', function (Blueprint $table) {
            $table->unsignedBigInteger('buy_item_id')->nullable()->after('order');
            $table->unsignedBigInteger('get_item_id')->nullable()->after('buy_item_id');
            $table->integer('buy_quantity')->default(1)->after('get_item_id');
            $table->integer('get_quantity')->default(1)->after('buy_quantity');
            $table->string('discount_type')->nullable()->after('get_quantity');
            $table->decimal('discount_value', 8, 2)->nullable()->after('discount_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('promos', function (Blueprint $table) {
            $table->dropColumn([
                'buy_item_id',
                'get_item_id',
                'buy_quantity',
                'get_quantity',
                'discount_type',
                'discount_value',
            ]);
        });
    }
};
