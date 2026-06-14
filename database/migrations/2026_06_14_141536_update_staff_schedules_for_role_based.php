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
        Schema::dropIfExists('staff_schedules');

        Schema::create('staff_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->integer('day_of_week')->comment('0=Sunday, 1=Monday, ..., 6=Saturday');
            $table->boolean('is_day_off')->default(false);
            $table->time('expected_start_time')->nullable();
            $table->time('expected_end_time')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // Each role should only have one schedule per day of week
            $table->unique(['role_id', 'day_of_week']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_schedules');

        Schema::create('staff_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('role_id')->nullable()->constrained()->onDelete('cascade');
            $table->date('date')->nullable();
            $table->string('type')->default('work_day');
            $table->time('expected_start_time')->nullable();
            $table->time('expected_end_time')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->unique(['user_id', 'date']);
        });
    }
};
