<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ConfigSistema extends Model
{
    use HasFactory;

    protected $table = 'config_sistema';

    protected $fillable = [
        'clave',
        'valor',
        'tipo',
        'descripcion',
    ];

    // Helper estático
    public static function obtener(string $clave, $default = null)
    {
        $config = self::where('clave', $clave)->first();

        if (!$config) {
            return $default;
        }

        return match($config->tipo) {
            'int' => (int) $config->valor,
            'boolean' => filter_var($config->valor, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($config->valor, true),
            default => $config->valor,
        };
    }
}
