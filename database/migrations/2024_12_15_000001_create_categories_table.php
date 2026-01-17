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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique(); // Nombre único: "Carpintería", "Plomería", etc.
            $table->string('slug', 100)->unique(); // URL-friendly: "carpinteria", "plomeria"
            $table->text('description')->nullable(); // Descripción opcional
            $table->string('icon', 50)->nullable(); // Icono opcional (emoji o clase CSS)
            $table->string('color', 7)->default('#3B82F6'); // Color en hex (#RRGGBB)
            $table->boolean('active')->default(true); // Si está activa o no
            $table->integer('order')->default(0); // Orden de visualización
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
