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
        Schema::table('sale_details', function (Blueprint $table) {
            // Hacer product_id nullable para ventas libres
            $table->foreignId('product_id')->nullable()->change();

            // Agregar campo tipo_venta para diferenciar
            if (!Schema::hasColumn('sale_details', 'tipo_venta')) {
                $table->string('tipo_venta', 20)->default('catalogo')->after('product_id');
            }

            // descripcion ya existe de la migración anterior, pero aseguramos que esté
            if (!Schema::hasColumn('sale_details', 'descripcion')) {
                $table->text('descripcion')->nullable()->after('tipo_venta');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sale_details', function (Blueprint $table) {
            $table->dropColumn(['tipo_venta']);
        });
    }
};
