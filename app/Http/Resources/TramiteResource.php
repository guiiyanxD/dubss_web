<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TramiteResource extends JsonResource
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
            'convocatoria' => [
                'id' => $this->convocatoria->id,
                'nombre' => $this->convocatoria->nombre,
                'codigo' => $this->convocatoria->codigo,
                'tipo_beca' => $this->convocatoria->tipo_beca,
                'subtipo' => $this->convocatoria->subtipo,
            ],
            'estado_actual' => [
                'id' => $this->estadoActual->id,
                'nombre' => $this->estadoActual->nombre,
                'codigo' => $this->estadoActual->codigo,
                'color' => $this->estadoActual->color_hex,
                'descripcion' => $this->estadoActual->descripcion,
                'es_final' => $this->estadoActual->es_final,
                'permite_edicion' => $this->estadoActual->permite_edicion,
            ],
            'fecha_solicitud' => $this->fecha_solicitud->format('Y-m-d H:i:s'),
            'fecha_ultima_actualizacion' => $this->fecha_ultima_actualizacion->format('Y-m-d H:i:s'),
            'es_apelacion' => $this->es_apelacion,
            'puede_apelar' => $this->puedeApelar(),
            'observaciones' => $this->observaciones,
            'documentos_completos' => $this->documentosCompletos(),
            'estadisticas_documentos' => [
                'total' => $this->documentos->count(),
                'aprobados' => $this->documentos->where('estado', 'aprobado')->count(),
                'rechazados' => $this->documentos->where('estado', 'rechazado')->count(),
                'pendientes' => $this->documentos->where('estado', 'pendiente')->count(),
            ],
            'documentos' => TramiteDocumentoResource::collection($this->whenLoaded('documentos')),
            'historial' => TramiteHistorialResource::collection($this->whenLoaded('historial')),
            'usuario' => $this->when($this->relationLoaded('usuario'), new UsuarioResource($this->usuario)),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
