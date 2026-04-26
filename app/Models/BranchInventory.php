<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Tenant;
use App\Traits\BelongsToTenant;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class BranchInventory extends Model
{
    use HasFactory, SoftDeletes, LogsActivity, BelongsToTenant;

    protected $table = 'branch_inventory';

    protected $fillable = [
        'branch_id',
        'product_id',
        'stock',
        'min_stock',
        'max_stock',
        'cost',
        'active',
        'tenant_id',
    ];

    protected $casts = [
        'stock' => 'integer',
        'min_stock' => 'integer',
        'max_stock' => 'integer',
        'cost' => 'decimal:2',
        'active' => 'boolean',
    ];

    /**
     * Relación con la sucursal
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Relación con el producto
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Scope para obtener solo inventario activo
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope para obtener productos con stock bajo
     */
    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock', '<=', 'min_stock')
            ->where('min_stock', '>', 0);
    }

    /**
     * Scope para obtener productos sin stock
     */
    public function scopeOutOfStock($query)
    {
        return $query->where('stock', '<=', 0);
    }

    /**
     * Scope para filtrar por sucursal
     */
    public function scopeForBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    /**
     * Verificar si el stock está bajo
     */
    public function isLowStock(): bool
    {
        return $this->min_stock > 0 && $this->stock <= $this->min_stock;
    }

    /**
     * Verificar si está fuera de stock
     */
    public function isOutOfStock(): bool
    {
        return $this->stock <= 0;
    }

    /**
     * Agregar stock
     */
    public function addStock(int $quantity): void
    {
        $this->increment('stock', $quantity);
    }

    /**
     * Reducir stock
     */
    public function reduceStock(int $quantity): void
    {
        $this->decrement('stock', $quantity);
    }

    /**
     * Obtener el valor total del inventario de este producto
     */
    public function getValueAttribute(): float
    {
        return $this->stock * ($this->cost ?? $this->product->cost ?? 0);
    }

    /**
     * Configuración de Activity Log
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['branch_id', 'product_id', 'stock', 'min_stock', 'max_stock', 'cost', 'active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Inventario {$eventName}");
    }
}
