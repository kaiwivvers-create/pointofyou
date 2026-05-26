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
        Schema::create('brand_settings', function (Blueprint $table) {
            $table->id();
            $table->string('app_name')->default('Golden Crumb');
            $table->string('logo_fallback')->default('GC');
            $table->string('logo')->nullable();
            $table->string('landing_kicker')->nullable();
            $table->json('fan_favourite_ids')->nullable();
            $table->string('address')->nullable();
            $table->string('hours')->nullable();
            $table->string('instagram')->nullable();
            $table->string('facebook')->nullable();
            $table->string('phone')->nullable();
            $table->string('primary_color')->default('#f59e0b');
            $table->string('secondary_color')->default('#faf6f0');
            $table->string('accent_color')->default('#10b981');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brand_settings');
    }
};
