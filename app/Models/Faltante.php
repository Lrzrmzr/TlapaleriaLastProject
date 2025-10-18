<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Faltante extends Model
{
    protected $fillable = ['descripcion', 'pedido', 'user_id', 'confirmado'];

    protected $casts = [
        'confirmado' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtiene el conteo de faltantes pendientes (no confirmados)
     */
    public static function faltantesPendientes()
    {
        return self::where('confirmado', false)->count();
    }

    /**
     * Obtiene el conteo de faltantes de hoy
     */
    public static function faltantesHoy()
    {
        return self::whereDate('created_at', today())->count();
    }

    /**
     * Obtiene el conteo de faltantes de ayer
     */
    public static function faltantesAyer()
    {
        return self::whereDate('created_at', today()->subDay())->count();
    }

    /**
     * Obtiene el cambio de faltantes vs ayer
     */
    public static function cambioFaltantes()
    {
        $hoy = self::faltantesHoy();
        $ayer = self::faltantesAyer();
        return $hoy - $ayer;
    }
}
