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
        // Products - agregar soft deletes y active status (solo si no existen)
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'active')) {
                $table->boolean('active')->default(true)->after('supplier_id');
            }
            if (!Schema::hasColumn('products', 'deleted_at')) {
                $table->softDeletes()->after('updated_at');
            }
        });

        // Suppliers - agregar soft deletes y active status (solo si no existen)
        Schema::table('suppliers', function (Blueprint $table) {
            if (!Schema::hasColumn('suppliers', 'active')) {
                $table->boolean('active')->default(true)->after('email');
            }
            if (!Schema::hasColumn('suppliers', 'deleted_at')) {
                $table->softDeletes()->after('updated_at');
            }
        });

        // Categories - agregar soft deletes (ya tiene active) (solo si no existe)
        Schema::table('categories', function (Blueprint $table) {
            if (!Schema::hasColumn('categories', 'deleted_at')) {
                $table->softDeletes()->after('updated_at');
            }
        });

        // Customers - agregar soft deletes y active status (solo si no existen)
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'active')) {
                $table->boolean('active')->default(true)->after('email');
            }
            if (!Schema::hasColumn('customers', 'deleted_at')) {
                $table->softDeletes()->after('updated_at');
            }
        });

        // Sales - agregar soft deletes y status (solo si no existen)
        Schema::table('sales', function (Blueprint $table) {
            if (!Schema::hasColumn('sales', 'status')) {
                $table->enum('status', ['completed', 'cancelled', 'pending'])->default('completed')->after('user_id');
            }
            if (!Schema::hasColumn('sales', 'deleted_at')) {
                $table->softDeletes()->after('updated_at');
            }
        });

        // Purchases - agregar soft deletes y status (solo si no existen)
        Schema::table('purchases', function (Blueprint $table) {
            if (!Schema::hasColumn('purchases', 'status')) {
                $table->enum('status', ['completed', 'cancelled', 'pending'])->default('completed')->after('user_id');
            }
            if (!Schema::hasColumn('purchases', 'deleted_at')) {
                $table->softDeletes()->after('updated_at');
            }
        });

        // Inventories - agregar soft deletes (solo si no existen)
        Schema::table('inventories', function (Blueprint $table) {
            if (!Schema::hasColumn('inventories', 'active')) {
                $table->boolean('active')->default(true)->after('min_stock');
            }
            if (!Schema::hasColumn('inventories', 'deleted_at')) {
                $table->softDeletes()->after('updated_at');
            }
        });

        // Faltantes - agregar soft deletes (solo si no existe)
        if (Schema::hasTable('faltantes')) {
            Schema::table('faltantes', function (Blueprint $table) {
                if (!Schema::hasColumn('faltantes', 'deleted_at')) {
                    $table->softDeletes()->after('updated_at');
                }
            });
        }

        // Gastos - agregar soft deletes (solo si no existen)
        if (Schema::hasTable('gastos')) {
            Schema::table('gastos', function (Blueprint $table) {
                if (!Schema::hasColumn('gastos', 'active')) {
                    $table->boolean('active')->default(true)->after('total');
                }
                if (!Schema::hasColumn('gastos', 'deleted_at')) {
                    $table->softDeletes()->after('updated_at');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('active');
            $table->dropSoftDeletes();
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn('active');
            $table->dropSoftDeletes();
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('active');
            $table->dropSoftDeletes();
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->dropSoftDeletes();
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->dropSoftDeletes();
        });

        Schema::table('inventories', function (Blueprint $table) {
            $table->dropColumn('active');
            $table->dropSoftDeletes();
        });

        if (Schema::hasTable('faltantes')) {
            Schema::table('faltantes', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }

        if (Schema::hasTable('gastos')) {
            Schema::table('gastos', function (Blueprint $table) {
                $table->dropColumn('active');
                $table->dropSoftDeletes();
            });
        }
    }
};
