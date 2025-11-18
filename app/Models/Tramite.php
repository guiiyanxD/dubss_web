<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Tramite extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tramites';

    protected $fillable = [
        'codigo',
        'usuario_id',
        'convocatoria_id',
        'estado_actual_id',
        'turno_id',
        'operador_asignado_id',
        'fecha_solicitud',
        'fecha_ultima_actualizacion',
        'es_apelacion',
        'tramite_original_id',
        'observaciones',
        'metadatos',
    ];

    protected $casts = [
        'fecha_solicitud' => 'datetime',
        'fecha_ultima_actualizacion' => 'datetime',
        'es_apelacion' => 'boolean',
        'metadatos' => 'array',
    ];

    // Relaciones
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function convocatoria(): BelongsTo
    {
        return $this->belongsTo(Convocatoria::class);
    }

    public function estadoActual(): BelongsTo
    {
        return $this->belongsTo(FlujoEstado::class, 'estado_actual_id');
    }

    public function turno(): BelongsTo
    {
        return $this->belongsTo(Turno::class);
    }

    public function operadorAsignado(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operador_asignado_id');
    }

    public function documentos(): HasMany
    {
        return $this->hasMany(TramiteDocumento::class);
    }

    public function historial(): HasMany
    {
        return $this->hasMany(TramiteHistorial::class)->orderBy('fecha_cambio', 'desc');
    }

    public function notificaciones(): HasMany
    {
        return $this->hasMany(Notificacion::class);
    }

    public function tramiteOriginal(): BelongsTo
    {
        return $this->belongsTo(Tramite::class, 'tramite_original_id');
    }

    public function apelaciones(): HasMany
    {
        return $this->hasMany(Tramite::class, 'tramite_original_id');
    }

    // Scopes
    public function scopePorUsuario($query, $usuarioId)
    {
        return $query->where('usuario_id', $usuarioId);
    }

    public function scopeEnEstado($query, $estadoId)
    {
        return $query->where('estado_actual_id', $estadoId);
    }

    public function scopeActivos($query)
    {
        return $query->whereHas('estadoActual', function($q) {
            $q->where('es_final', false);
        });
    }

    // Helpers
    public function tiempoTotalProcesamiento(): ?int
    {
        $primerHistorial = $this->historial()->oldest('fecha_cambio')->first();
        $ultimoHistorial = $this->historial()->latest('fecha_cambio')->first();

        if (!$primerHistorial || !$ultimoHistorial) {
            return null;
        }

        return $primerHistorial->fecha_cambio->diffInMinutes($ultimoHistorial->fecha_cambio);
    }

    public function puedeApelar(): bool
    {
        return !$this->es_apelacion
            && $this->estadoActual->es_final
            && $this->convocatoria->permiteApelacion();
    }

    public function documentosCompletos(): bool
    {
        $requisitosObligatorios = $this->convocatoria->requisitos()
            ->wherePivot('es_obligatorio', true)
            ->count();

        $documentosAprobados = $this->documentos()
            ->where('estado', 'aprobado')
            ->count();

        return $documentosAprobados >= $requisitosObligatorios;
    }
}
