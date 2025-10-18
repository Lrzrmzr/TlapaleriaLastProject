<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = ['name', 'phone', 'email', 'address'];

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
}
