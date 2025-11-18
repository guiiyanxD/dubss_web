<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ConvocatoriaController extends Controller
{
    /**
     * Listar convocatorias activas
     */
    public function index(Request $request)
    {
        $convocatorias = \App\Models\Convocatoria::activas()
            ->with('requisitos')
            ->get()
            ->map(function($conv) {
                return [
                    'id' => $conv->id,
                    'nombre' => $conv->nombre,
                    'codigo' => $conv->codigo,
                    'tipo_beca' => $conv->tipo_beca,
                    'subtipo' => $conv->subtipo,
                    'descripcion' => $conv->descripcion,
                    'fecha_inicio' => $conv->fecha_inicio->format('Y-m-d'),
                    'fecha_fin' => $conv->fecha_fin->format('Y-m-d'),
                    'fecha_limite_apelacion' => $conv->fecha_limite_apelacion->format('Y-m-d'),
                    'dias_restantes' => now()->diffInDays($conv->fecha_fin, false),
                    'puede_solicitar' => $conv->estaVigente(),
                    'requisitos' => $conv->requisitos->map(function($req) {
                        return [
                            'id' => $req->id,
                            'nombre' => $req->nombre,
                            'descripcion' => $req->descripcion,
                            'es_obligatorio' => $req->pivot->es_obligatorio,
                            'orden' => $req->pivot->orden,
                            'instrucciones' => $req->pivot->instrucciones_especificas,
                            'tipo_archivo' => $req->tipo_archivo,
                            'tamano_max_mb' => $req->tamano_max_mb,
                        ];
                    }),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $convocatorias
        ]);
    }

    /**
     * Detalle de una convocatoria
     */
    public function show($id)
    {
        $convocatoria = \App\Models\Convocatoria::with('requisitos', 'flujos.estado')
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $convocatoria->id,
                'nombre' => $convocatoria->nombre,
                'codigo' => $convocatoria->codigo,
                'tipo_beca' => $convocatoria->tipo_beca,
                'subtipo' => $convocatoria->subtipo,
                'descripcion' => $convocatoria->descripcion,
                'fecha_inicio' => $convocatoria->fecha_inicio->format('Y-m-d'),
                'fecha_fin' => $convocatoria->fecha_fin->format('Y-m-d'),
                'fecha_limite_apelacion' => $convocatoria->fecha_limite_apelacion->format('Y-m-d'),
                'esta_vigente' => $convocatoria->estaVigente(),
                'permite_apelacion' => $convocatoria->permiteApelacion(),
                'requisitos' => $convocatoria->requisitos->map(function($req) {
                    return [
                        'id' => $req->id,
                        'nombre' => $req->nombre,
                        'descripcion' => $req->descripcion,
                        'es_obligatorio' => $req->pivot->es_obligatorio,
                        'orden' => $req->pivot->orden,
                        'instrucciones' => $req->pivot->instrucciones_especificas,
                        'tipo_archivo' => $req->tipo_archivo,
                        'tamano_max_mb' => $req->tamano_max_mb,
                    ];
                }),
                'flujo' => $convocatoria->flujos->map(function($flujo) {
                    return [
                        'orden' => $flujo->orden,
                        'estado' => $flujo->estado->nombre,
                        'tiempo_estimado_horas' => $flujo->tiempo_estimado_horas,
                    ];
                }),
            ]
        ]);
    }
}
