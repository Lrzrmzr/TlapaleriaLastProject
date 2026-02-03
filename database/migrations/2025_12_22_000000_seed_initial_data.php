<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Datos iniciales del sistema: roles, sucursal principal y categorías.
     */
    public function up(): void
    {
        // Roles
        DB::table('roles')->insert([
            ['id' => 1, 'name' => 'root',          'description' => 'administrador general', 'created_at' => now(), 'updated_at' => null],
            ['id' => 2, 'name' => 'administrador',  'description' => 'Admin',                'created_at' => now(), 'updated_at' => null],
            ['id' => 3, 'name' => 'trabajador',     'description' => 'Trabajador',            'created_at' => now(), 'updated_at' => null],
            ['id' => 4, 'name' => 'cliente',         'description' => 'Cliente',               'created_at' => now(), 'updated_at' => null],
        ]);

        // Sucursal principal por defecto
        DB::table('branches')->insert([
            'id'           => 1,
            'name'         => 'Ferretería Totoro Copalera',
            'code'         => 'COP-TOTORO',
            'address'      => null,
            'phone'        => '555555555555',
            'email'        => null,
            'manager_name' => 'Lorenzo Ramirez',
            'active'       => true,
            'is_main'      => true,
            'notes'        => 'test',
            'created_at'   => now(),
            'updated_at'   => now(),
            'deleted_at'   => null,
        ]);

        // Categorías
        $now = now();
        DB::table('categories')->insert([
            ['name' => 'Carpintería',        'slug' => 'carpinteria',        'description' => 'Herramientas y materiales para trabajos de carpintería', 'icon' => '🔨', 'color' => '#8B4513', 'active' => true, 'order' =>  1, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Albañilería',        'slug' => 'albanileria',        'description' => 'Materiales para construcción y albañilería',             'icon' => '🧱', 'color' => '#DC2626', 'active' => true, 'order' =>  2, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Plomería',           'slug' => 'plomeria',           'description' => 'Tuberías, conexiones y accesorios de plomería',          'icon' => '🚰', 'color' => '#2563EB', 'active' => true, 'order' =>  3, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Electricidad',       'slug' => 'electricidad',       'description' => 'Cables, interruptores y material eléctrico',             'icon' => '⚡', 'color' => '#FBBF24', 'active' => true, 'order' =>  4, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Pintura',            'slug' => 'pintura',            'description' => 'Pinturas, barnices, brochas y rodillos',                 'icon' => '🎨', 'color' => '#7C3AED', 'active' => true, 'order' =>  5, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Jardinería',         'slug' => 'jardineria',         'description' => 'Herramientas y productos para jardinería',               'icon' => '🌱', 'color' => '#16A34A', 'active' => true, 'order' =>  6, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Ferretería General', 'slug' => 'ferreteria-general', 'description' => 'Productos generales de ferretería',                     'icon' => '🔧', 'color' => '#64748B', 'active' => true, 'order' =>  7, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Herrajes',           'slug' => 'herrajes',           'description' => 'Bisagras, cerraduras, manijas y herrajes diversos',      'icon' => '🔩', 'color' => '#475569', 'active' => true, 'order' =>  8, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Seguridad',          'slug' => 'seguridad',          'description' => 'Candados, cerrojos y equipos de seguridad',              'icon' => '🔒', 'color' => '#EF4444', 'active' => true, 'order' =>  9, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Limpieza',           'slug' => 'limpieza',           'description' => 'Productos de limpieza y mantenimiento',                  'icon' => '🧹', 'color' => '#06B6D4', 'active' => true, 'order' => 10, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('categories')->whereIn('slug', [
            'carpinteria', 'albanileria', 'plomeria', 'electricidad', 'pintura',
            'jardineria', 'ferreteria-general', 'herrajes', 'seguridad', 'limpieza',
        ])->delete();

        DB::table('branches')->where('code', 'COP-TOTORO')->delete();

        DB::table('roles')->whereIn('id', [1, 2, 3, 4])->delete();
    }
};
