<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TramiteDocumentoResource extends JsonResource
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
            'requisito' => [
                'id' => $this->requisito->id,
                'nombre' => $this->requisito->nombre,
                'codigo' => $this->requisito->codigo,
            ],
            'nombre_archivo' => $this->nombre_archivo,
            'ruta_archivo' => $this->ruta_archivo,
            'url_descarga' => $this->when(
                $this->ruta_archivo,
                url('storage/' . $this->ruta_archivo)
            ),
            'estado' => $this->estado,
            'observaciones' => $this->observaciones,
            'validado_por' => $this->when($this->validador, new UsuarioResource($this->validador)),
            'validado_en' => $this->validado_en?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
