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
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, boolean, integer
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Insertar configuraciones por defecto
        DB::table('system_settings')->insert([
            [
                'key' => 'enable_ventas_libres',
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Habilitar ventas libres (productos no registrados en inventario)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'enable_faltantes_manual',
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Habilitar registro manual de productos faltantes',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'stock_bajo_threshold',
                'value' => '5',
                'type' => 'integer',
                'description' => 'Cantidad mínima de stock para considerar producto bajo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
