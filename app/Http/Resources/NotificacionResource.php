<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificacionResource extends JsonResource
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
            'titulo' => $this->titulo,
            'mensaje' => $this->mensaje,
            'tipo' => $this->tipo,
            'canal' => $this->canal,
            'leida' => $this->leida,
            'leida_en' => $this->leida_en?->format('Y-m-d H:i:s'),
            'tramite_id' => $this->tramite_id,
            'tramite' => $this->when($this->tramite, [
                'codigo' => $this->tramite?->codigo,
                'estado' => $this->tramite?->estadoActual->nombre,
            ]),
            'tiempo_transcurrido' => $this->created_at->diffForHumans(),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
