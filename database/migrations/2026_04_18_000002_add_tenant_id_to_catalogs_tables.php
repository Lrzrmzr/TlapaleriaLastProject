<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Catálogos maestros
        foreach (['customers', 'suppliers', 'products', 'categories'] as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->string('tenant_id')->nullable()->after('id');
                $table->foreign('tenant_id')->references('id')->on('tenants')->onUpdate('cascade')->onDelete('cascade');
            });
        }

        // Tablas pivot: no tienen FK directa a tenants, se filtran por las tablas padre
        Schema::table('category_product', function (Blueprint $table) {
            $table->string('tenant_id')->nullable()->after('id');
        });

        Schema::table('product_supplier', function (Blueprint $table) {
            $table->string('tenant_id')->nullable()->after('product_id');
        });
    }

    public function down(): void
    {
        Schema::table('product_supplier', function (Blueprint $table) {
            $table->dropColumn('tenant_id');
        });

        Schema::table('category_product', function (Blueprint $table) {
            $table->dropColumn('tenant_id');
        });

        foreach (['categories', 'products', 'suppliers', 'customers'] as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropForeign(['tenant_id']);
                $table->dropColumn('tenant_id');
            });
        }
    }
};
