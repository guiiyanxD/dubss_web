<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Convocatoria extends Model
{
    use HasFactory;

    protected $table = 'convocatorias';

    protected $fillable = [
        'nombre',
        'codigo',
        'tipo_beca',
        'subtipo',
        'descripcion',
        'fecha_inicio',
        'fecha_fin',
        'fecha_limite_apelacion',
        'activa',
        'version',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'fecha_limite_apelacion' => 'date',
        'activa' => 'boolean',
    ];

    // Relaciones
    public function tramites(): HasMany
    {
        return $this->hasMany(Tramite::class);
    }

    public function flujos(): HasMany
    {
        return $this->hasMany(ConvocatoriaFlujo::class)->orderBy('orden');
    }

    public function requisitos()
    {
        return $this->belongsToMany(Requisito::class, 'convocatoria_requisitos')
            ->withPivot(['es_obligatorio', 'orden', 'instrucciones_especificas'])
            ->withTimestamps()
            ->orderByPivot('orden');
    }

    // Scopes
    public function scopeActivas($query)
    {
        return $query->where('activa', true)
            ->where('fecha_inicio', '<=', now())
            ->where('fecha_fin', '>=', now());
    }

    public function scopeTipo($query, string $tipo)
    {
        return $query->where('tipo_beca', $tipo);
    }

    // Helpers
    public function estaVigente(): bool
    {
        return $this->activa
            && now()->between($this->fecha_inicio, $this->fecha_fin);
    }

    public function permiteApelacion(): bool
    {
        return now()->lte($this->fecha_limite_apelacion);
    }

    public function estadoInicial(): ?FlujoEstado
    {
        return $this->flujos()->whereHas('estado', function($q) {
            $q->where('es_inicial', true);
        })->first()?->estado;
    }
}
