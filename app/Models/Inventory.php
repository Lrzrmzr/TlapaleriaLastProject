<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inventory extends Model
{
    protected $fillable = ['product_id', 'quantity', 'stock', 'min_stock'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Obtiene el total de productos en inventario
     */
    public static function totalProductosEnStock()
    {
        return self::sum('quantity');
    }

    /**
     * Obtiene el conteo de productos con stock bajo
     */
    public static function productosStockBajo($limite = 2)
    {
        return self::where('quantity', '<=', $limite)
            ->where('quantity', '>=', 0)
            ->count();
    }
}
