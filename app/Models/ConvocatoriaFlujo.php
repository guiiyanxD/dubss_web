<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ConvocatoriaFlujo extends Model
{
    use HasFactory;

    protected $table = 'convocatoria_flujos';

    protected $fillable = [
        'convocatoria_id',
        'estado_id',
        'estado_siguiente_id',
        'orden',
        'tiempo_estimado_horas',
        'requiere_notificacion',
        'metadatos',
    ];

    protected $casts = [
        'requiere_notificacion' => 'boolean',
        'metadatos' => 'array',
    ];

    // Relaciones
    public function convocatoria(): BelongsTo
    {
        return $this->belongsTo(Convocatoria::class);
    }

    public function estado(): BelongsTo
    {
        return $this->belongsTo(FlujoEstado::class, 'estado_id');
    }

    public function estadoSiguiente(): BelongsTo
    {
        return $this->belongsTo(FlujoEstado::class, 'estado_siguiente_id');
    }

    // Helpers
    public function tieneEstadoSiguiente(): bool
    {
        return $this->estado_siguiente_id !== null;
    }
}
