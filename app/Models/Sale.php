<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Sale extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = ['customer_id', 'user_id', 'branch_id', 'total', 'status'];

    protected $casts = [
        'total' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function saleDetails(): HasMany
    {
        return $this->hasMany(SaleDetail::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Obtiene las ventas del día actual
     */
    public static function ventasHoy()
    {
        return self::whereDate('created_at', today())->sum('total');
    }

    /**
     * Obtiene las ventas de ayer
     */
    public static function ventasAyer()
    {
        return self::whereDate('created_at', today()->subDay())->sum('total');
    }

    /**
     * Obtiene el porcentaje de cambio vs ayer
     */
    public static function porcentajeCambioHoy()
    {
        $hoy = self::ventasHoy();
        $ayer = self::ventasAyer();

        if ($ayer == 0) {
            return $hoy > 0 ? 100 : 0;
        }

        return round((($hoy - $ayer) / $ayer) * 100, 1);
    }

    /**
     * Obtiene las ventas recientes con detalles
     */
    public static function ventasRecientes($limit = 4)
    {
        return self::with(['customer', 'user', 'saleDetails.product'])
            ->latest()
            ->take($limit)
            ->get()
            ->map(function ($sale) {
                $firstDetail = $sale->saleDetails->first();
                return [
                    'product' => $firstDetail ? $firstDetail->descripcion_completa : 'N/A',
                    'quantity' => $sale->saleDetails->sum('quantity'),
                    'amount' => '$' . number_format($sale->total, 0),
                    'time' => $sale->created_at->diffForHumans(),
                    'customer' => $sale->customer ? $sale->customer->name : 'Cliente general'
                ];
            });
    }

    /**
     * Obtiene el total de ventas del mes
     */
    public static function ventasMes()
    {
        return self::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total');
    }

    /**
     * Obtiene el conteo de ventas de hoy
     */
    public static function conteoVentasHoy()
    {
        return self::whereDate('created_at', today())->count();
    }

    /**
     * Scope para filtrar por sucursal
     */
    public function scopeForBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    /**
     * Scope para solo ventas completadas
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Configuración de Activity Log
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Venta {$eventName}");
    }
}
