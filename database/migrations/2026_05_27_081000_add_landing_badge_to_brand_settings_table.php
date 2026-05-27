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
        Schema::table('brand_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('brand_settings', 'landing_badge')) {
                $table->string('landing_badge')->default('Artisan bakery since 2026')->after('landing_kicker');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('brand_settings', function (Blueprint $table) {
            if (Schema::hasColumn('brand_settings', 'landing_badge')) {
                $table->dropColumn('landing_badge');
            }
        });
    }
};
