<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'type', 'description'];

    /**
     * Obtener el valor de una configuración por su clave
     */
    public static function get($key, $default = null)
    {
        try {
            return static::where('key', $key)->value('value') ?? $default;
        } catch (\Exception $e) {
            \Log::warning("Error reading setting '{$key}': " . $e->getMessage());
            return $default;
        }
    }

    /**
     * Establecer el valor de una configuración
     */
    public static function set($key, $value, $type = 'text', $description = null)
    {
        return static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'type' => $type,
                'description' => $description
            ]
        );
    }

    /**
     * Obtener todas las configuraciones como array asociativo
     */
    public static function getAll()
    {
        try {
            return static::all()->pluck('value', 'key')->toArray();
        } catch (\Exception $e) {
            \Log::warning('Error reading all settings: ' . $e->getMessage());
            return [];
        }
    }
}
