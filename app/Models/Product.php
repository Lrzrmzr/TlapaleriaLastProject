<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Tenant;
use App\Traits\BelongsToTenant;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Product extends Model
{
    use SoftDeletes, LogsActivity, BelongsToTenant;

    protected $fillable = ['name', 'description', 'barcode', 'price', 'cost', 'supplier_id', 'active', 'tenant_id'];

    protected $casts = [
        'price' => 'decimal:2',
        'cost' => 'decimal:2',
        'active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Relación many-to-many con proveedores
     */
    public function suppliers(): BelongsToMany
    {
        return $this->belongsToMany(Supplier::class, 'product_supplier')
            ->withPivot('cost', 'supplier_code', 'is_preferred', 'last_purchase_date', 'notes')
            ->withTimestamps();
    }

    /**
     * Relación many-to-many con categorías
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_product')
            ->withTimestamps();
    }

    /**
     * Obtener el proveedor preferido
     */
    public function preferredSupplier()
    {
        return $this->suppliers()->wherePivot('is_preferred', true)->first();
    }

    /**
     * Obtener el costo más bajo entre todos los proveedores
     */
    public function getLowestCostAttribute()
    {
        return $this->suppliers()->min('product_supplier.cost') ?? $this->cost ?? 0;
    }

    public function inventory(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }

    /**
     * Relación con inventario por sucursal
     */
    public function branchInventory(): HasMany
    {
        return $this->hasMany(BranchInventory::class);
    }

    /**
     * Relación muchos a muchos con sucursales a través del inventario
     */
    public function branches(): BelongsToMany
    {
        return $this->belongsToMany(Branch::class, 'branch_inventory')
            ->withPivot('stock', 'min_stock', 'max_stock', 'cost', 'active')
            ->withTimestamps();
    }

    public function saleDetails(): HasMany
    {
        return $this->hasMany(SaleDetail::class);
    }

    public function purchaseDetails(): HasMany
    {
        return $this->hasMany(PurchaseDetail::class);
    }

    /**
     * Scope para obtener solo productos activos
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Configuración de Activity Log
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'description', 'barcode', 'price', 'cost', 'supplier_id', 'active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Producto {$eventName}");
    }

    /**
     * Obtiene el stock total del producto
     */
    public function getStockAttribute()
    {
        return $this->inventory()->sum('quantity');
    }

    /**
     * Obtiene los productos más vendidos
     */
    public static function topProductos($limit = 4)
    {
        return self::select('products.*')
            ->join('sale_details', 'products.id', '=', 'sale_details.product_id')
            ->where('sale_details.tipo_venta', 'catalogo')
            ->groupBy('products.id')
            ->selectRaw('SUM(sale_details.quantity) as total_ventas')
            ->selectRaw('SUM(sale_details.subtotal) as total_ingresos')
            ->orderByDesc('total_ventas')
            ->limit($limit)
            ->get()
            ->map(function ($product) {
                return [
                    'name' => $product->name,
                    'sales' => $product->total_ventas ?? 0,
                    'revenue' => '$' . number_format($product->total_ingresos ?? 0, 0),
                    'trend' => 'up', // TODO: Calcular tendencia real
                    'color' => 'text-orange-600'
                ];
            });
    }

    /**
     * Obtiene el conteo total de productos
     */
    public static function totalProductos()
    {
        return self::count();
    }

    /**
     * Obtiene el conteo de productos nuevos de hoy
     */
    public static function productosNuevosHoy()
    {
        return self::whereDate('created_at', today())->count();
    }
}
