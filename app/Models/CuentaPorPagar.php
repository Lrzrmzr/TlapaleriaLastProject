<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Tenant;
use App\Traits\BelongsToTenant;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class CuentaPorPagar extends Model
{
    use SoftDeletes, LogsActivity, BelongsToTenant;

    protected $table = 'cuentas_por_pagar';

    protected $fillable = [
        'supplier_id',
        'branch_id',
        'user_id',
        'numero_nota',
        'concepto',
        'monto_total',
        'monto_pagado',
        'saldo',
        'fecha_nota',
        'fecha_vencimiento',
        'status',
        'notas',
        'tenant_id',
    ];

    protected $casts = [
        'monto_total'      => 'decimal:2',
        'monto_pagado'     => 'decimal:2',
        'saldo'            => 'decimal:2',
        'fecha_nota'       => 'date',
        'fecha_vencimiento'=> 'date',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(PagoProveedor::class);
    }

    // ─── Business Logic ───────────────────────────────────────────────────────

    /**
     * Recalculate monto_pagado, saldo and status from registered pagos.
     */
    public function recalcular(): void
    {
        $pagado = $this->pagos()->sum('monto');
        $saldo  = $this->monto_total - $pagado;

        $status = 'pendiente';
        if ($saldo <= 0) {
            $status = 'pagado';
            $saldo  = 0;
        } elseif ($pagado > 0) {
            $status = 'parcial';
        }

        $this->update([
            'monto_pagado' => $pagado,
            'saldo'        => $saldo,
            'status'       => $status,
        ]);
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopePendiente($query)
    {
        return $query->where('status', 'pendiente');
    }

    public function scopeVencidas($query)
    {
        return $query->whereIn('status', ['pendiente', 'parcial'])
                     ->whereNotNull('fecha_vencimiento')
                     ->where('fecha_vencimiento', '<', now()->toDateString());
    }

    // ─── Activity Log ─────────────────────────────────────────────────────────

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['concepto', 'monto_total', 'monto_pagado', 'saldo', 'status', 'supplier_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $event) => "Cuenta por pagar {$event}");
    }
}
