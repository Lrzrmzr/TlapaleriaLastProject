<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\CuentaPorPagar;
use App\Models\Tenant;
use App\Traits\BelongsToTenant;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Supplier extends Model
{
    use SoftDeletes, LogsActivity, BelongsToTenant;

    protected $fillable = ['name', 'contact_name', 'phone', 'email', 'address', 'active', 'tenant_id'];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

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

    public function cuentasPorPagar(): HasMany
    {
        return $this->hasMany(CuentaPorPagar::class);
    }

    /**
     * Scope para obtener solo proveedores activos
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
            ->logOnly(['name', 'contact_name', 'phone', 'email', 'address', 'active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Proveedor {$eventName}");
    }
}
