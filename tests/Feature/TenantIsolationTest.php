<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * TenantIsolationTest
 *
 * Verifica que el aislamiento de datos por tenant_id funcione correctamente:
 *  - Un tenant no puede ver los datos de otro tenant.
 *  - Un usuario root (tenant_id = null) ve todos los datos.
 *  - Al crear un registro dentro de contexto de tenant, se asigna tenant_id automáticamente.
 */
class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenantA;
    private Tenant $tenantB;
    private User   $userA;
    private User   $userB;
    private User   $rootUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Crear dos tenants de prueba
        $this->tenantA = Tenant::create([
            'id'     => 'empresa-a',
            'name'   => 'Empresa A',
            'email'  => 'a@test.com',
            'status' => 'active',
            'plan'   => 'starter',
        ]);

        $this->tenantB = Tenant::create([
            'id'     => 'empresa-b',
            'name'   => 'Empresa B',
            'email'  => 'b@test.com',
            'status' => 'active',
            'plan'   => 'starter',
        ]);

        // Usuarios de cada tenant
        $this->userA = User::factory()->create(['tenant_id' => 'empresa-a']);
        $this->userB = User::factory()->create(['tenant_id' => 'empresa-b']);

        // Usuario root (sin tenant)
        $this->rootUser = User::factory()->create(['tenant_id' => null]);
    }

    // ─── Diagnóstico ─────────────────────────────────────────────────────────

    #[Test]
    public function current_tenant_id_returns_tenant_key_after_initialize(): void
    {
        tenancy()->initialize($this->tenantA);
        $result = currentTenantId();
        tenancy()->end();

        $this->assertEquals('empresa-a', $result, "currentTenantId() returned: " . var_export($result, true));
    }

    // ─── Aislamiento básico ───────────────────────────────────────────────────

    #[Test]
    public function tenant_a_cannot_see_tenant_b_customers(): void
    {
        // Crear clientes en cada tenant directamente via DB
        \DB::table('customers')->insert([
            ['name' => 'Cliente A', 'tenant_id' => 'empresa-a', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Cliente B', 'tenant_id' => 'empresa-b', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Simular contexto del tenant A
        tenancy()->initialize($this->tenantA);

        $customers = Customer::all();

        tenancy()->end();

        $this->assertCount(1, $customers);
        $this->assertEquals('Cliente A', $customers->first()->name);
    }

    #[Test]
    public function tenant_b_cannot_see_tenant_a_customers(): void
    {
        \DB::table('customers')->insert([
            ['name' => 'Cliente A', 'tenant_id' => 'empresa-a', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Cliente B', 'tenant_id' => 'empresa-b', 'created_at' => now(), 'updated_at' => now()],
        ]);

        tenancy()->initialize($this->tenantB);
        $customers = Customer::all();
        tenancy()->end();

        $this->assertCount(1, $customers);
        $this->assertEquals('Cliente B', $customers->first()->name);
    }

    #[Test]
    public function root_user_can_see_all_customers_without_scope(): void
    {
        \DB::table('customers')->insert([
            ['name' => 'Cliente A', 'tenant_id' => 'empresa-a', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Cliente B', 'tenant_id' => 'empresa-b', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Sin contexto de tenant (root), withoutTenant() levanta el scope
        $customers = Customer::withoutTenant()->get();

        $this->assertCount(2, $customers);
    }

    // ─── Auto-asignación de tenant_id ────────────────────────────────────────

    #[Test]
    public function creating_record_in_tenant_context_auto_assigns_tenant_id(): void
    {
        tenancy()->initialize($this->tenantA);

        $product = Product::create([
            'name'  => 'Martillo',
            'code'  => 'MART-01',
            'price' => 100,
            'cost'  => 70,
            'stock' => 10,
        ]);

        tenancy()->end();

        $this->assertEquals('empresa-a', $product->tenant_id);
    }

    #[Test]
    public function creating_record_without_tenant_context_leaves_tenant_id_null(): void
    {
        // Sin tenancy()->initialize() — contexto raíz
        $product = Product::withoutTenant()->create([
            'name'  => 'Producto Global',
            'code'  => 'GLOB-01',
            'price' => 50,
            'cost'  => 30,
            'stock' => 5,
        ]);

        $this->assertNull($product->fresh()->tenant_id);
    }

    // ─── Branches ────────────────────────────────────────────────────────────

    #[Test]
    public function branches_are_isolated_per_tenant(): void
    {
        // La migración inserta branches con id=1 explícito, lo que deja
        // la secuencia desincronizada. Avanzamos la secuencia antes de crear.
        \DB::statement("SELECT setval('branches_id_seq', COALESCE((SELECT MAX(id) FROM branches), 0))");

        Branch::create(['name' => 'Sucursal A', 'code' => 'TEST-SUA', 'tenant_id' => 'empresa-a', 'active' => true, 'is_main' => false]);
        Branch::create(['name' => 'Sucursal B', 'code' => 'TEST-SUB', 'tenant_id' => 'empresa-b', 'active' => true, 'is_main' => false]);

        tenancy()->initialize($this->tenantA);
        $branches = Branch::all();
        tenancy()->end();

        $this->assertCount(1, $branches);
        $this->assertEquals('Sucursal A', $branches->first()->name);
    }

    // ─── WithoutTenant scope ──────────────────────────────────────────────────

    #[Test]
    public function without_tenant_scope_bypasses_filter(): void
    {
        \DB::table('customers')->insert([
            ['name' => 'Cliente A', 'tenant_id' => 'empresa-a', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Cliente B', 'tenant_id' => 'empresa-b', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Inicializar tenant A pero usar withoutTenant()
        tenancy()->initialize($this->tenantA);
        $all = Customer::withoutTenant()->get();
        tenancy()->end();

        $this->assertCount(2, $all);
    }

    // ─── Suspended tenant ────────────────────────────────────────────────────

    #[Test]
    public function suspended_tenant_status_is_detected(): void
    {
        $this->tenantA->update(['status' => 'suspended']);
        $this->tenantA->refresh();

        $this->assertTrue($this->tenantA->isSuspended());
        $this->assertFalse($this->tenantA->isActive());
    }

    #[Test]
    public function active_tenant_is_detected(): void
    {
        $this->assertFalse($this->tenantA->isSuspended());
        $this->assertTrue($this->tenantA->isActive());
    }

    // ─── forTenant scope ─────────────────────────────────────────────────────

    #[Test]
    public function for_tenant_scope_filters_by_specific_tenant(): void
    {
        \DB::table('customers')->insert([
            ['name' => 'Cliente A', 'tenant_id' => 'empresa-a', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Cliente B', 'tenant_id' => 'empresa-b', 'created_at' => now(), 'updated_at' => now()],
        ]);

        $customersOfA = Customer::forTenant('empresa-a')->get();

        $this->assertCount(1, $customersOfA);
        $this->assertEquals('empresa-a', $customersOfA->first()->tenant_id);
    }
}
