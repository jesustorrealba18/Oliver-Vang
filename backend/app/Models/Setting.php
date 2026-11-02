<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = [
        'setting_key',
        'setting_value',
    ];

    /**
     * Lee un ajuste con caché (para el número de WhatsApp, redes, etc.).
     * Se usa desde la API pública y desde Filament.
     */
    public static function get(string $key, ?string $default = null): ?string
    {
        $value = Cache::rememberForever("setting.$key", function () use ($key) {
            return static::query()->where('setting_key', $key)->value('setting_value');
        });

        return $value ?? $default;
    }

    /** Escribe un ajuste y limpia su caché */
    public static function set(string $key, ?string $value): void
    {
        static::updateOrCreate(
            ['setting_key' => $key],
            ['setting_value' => $value],
        );

        Cache::forget("setting.$key");
    }
}
