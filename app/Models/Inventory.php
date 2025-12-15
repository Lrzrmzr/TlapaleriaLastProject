<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inventory extends Model
{
    protected $fillable = ['product_id', 'stock', 'min_stock'];

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
