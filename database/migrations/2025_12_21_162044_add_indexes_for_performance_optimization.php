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
        // Índices para la tabla products
        Schema::table('products', function (Blueprint $table) {
            // Índice para búsquedas por nombre
            $table->index('name', 'idx_products_name');

            // Índice para búsquedas por código de barras
            $table->index('barcode', 'idx_products_barcode');

            // Índice compuesto para filtros comunes (proveedor + activo)
            $table->index(['supplier_id', 'active'], 'idx_products_supplier_active');

            // Índice para ordenamiento por fecha de creación
            $table->index('created_at', 'idx_products_created_at');
        });

        // Índices para la tabla branch_inventory
        Schema::table('branch_inventory', function (Blueprint $table) {
            // Índice compuesto para filtros por sucursal y producto
            $table->index(['branch_id', 'product_id'], 'idx_inventory_branch_product');

            // Índice para filtros de stock bajo
            $table->index(['stock', 'min_stock'], 'idx_inventory_stock_levels');

            // Índice para ordenamiento por fecha de actualización
            $table->index('updated_at', 'idx_inventory_updated_at');

            // Índice compuesto para filtros por sucursal y stock
            $table->index(['branch_id', 'stock'], 'idx_inventory_branch_stock');
        });

        // Índices para la tabla category_product (pivot)
        Schema::table('category_product', function (Blueprint $table) {
            // Índice para búsquedas de productos por categoría
            $table->index('category_id', 'idx_category_product_category');

            // Índice para búsquedas de categorías de un producto
            $table->index('product_id', 'idx_category_product_product');
        });

        // Índices para la tabla categories
        Schema::table('categories', function (Blueprint $table) {
            // Índice para búsquedas por slug
            $table->index('slug', 'idx_categories_slug');

            // Índice compuesto para ordenamiento
            $table->index(['active', 'order'], 'idx_categories_active_order');
        });

        // Índices para la tabla suppliers
        Schema::table('suppliers', function (Blueprint $table) {
            // Índice para búsquedas por nombre
            $table->index('name', 'idx_suppliers_name');

            // Índice para filtrar solo activos
            $table->index('active', 'idx_suppliers_active');
        });

        // Índices para la tabla users
        Schema::table('users', function (Blueprint $table) {
            // Índice compuesto para filtros por sucursal y activo
            $table->index(['branch_id', 'active'], 'idx_users_branch_active');

            // Índice para búsquedas por nombre
            $table->index('name', 'idx_users_name');
        });

        // Índices para la tabla branches
        Schema::table('branches', function (Blueprint $table) {
            // Índice para filtrar solo activas
            $table->index('active', 'idx_branches_active');

            // Índice para búsquedas por código
            $table->index('code', 'idx_branches_code');
        });

        // Índices para la tabla sales
        Schema::table('sales', function (Blueprint $table) {
            // Índice compuesto para reportes por sucursal y estado
            $table->index(['branch_id', 'status'], 'idx_sales_branch_status');

            // Índice para ordenamiento por fecha
            $table->index('created_at', 'idx_sales_created_at');

            // Índice para búsquedas por usuario
            $table->index('user_id', 'idx_sales_user');
        });

        // Índices para la tabla purchases
        Schema::table('purchases', function (Blueprint $table) {
            // Índice compuesto para reportes por sucursal y estado
            $table->index(['branch_id', 'status'], 'idx_purchases_branch_status');

            // Índice para ordenamiento por fecha
            $table->index('created_at', 'idx_purchases_created_at');

            // Índice para búsquedas por proveedor
            $table->index('supplier_id', 'idx_purchases_supplier');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar índices de products
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('idx_products_name');
            $table->dropIndex('idx_products_barcode');
            $table->dropIndex('idx_products_supplier_active');
            $table->dropIndex('idx_products_created_at');
        });

        // Eliminar índices de branch_inventory
        Schema::table('branch_inventory', function (Blueprint $table) {
            $table->dropIndex('idx_inventory_branch_product');
            $table->dropIndex('idx_inventory_stock_levels');
            $table->dropIndex('idx_inventory_updated_at');
            $table->dropIndex('idx_inventory_branch_stock');
        });

        // Eliminar índices de category_product
        Schema::table('category_product', function (Blueprint $table) {
            $table->dropIndex('idx_category_product_category');
            $table->dropIndex('idx_category_product_product');
        });

        // Eliminar índices de categories
        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex('idx_categories_slug');
            $table->dropIndex('idx_categories_active_order');
        });

        // Eliminar índices de suppliers
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropIndex('idx_suppliers_name');
            $table->dropIndex('idx_suppliers_active');
        });

        // Eliminar índices de users
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_branch_active');
            $table->dropIndex('idx_users_name');
        });

        // Eliminar índices de branches
        Schema::table('branches', function (Blueprint $table) {
            $table->dropIndex('idx_branches_active');
            $table->dropIndex('idx_branches_code');
        });

        // Eliminar índices de sales
        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex('idx_sales_branch_status');
            $table->dropIndex('idx_sales_created_at');
            $table->dropIndex('idx_sales_user');
        });

        // Eliminar índices de purchases
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropIndex('idx_purchases_branch_status');
            $table->dropIndex('idx_purchases_created_at');
            $table->dropIndex('idx_purchases_supplier');
        });
    }
};
