<?php

namespace Database\Seeders;

use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * InitialTenantSeeder
 *
 * Migración única para convertir datos existentes al modelo multi-tenant.
 *
 * Crea el tenant inicial "tlapaleria" y asigna su tenant_id a todos los
 * registros que actualmente no tienen tenant_id (datos pre-SaaS).
 *
 * Los usuarios con rol root quedan con tenant_id = null (acceso global).
 *
 * USO:
 *   php artisan db:seed --class=InitialTenantSeeder
 */
class InitialTenantSeeder extends Seeder
{
    /**
     * Slug del tenant inicial. Debe coincidir con el subdominio que se usará.
     * Ej: "tlapaleria" → tlapaleria.totoro.mx
     */
    private const TENANT_ID = 'tlapaleria';

    public function run(): void
    {
        DB::beginTransaction();

        try {
            // ── 1. Crear (o recuperar) el tenant inicial ─────────────────────
            $tenant = Tenant::firstOrCreate(
                ['id' => self::TENANT_ID],
                [
                    'name'   => 'Tlapalería Principal',
                    'email'  => 'admin@tlapaleria.com',
                    'status' => 'active',
                    'plan'   => 'starter',
                ]
            );

            $tenantId = $tenant->id;
            $this->command->info("Tenant: [{$tenantId}] {$tenant->name}");

            // ── 2. Obtener IDs de usuarios root (no deben tener tenant_id) ───
            $rootUserIds = DB::table('role_user')
                ->join('roles', 'roles.id', '=', 'role_user.role_id')
                ->where('roles.name', 'root')
                ->pluck('role_user.user_id')
                ->toArray();

            $this->command->info('Usuarios root (sin tenant): ' . implode(', ', $rootUserIds ?: ['ninguno']));

            // ── 3. Asignar tenant_id a usuarios no-root sin tenant_id ─────────
            $usersUpdated = DB::table('users')
                ->whereNull('tenant_id')
                ->when(!empty($rootUserIds), fn($q) => $q->whereNotIn('id', $rootUserIds))
                ->update(['tenant_id' => $tenantId]);

            $this->command->info("Usuarios actualizados: {$usersUpdated}");

            // ── 4. Sucursales ─────────────────────────────────────────────────
            $n = DB::table('branches')->whereNull('tenant_id')->update(['tenant_id' => $tenantId]);
            $this->command->info("Sucursales: {$n}");

            // ── 5. Catálogos ──────────────────────────────────────────────────
            foreach (['customers', 'suppliers', 'products', 'categories'] as $table) {
                $n = DB::table($table)->whereNull('tenant_id')->update(['tenant_id' => $tenantId]);
                $this->command->info("{$table}: {$n}");
            }

            // ── 6. Tablas pivot (sin FK, solo columna) ────────────────────────
            foreach (['category_product', 'product_supplier'] as $table) {
                $n = DB::table($table)->whereNull('tenant_id')->update(['tenant_id' => $tenantId]);
                $this->command->info("{$table}: {$n}");
            }

            // ── 7. Transacciones ──────────────────────────────────────────────
            $transaccionTables = [
                'sales', 'purchases', 'gastos', 'faltantes',
                'cuentas_por_cobrar', 'cuentas_por_pagar',
                'branch_inventory', 'inventories',
                'sale_details', 'purchase_details',
                'cobros', 'pagos_proveedor',
            ];

            foreach ($transaccionTables as $table) {
                // Verificar que la tabla existe antes de actualizar
                if (DB::getSchemaBuilder()->hasTable($table)) {
                    $n = DB::table($table)->whereNull('tenant_id')->update(['tenant_id' => $tenantId]);
                    $this->command->info("{$table}: {$n}");
                } else {
                    $this->command->warn("Tabla {$table} no encontrada, omitiendo.");
                }
            }

            DB::commit();

            $this->command->newLine();
            $this->command->info('✅ Migración al tenant inicial completada exitosamente.');
            $this->command->info("   Tenant ID : {$tenantId}");
            $this->command->info("   Nombre    : {$tenant->name}");

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->command->error('❌ Error durante la migración: ' . $e->getMessage());
            throw $e;
        }
    }
}
