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
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_paid')->default(false);
            $table->decimal('base_salary', 10, 2)->nullable();
            $table->string('payment_frequency')->default('monthly'); // monthly, bi-weekly, weekly
            $table->boolean('can_manage_inventory')->default(false);
            $table->boolean('can_manage_payroll')->default(false);
            $table->boolean('can_manage_expenses')->default(false);
            $table->boolean('can_view_reports')->default(false);
            $table->boolean('is_admin')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
