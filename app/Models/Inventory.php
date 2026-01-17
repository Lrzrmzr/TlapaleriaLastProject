<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inventory extends Model
{
    use SoftDeletes;

    protected $fillable = ['product_id', 'branch_id', 'stock', 'min_stock', 'active'];

    protected $casts = [
        'active' => 'boolean',
    ];

    // Alias para compatibilidad
    public function getQuantityAttribute()
    {
        return $this->stock;
    }

    public function setQuantityAttribute($value)
    {
        $this->attributes['stock'] = $value;
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Scope para filtrar por sucursal
     */
    public function scopeForBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    /**
     * Scope para obtener solo inventarios activos
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Obtiene el total de productos en inventario
     */
    public static function totalProductosEnStock()
    {
        return self::sum('stock');
    }

    /**
     * Obtiene el conteo de productos con stock bajo
     */
    public static function productosStockBajo($limite = 2)
    {
        return self::where('stock', '<=', $limite)
            ->where('stock', '>=', 0)
            ->count();
    }
}
