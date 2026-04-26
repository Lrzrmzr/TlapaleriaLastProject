<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class PagoProveedor extends Model
{
    use SoftDeletes, LogsActivity;

    protected $table = 'pagos_proveedor';

    protected $fillable = [
        'cuenta_por_pagar_id',
        'user_id',
        'monto',
        'fecha_pago',
        'metodo_pago',
        'referencia',
        'notas',
    ];

    protected $casts = [
        'monto'      => 'decimal:2',
        'fecha_pago' => 'date',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function cuentaPorPagar(): BelongsTo
    {
        return $this->belongsTo(CuentaPorPagar::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ─── Model Events ─────────────────────────────────────────────────────────

    protected static function booted(): void
    {
        static::created(function (PagoProveedor $pago) {
            $pago->cuentaPorPagar->recalcular();
        });

        static::deleted(function (PagoProveedor $pago) {
            $pago->cuentaPorPagar->recalcular();
        });
    }

    // ─── Activity Log ─────────────────────────────────────────────────────────

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['monto', 'fecha_pago', 'metodo_pago', 'referencia'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $event) => "Pago a proveedor {$event}");
    }
}
