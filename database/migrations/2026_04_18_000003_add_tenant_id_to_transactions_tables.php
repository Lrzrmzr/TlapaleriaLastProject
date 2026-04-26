<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Tablas con FK directa a tenants
    private array $mainTables = [
        'sales',
        'purchases',
        'gastos',
        'faltantes',
        'cuentas_por_cobrar',
        'cuentas_por_pagar',
        'branch_inventory',
        'inventories',
    ];

    // Tablas hijas (sin FK a tenants, se filtran por tabla padre)
    private array $childTables = [
        'sale_details',
        'purchase_details',
        'cobros',
        'pagos_proveedor',
    ];

    public function up(): void
    {
        foreach ($this->mainTables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) {
                    $table->string('tenant_id')->nullable()->after('id');
                    $table->foreign('tenant_id')->references('id')->on('tenants')->onUpdate('cascade')->onDelete('cascade');
                });
            }
        }

        foreach ($this->childTables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) {
                    $table->string('tenant_id')->nullable()->after('id');
                });
            }
        }
    }

    public function down(): void
    {
        foreach (array_reverse($this->childTables) as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropColumn('tenant_id');
                });
            }
        }

        foreach (array_reverse($this->mainTables) as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropForeign(['tenant_id']);
                    $table->dropColumn('tenant_id');
                });
            }
        }
    }
};
