<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cobros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cuenta_por_cobrar_id')->constrained('cuentas_por_cobrar')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('monto', 12, 2);
            $table->date('fecha_cobro');
            $table->enum('metodo_pago', ['efectivo', 'transferencia'])->default('efectivo');
            $table->string('referencia')->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('cuenta_por_cobrar_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cobros');
    }
};
