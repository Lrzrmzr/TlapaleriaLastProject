<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $fillable = ['key', 'value', 'type', 'description'];

    /**
     * Obtener el valor de una configuración
     */
    public static function get(string $key, $default = null)
    {
        $setting = self::where('key', $key)->first();

        if (!$setting) {
            return $default;
        }

        return self::castValue($setting->value, $setting->type);
    }

    /**
     * Establecer el valor de una configuración
     */
    public static function set(string $key, $value): bool
    {
        $setting = self::where('key', $key)->first();

        if (!$setting) {
            return false;
        }

        $setting->value = $value;
        return $setting->save();
    }

    /**
     * Convertir el valor según su tipo
     */
    private static function castValue($value, $type)
    {
        return match($type) {
            'boolean' => (bool) $value,
            'integer' => (int) $value,
            'float' => (float) $value,
            default => $value,
        };
    }

    /**
     * Verificar si las ventas libres están habilitadas
     */
    public static function ventasLibresHabilitadas(): bool
    {
        return self::get('enable_ventas_libres', true);
    }

    /**
     * Verificar si los faltantes manuales están habilitados
     */
    public static function faltantesManualesHabilitados(): bool
    {
        return self::get('enable_faltantes_manual', true);
    }

    /**
     * Obtener el threshold de stock bajo
     */
    public static function stockBajoThreshold(): int
    {
        return self::get('stock_bajo_threshold', 5);
    }
}
