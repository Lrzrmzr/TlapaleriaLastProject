<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\CuentaPorCobrar;
use App\Models\Tenant;
use App\Traits\BelongsToTenant;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Customer extends Model
{
    use SoftDeletes, LogsActivity, BelongsToTenant;

    protected $fillable = ['name', 'phone', 'email', 'address', 'active', 'tenant_id'];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function cuentasPorCobrar(): HasMany
    {
        return $this->hasMany(CuentaPorCobrar::class);
    }

    public function saldoPendiente(): float
    {
        return (float) $this->cuentasPorCobrar()
            ->whereIn('status', ['pendiente', 'parcial'])
            ->sum('saldo');
    }

    /**
     * Obtiene el total de clientes activos
     */
    public static function clientesActivos()
    {
        return self::whereHas('sales', function ($query) {
            $query->whereMonth('created_at', now()->month);
        })->count();
    }

    /**
     * Obtiene clientes nuevos de hoy
     */
    public static function clientesNuevosHoy()
    {
        return self::whereDate('created_at', today())->count();
    }

    /**
     * Obtiene el total de clientes
     */
    public static function totalClientes()
    {
        return self::count();
    }

    /**
     * Scope para obtener solo clientes activos
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
            ->logOnly(['name', 'phone', 'email', 'address', 'active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Cliente {$eventName}");
    }
}
