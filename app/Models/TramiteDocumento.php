<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TramiteDocumento extends Model
{
    use HasFactory;

    protected $table = 'tramite_documentos';

    protected $fillable = [
        'tramite_id',
        'requisito_id',
        'nombre_archivo',
        'ruta_archivo',
        'hash_archivo',
        'estado',
        'observaciones',
        'validado_por',
        'validado_en',
    ];

    protected $casts = [
        'validado_en' => 'datetime',
    ];

    // Relaciones
    public function tramite(): BelongsTo
    {
        return $this->belongsTo(Tramite::class);
    }

    public function requisito(): BelongsTo
    {
        return $this->belongsTo(Requisito::class);
    }

    public function validador(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'validado_por');
    }

    // Helpers
    public function aprobar(Usuario $operador, ?string $observaciones = null): void
    {
        $this->update([
            'estado' => 'aprobado',
            'validado_por' => $operador->id,
            'validado_en' => now(),
            'observaciones' => $observaciones,
        ]);
    }

    public function rechazar(Usuario $operador, string $observaciones): void
    {
        $this->update([
            'estado' => 'rechazado',
            'validado_por' => $operador->id,
            'validado_en' => now(),
            'observaciones' => $observaciones,
        ]);
    }
}
