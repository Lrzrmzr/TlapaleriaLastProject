<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Branch extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'name',
        'code',
        'address',
        'phone',
        'email',
        'manager_name',
        'active',
        'is_main',
        'notes',
    ];

    protected $casts = [
        'active' => 'boolean',
        'is_main' => 'boolean',
    ];

    /**
     * Scope para obtener solo sucursales activas
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope para obtener la sucursal principal
     */
    public function scopeMain($query)
    {
        return $query->where('is_main', true);
    }

    /**
     * Relación con usuarios de esta sucursal
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Relación con el inventario de esta sucursal
     */
    public function inventory(): HasMany
    {
        return $this->hasMany(BranchInventory::class);
    }

    /**
     * Relación con el inventario de esta sucursal (alias)
     */
    public function inventories(): HasMany
    {
        return $this->hasMany(BranchInventory::class);
    }

    /**
     * Relación muchos a muchos con productos a través del inventario
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'branch_inventory')
            ->withPivot('stock', 'min_stock', 'max_stock', 'cost', 'active')
            ->withTimestamps();
    }

    /**
     * Relación con ventas de esta sucursal
     */
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    /**
     * Relación con ventas de esta sucursal (alias)
     */
    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class);
    }

    /**
     * Relación con compras de esta sucursal
     */
    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    /**
     * Relación con gastos de esta sucursal
     */
    public function gastos(): HasMany
    {
        return $this->hasMany(Gasto::class);
    }

    /**
     * Relación con faltantes de esta sucursal
     */
    public function faltantes(): HasMany
    {
        return $this->hasMany(Faltante::class);
    }

    /**
     * Obtener el stock total de todos los productos en esta sucursal
     */
    public function getTotalStockAttribute()
    {
        return $this->inventory()->sum('stock');
    }

    /**
     * Obtener el valor total del inventario en esta sucursal
     */
    public function getTotalInventoryValueAttribute()
    {
        return $this->inventory()
            ->join('products', 'branch_inventory.product_id', '=', 'products.id')
            ->selectRaw('SUM(branch_inventory.stock * products.price) as total')
            ->value('total') ?? 0;
    }

    /**
     * Verificar si un producto existe en el inventario de esta sucursal
     */
    public function hasProduct($productId): bool
    {
        return $this->inventory()->where('product_id', $productId)->exists();
    }

    /**
     * Obtener el stock de un producto específico en esta sucursal
     */
    public function getProductStock($productId): int
    {
        $inventory = $this->inventory()->where('product_id', $productId)->first();
        return $inventory ? $inventory->stock : 0;
    }

    /**
     * Configuración de Activity Log
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'code', 'address', 'phone', 'email', 'manager_name', 'active', 'is_main', 'notes'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Sucursal {$eventName}");
    }
}
