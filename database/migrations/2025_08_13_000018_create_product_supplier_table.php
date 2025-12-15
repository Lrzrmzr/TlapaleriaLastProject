<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('product_supplier', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('cascade');
            $table->decimal('cost', 10, 2); // Costo del proveedor específico
            $table->string('supplier_code')->nullable(); // Código del producto en ese proveedor
            $table->boolean('is_preferred')->default(false); // Proveedor preferido
            $table->date('last_purchase_date')->nullable(); // Última fecha de compra
            $table->text('notes')->nullable(); // Notas adicionales
            $table->timestamps();

            // Evitar duplicados: un proveedor solo puede aparecer una vez por producto
            $table->unique(['product_id', 'supplier_id']);
        });
    }

    public function down() {
        Schema::dropIfExists('product_supplier');
    }
};
