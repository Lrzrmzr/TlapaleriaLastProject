<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Supplier extends Model
{
    protected $fillable = ['name', 'contact_name', 'phone', 'email', 'address'];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Relación many-to-many con productos (todos los productos que este proveedor suministra)
     */
    public function productsSupplied(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_supplier')
            ->withPivot('cost', 'supplier_code', 'is_preferred', 'last_purchase_date', 'notes')
            ->withTimestamps();
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }
}
