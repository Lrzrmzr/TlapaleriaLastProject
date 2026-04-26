<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cuentas_por_pagar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('numero_nota')->nullable()->comment('Número de nota/factura del proveedor');
            $table->string('concepto');
            $table->decimal('monto_total', 12, 2);
            $table->decimal('monto_pagado', 12, 2)->default(0);
            $table->decimal('saldo', 12, 2)->default(0);
            $table->date('fecha_nota');
            $table->date('fecha_vencimiento')->nullable();
            $table->enum('status', ['pendiente', 'parcial', 'pagado'])->default('pendiente');
            $table->text('notas')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['supplier_id', 'status']);
            $table->index(['branch_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cuentas_por_pagar');
    }
};
