<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VentaItem extends Model
{
    use HasFactory;

    protected $table = 'venta_items';

    protected $fillable = [
        'venta_id',
        'product_id',
        'quantity',
        'precio_costo',
        'precio_venta',
        'subtotal',
        'utilidad',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'precio_costo' => 'decimal:2',
        'precio_venta' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'utilidad' => 'decimal:2',
    ];

    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
