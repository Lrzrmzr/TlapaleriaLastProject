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
        Schema::create('branch_inventory', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->integer('stock')->default(0);
            $table->integer('min_stock')->default(0);
            $table->integer('max_stock')->default(0);
            $table->decimal('cost', 10, 2)->nullable(); // Costo promedio en esta sucursal
            $table->boolean('active')->default(true); // Si el producto está activo en esta sucursal
            $table->timestamps();
            $table->softDeletes();

            // Índices para optimizar búsquedas
            $table->unique(['branch_id', 'product_id'], 'branch_product_unique');
            $table->index('branch_id');
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branch_inventory');
    }
};
