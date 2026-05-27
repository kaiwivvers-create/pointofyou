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
            if (!Schema::hasColumn('brand_settings', 'primary_font_color')) {
                $table->string('primary_font_color')->default('#78350f')->after('primary_color');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('brand_settings', function (Blueprint $table) {
            if (Schema::hasColumn('brand_settings', 'primary_font_color')) {
                $table->dropColumn('primary_font_color');
            }
        });
    }
};
