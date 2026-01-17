<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Customer extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = ['name', 'phone', 'email', 'address', 'active'];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
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
