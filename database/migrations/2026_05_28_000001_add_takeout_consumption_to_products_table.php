<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('consume_on_takeout')->default(false)->after('description');
            $table->unsignedInteger('consume_per_item')->default(1)->after('consume_on_takeout');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['consume_on_takeout', 'consume_per_item']);
        });
    }
};
