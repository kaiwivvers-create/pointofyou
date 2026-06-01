<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('operational_expenses', function (Blueprint $table) {
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete()->after('expense_category_id');
            $table->enum('source', ['manual', 'auto_stock_purchase'])->default('auto_stock_purchase')->after('product_id');
            $table->enum('item_type', ['inventory', 'supply'])->nullable()->after('source');
            $table->unsignedInteger('quantity')->default(1)->after('item_type');
        });
    }

    public function down(): void
    {
        Schema::table('operational_expenses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_id');
            $table->dropColumn(['source', 'item_type', 'quantity']);
        });
    }
};
