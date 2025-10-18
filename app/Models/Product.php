<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Product extends Model
{
    protected $fillable = ['name', 'description', 'barcode', 'price', 'cost', 'supplier_id'];

    protected $casts = [
        'price' => 'decimal:2',
        'cost' => 'decimal:2',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function inventory(): HasMany
    {
        return $this->hasMany(Inventory::class);
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
