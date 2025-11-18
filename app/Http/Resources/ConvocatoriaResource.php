<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConvocatoriaResource extends JsonResource
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
            'tipo_beca' => $this->tipo_beca,
            'subtipo' => $this->subtipo,
            'descripcion' => $this->descripcion,
            'fecha_inicio' => $this->fecha_inicio->format('Y-m-d'),
            'fecha_fin' => $this->fecha_fin->format('Y-m-d'),
            'fecha_limite_apelacion' => $this->fecha_limite_apelacion->format('Y-m-d'),
            'dias_restantes' => now()->diffInDays($this->fecha_fin, false),
            'esta_vigente' => $this->estaVigente(),
            'permite_apelacion' => $this->permiteApelacion(),
            'requisitos' => RequisitoResource::collection($this->whenLoaded('requisitos')),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
