<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Branch;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

class BranchesAndRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::beginTransaction();

        try {
            // Crear sucursal principal
            $mainBranch = Branch::create([
                'name' => 'Ferretería TOTORO - Matriz',
                'code' => 'MATRIZ',
                'address' => 'Dirección de la sucursal principal',
                'phone' => '555-0000',
                'email' => 'matriz@tlapaleria.com',
                'manager_name' => 'Administrador Principal',
                'active' => true,
                'is_main' => true,
                'notes' => 'Sucursal principal - Casa matriz',
            ]);

            // Verificar que existan los roles base
            // No creamos roles nuevos, solo verificamos que existan los existentes
            $rolesExistentes = Role::whereIn('id', [1, 2, 3, 4])->get();

            if ($rolesExistentes->count() < 4) {
                $this->command->warn('⚠️  Advertencia: No se encontraron todos los roles base en la BD');
                $this->command->info('Se esperan los roles: 1=root, 2=administrador, 3=trabajador, 4=cliente');
            } else {
                $this->command->info('✅ Roles base encontrados:');
                foreach ($rolesExistentes as $role) {
                    $this->command->info("   - {$role->id}: {$role->name} ({$role->description})");
                }
            }

            DB::commit();

            $this->command->info('✅ Sucursal principal creada exitosamente');
            $this->command->info('📍 Sucursal: ' . $mainBranch->name . ' (' . $mainBranch->code . ')');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('❌ Error: ' . $e->getMessage());
            throw $e;
        }
    }
}
