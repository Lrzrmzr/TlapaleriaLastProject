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
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('code', 20)->unique(); // Código único de la sucursal
            $table->text('address')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('manager_name', 100)->nullable(); // Nombre del encargado
            $table->boolean('active')->default(true); // Status: 1 = activo, 0 = inactivo
            $table->boolean('is_main')->default(false); // Sucursal principal
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes(); // Para soft delete
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};
