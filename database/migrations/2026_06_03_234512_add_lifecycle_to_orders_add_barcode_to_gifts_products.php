<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add picked-up tracking to orders
        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('is_picked_up')->default(false)->after('is_closed');
            $table->unsignedBigInteger('picked_up_by')->nullable()->after('is_picked_up');
            $table->timestamp('picked_up_at')->nullable()->after('picked_up_by');
        });

        // Add barcode to gifts
        Schema::table('gifts', function (Blueprint $table) {
            $table->string('barcode')->nullable()->unique()->after('sku');
        });

        // Add barcode to products (inventory items)
        Schema::table('products', function (Blueprint $table) {
            $table->string('barcode')->nullable()->unique()->after('sku');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['is_picked_up', 'picked_up_by', 'picked_up_at']);
        });

        Schema::table('gifts', function (Blueprint $table) {
            $table->dropColumn('barcode');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('barcode');
        });
    }
};
