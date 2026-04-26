<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cuentas_por_cobrar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('concepto');
            $table->decimal('monto_total', 12, 2);
            $table->decimal('monto_cobrado', 12, 2)->default(0);
            $table->decimal('saldo', 12, 2)->default(0);
            $table->date('fecha');
            $table->date('fecha_vencimiento')->nullable();
            $table->enum('status', ['pendiente', 'parcial', 'cobrado'])->default('pendiente');
            $table->text('notas')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['customer_id', 'status']);
            $table->index(['branch_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cuentas_por_cobrar');
    }
};
