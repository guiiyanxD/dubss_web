<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TramiteHistorial extends Model
{
    use HasFactory;

    protected $table = 'tramite_historial';

    protected $fillable = [
        'tramite_id',
        'estado_anterior_id',
        'estado_nuevo_id',
        'usuario_id',
        'fecha_cambio',
        'tiempo_en_estado_anterior_min',
        'comentario',
        'metadatos',
    ];

    protected $casts = [
        'fecha_cambio' => 'datetime',
        'metadatos' => 'array',
    ];

    // Relaciones
    public function tramite(): BelongsTo
    {
        return $this->belongsTo(Tramite::class);
    }

    public function estadoAnterior(): BelongsTo
    {
        return $this->belongsTo(FlujoEstado::class, 'estado_anterior_id');
    }

    public function estadoNuevo(): BelongsTo
    {
        return $this->belongsTo(FlujoEstado::class, 'estado_nuevo_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
