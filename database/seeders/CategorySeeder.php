<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Carpintería',
                'slug' => 'carpinteria',
                'description' => 'Herramientas y materiales para trabajos de carpintería',
                'icon' => '🔨',
                'color' => '#8B4513', // Brown
                'active' => true,
                'order' => 1,
            ],
            [
                'name' => 'Albañilería',
                'slug' => 'albanileria',
                'description' => 'Materiales para construcción y albañilería',
                'icon' => '🧱',
                'color' => '#DC2626', // Red
                'active' => true,
                'order' => 2,
            ],
            [
                'name' => 'Plomería',
                'slug' => 'plomeria',
                'description' => 'Tuberías, conexiones y accesorios de plomería',
                'icon' => '🚰',
                'color' => '#2563EB', // Blue
                'active' => true,
                'order' => 3,
            ],
            [
                'name' => 'Electricidad',
                'slug' => 'electricidad',
                'description' => 'Cables, interruptores y material eléctrico',
                'icon' => '⚡',
                'color' => '#FBBF24', // Yellow/Amber
                'active' => true,
                'order' => 4,
            ],
            [
                'name' => 'Pintura',
                'slug' => 'pintura',
                'description' => 'Pinturas, barnices, brochas y rodillos',
                'icon' => '🎨',
                'color' => '#7C3AED', // Purple
                'active' => true,
                'order' => 5,
            ],
            [
                'name' => 'Jardinería',
                'slug' => 'jardineria',
                'description' => 'Herramientas y productos para jardinería',
                'icon' => '🌱',
                'color' => '#16A34A', // Green
                'active' => true,
                'order' => 6,
            ],
            [
                'name' => 'Ferretería General',
                'slug' => 'ferreteria-general',
                'description' => 'Productos generales de ferretería',
                'icon' => '🔧',
                'color' => '#64748B', // Slate
                'active' => true,
                'order' => 7,
            ],
            [
                'name' => 'Herrajes',
                'slug' => 'herrajes',
                'description' => 'Bisagras, cerraduras, manijas y herrajes diversos',
                'icon' => '🔩',
                'color' => '#475569', // Gray
                'active' => true,
                'order' => 8,
            ],
            [
                'name' => 'Seguridad',
                'slug' => 'seguridad',
                'description' => 'Candados, cerrojos y equipos de seguridad',
                'icon' => '🔒',
                'color' => '#EF4444', // Red
                'active' => true,
                'order' => 9,
            ],
            [
                'name' => 'Limpieza',
                'slug' => 'limpieza',
                'description' => 'Productos de limpieza y mantenimiento',
                'icon' => '🧹',
                'color' => '#06B6D4', // Cyan
                'active' => true,
                'order' => 10,
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }

        $this->command->info('✅ Categorías creadas exitosamente');
    }
}
