<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class FlujoEstado extends Model
{
    use HasFactory;

    protected $table = 'flujo_estados';

    protected $fillable = [
        'nombre',
        'codigo',
        'color_hex',
        'es_inicial',
        'es_final',
        'permite_edicion',
        'descripcion',
    ];

    protected $casts = [
        'es_inicial' => 'boolean',
        'es_final' => 'boolean',
        'permite_edicion' => 'boolean',
    ];

    // Relaciones
    public function convocatoriaFlujos(): HasMany
    {
        return $this->hasMany(ConvocatoriaFlujo::class, 'estado_id');
    }

    public function tramites(): HasMany
    {
        return $this->hasMany(Tramite::class, 'estado_actual_id');
    }
}
