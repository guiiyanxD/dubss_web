<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TramiteHistorialResource extends JsonResource
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
            'estado_anterior' => $this->when($this->estadoAnterior, [
                'nombre' => $this->estadoAnterior?->nombre,
                'codigo' => $this->estadoAnterior?->codigo,
            ]),
            'estado_nuevo' => [
                'nombre' => $this->estadoNuevo->nombre,
                'codigo' => $this->estadoNuevo->codigo,
                'color' => $this->estadoNuevo->color_hex,
            ],
            'usuario' => [
                'nombre_completo' => $this->usuario->nombreCompleto(),
                'rol' => $this->usuario->rol,
            ],
            'fecha_cambio' => $this->fecha_cambio->format('Y-m-d H:i:s'),
            'tiempo_en_estado_anterior' => $this->when(
                $this->tiempo_en_estado_anterior_min,
                [
                    'minutos' => $this->tiempo_en_estado_anterior_min,
                    'horas' => number_format($this->tiempo_en_estado_anterior_min / 60, 1),
                    'formato_legible' => $this->formatTiempo(),
                ]
            ),
            'comentario' => $this->comentario,
        ];
    }

    private function formatTiempo()
    {
        if (!$this->tiempo_en_estado_anterior_min) {
            return null;
        }

        $minutos = $this->tiempo_en_estado_anterior_min;

        if ($minutos < 60) {
            return "{$minutos} minutos";
        }

        if ($minutos < 1440) { // menos de 24 horas
            $horas = floor($minutos / 60);
            $mins = $minutos % 60;
            return $mins > 0 ? "{$horas}h {$mins}m" : "{$horas} horas";
        }

        $dias = floor($minutos / 1440);
        $horasRestantes = floor(($minutos % 1440) / 60);
        return $horasRestantes > 0 ? "{$dias}d {$horasRestantes}h" : "{$dias} días";
    }
}
