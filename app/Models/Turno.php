<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Turno extends Model
{
    use HasFactory;

    protected $table = 'turnos';

    protected $fillable = [
        'usuario_id',
        'fecha',
        'hora_inicio',
        'hora_fin',
        'codigo',
        'estado',
        'duracion_minutos',
        'atendido_por',
        'atendido_en',
        'observaciones',
    ];

    protected $casts = [
        'fecha' => 'date',
        'atendido_en' => 'datetime',
    ];

    // Relaciones
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function atendidoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'atendido_por');
    }

    public function tramites(): HasMany
    {
        return $this->hasMany(Tramite::class);
    }

    // Scopes
    public function scopeDisponibles($query, $fecha = null)
    {
        $fecha = $fecha ?? now()->toDateString();

        return $query->where('fecha', $fecha)
            ->where('estado', 'reservado')
            ->where('hora_inicio', '>', now()->toTimeString());
    }

    public function scopeAtendidos($query)
    {
        return $query->where('estado', 'atendido');
    }

    // Helpers
    public function estaVencido(): bool
    {
        return now()->gt($this->fecha->setTimeFromTimeString($this->hora_fin));
    }

    public function puedeSerCancelado(): bool
    {
        return $this->estado === 'reservado'
            && now()->lt($this->fecha->setTimeFromTimeString($this->hora_inicio));
    }
}
