<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TurnoResource extends JsonResource
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
            'codigo' => $this->codigo,
            'fecha' => $this->fecha->format('Y-m-d'),
            'hora_inicio' => $this->hora_inicio,
            'hora_fin' => $this->hora_fin,
            'duracion_minutos' => $this->duracion_minutos,
            'estado' => $this->estado,
            'puede_cancelar' => $this->puedeSerCancelado(),
            'esta_vencido' => $this->estaVencido(),
            'atendido_por' => $this->when($this->atendidoPor, new UsuarioResource($this->atendidoPor)),
            'atendido_en' => $this->atendido_en?->format('Y-m-d H:i:s'),
            'observaciones' => $this->observaciones,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
