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
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('role'); // super_admin, owner, manager, admin, cashier
            $table->string('permission'); // e.g., 'view_menu', 'edit_menu', 'view_users', etc.
            $table->boolean('can_view')->default(false);
            $table->boolean('can_edit')->default(false);
            $table->timestamps();
            
            $table->unique(['role', 'permission']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};
