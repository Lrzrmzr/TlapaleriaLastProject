<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Faltante extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = ['descripcion', 'pedido', 'user_id', 'branch_id', 'confirmado'];

    protected $casts = [
        'confirmado' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Scope para filtrar por sucursal
     */
    public function scopeForBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
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

    /**
     * Configuración de Activity Log
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['descripcion', 'pedido', 'user_id', 'branch_id', 'confirmado'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Faltante {$eventName}");
    }
}
