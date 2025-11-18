<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Requisito extends Model
{
    use HasFactory;

    protected $table = 'requisitos';

    protected $fillable = [
        'nombre',
        'codigo',
        'descripcion',
        'es_comun',
        'tipo_archivo',
        'tamano_max_mb',
    ];

    protected $casts = [
        'es_comun' => 'boolean',
    ];

    // Relaciones
    public function convocatorias(): BelongsToMany
    {
        return $this->belongsToMany(Convocatoria::class, 'convocatoria_requisitos')
            ->withPivot(['es_obligatorio', 'orden', 'instrucciones_especificas'])
            ->withTimestamps();
    }

    public function documentos(): HasMany
    {
        return $this->hasMany(TramiteDocumento::class);
    }

    // Helpers
    public function tiposArchivoPermitidos(): array
    {
        return explode(',', $this->tipo_archivo);
    }
}
