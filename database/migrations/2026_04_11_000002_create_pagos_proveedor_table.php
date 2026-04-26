<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagos_proveedor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cuenta_por_pagar_id')->constrained('cuentas_por_pagar')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('monto', 12, 2);
            $table->date('fecha_pago');
            $table->enum('metodo_pago', ['efectivo', 'transferencia', 'cheque'])->default('efectivo');
            $table->string('referencia')->nullable()->comment('Número de cheque o referencia de transferencia');
            $table->text('notas')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('cuenta_por_pagar_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos_proveedor');
    }
};
