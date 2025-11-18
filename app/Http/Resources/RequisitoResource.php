<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RequisitoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'codigo' => $this->codigo,
            'descripcion' => $this->descripcion,
            'es_obligatorio' => $this->when($this->pivot, $this->pivot->es_obligatorio ?? true),
            'orden' => $this->when($this->pivot, $this->pivot->orden ?? 0),
            'instrucciones' => $this->when($this->pivot, $this->pivot->instrucciones_especificas),
            'tipo_archivo' => $this->tipo_archivo,
            'tamano_max_mb' => $this->tamano_max_mb,
        ];
    }
}
