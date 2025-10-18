<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleDetail extends Model
{
    protected $fillable = [
        'sale_id',
        'product_id',
        'tipo_venta', // 'catalogo' o 'libre'
        'descripcion', // Para ventas libres
        'quantity',
        'price',
        'subtotal',
        'total'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    /**
     * Relación con la venta
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Relación con el producto (nullable para ventas libres)
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Verifica si es una venta libre
     */
    public function esVentaLibre(): bool
    {
        return $this->tipo_venta === 'libre';
    }

    /**
     * Verifica si es una venta de catálogo
     */
    public function esVentaCatalogo(): bool
    {
        return $this->tipo_venta === 'catalogo';
    }

    /**
     * Obtiene la descripción del detalle
     * Si es venta libre, usa la descripción, sino usa el nombre del producto
     */
    public function getDescripcionCompletaAttribute(): string
    {
        if ($this->esVentaLibre()) {
            return $this->descripcion ?? 'Venta libre';
        }
        return $this->product ? $this->product->name : 'Producto eliminado';
    }
}
