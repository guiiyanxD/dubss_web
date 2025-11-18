<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TramiteController extends Controller
{
    /**
     * Crear nuevo trámite
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'convocatoria_id' => 'required|exists:convocatorias,id',
            'turno_id' => 'nullable|exists:turnos,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $convocatoria = \App\Models\Convocatoria::findOrFail($request->convocatoria_id);

        if (!$convocatoria->estaVigente()) {
            return response()->json([
                'success' => false,
                'message' => 'Esta convocatoria no está vigente'
            ], 400);
        }

        // Verificar que no tenga un trámite activo para esta convocatoria
        $tramiteExistente = \App\Models\Tramite::where('usuario_id', $request->user()->id)
            ->where('convocatoria_id', $request->convocatoria_id)
            ->whereHas('estadoActual', function($q) {
                $q->where('es_final', false);
            })
            ->exists();

        if ($tramiteExistente) {
            return response()->json([
                'success' => false,
                'message' => 'Ya tienes un trámite activo para esta convocatoria'
            ], 400);
        }

        $turno = $request->turno_id ? \App\Models\Turno::find($request->turno_id) : null;

        $service = new \App\Services\TramiteService();
        $tramite = $service->crear($request->user(), $convocatoria, $turno);

        return response()->json([
            'success' => true,
            'message' => 'Trámite creado exitosamente',
            'data' => [
                'id' => $tramite->id,
                'codigo' => $tramite->codigo,
                'convocatoria' => $tramite->convocatoria->nombre,
                'estado' => $tramite->estadoActual->nombre,
                'fecha_solicitud' => $tramite->fecha_solicitud->format('Y-m-d H:i:s'),
            ]
        ], 201);
    }

    /**
     * Mis trámites
     */
    public function index(Request $request)
    {
        $tramites = $request->user()->tramites()
            ->with(['estadoActual', 'convocatoria', 'documentos'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($tramite) {
                return [
                    'id' => $tramite->id,
                    'codigo' => $tramite->codigo,
                    'convocatoria' => [
                        'id' => $tramite->convocatoria->id,
                        'nombre' => $tramite->convocatoria->nombre,
                        'tipo_beca' => $tramite->convocatoria->tipo_beca,
                    ],
                    'estado' => [
                        'nombre' => $tramite->estadoActual->nombre,
                        'codigo' => $tramite->estadoActual->codigo,
                        'color' => $tramite->estadoActual->color_hex,
                        'es_final' => $tramite->estadoActual->es_final,
                    ],
                    'fecha_solicitud' => $tramite->fecha_solicitud->format('Y-m-d H:i:s'),
                    'fecha_ultima_actualizacion' => $tramite->fecha_ultima_actualizacion->format('Y-m-d H:i:s'),
                    'puede_apelar' => $tramite->puedeApelar(),
                    'documentos_completos' => $tramite->documentosCompletos(),
                    'total_documentos' => $tramite->documentos->count(),
                    'documentos_aprobados' => $tramite->documentos->where('estado', 'aprobado')->count(),
                    'documentos_rechazados' => $tramite->documentos->where('estado', 'rechazado')->count(),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $tramites
        ]);
    }

    /**
     * Detalle de un trámite
     */
    public function show(Request $request, $id)
    {
        $tramite = $request->user()->tramites()
            ->with(['estadoActual', 'convocatoria', 'documentos.requisito', 'historial.estadoNuevo', 'historial.usuario'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $tramite->id,
                'codigo' => $tramite->codigo,
                'convocatoria' => [
                    'id' => $tramite->convocatoria->id,
                    'nombre' => $tramite->convocatoria->nombre,
                    'codigo' => $tramite->convocatoria->codigo,
                ],
                'estado_actual' => [
                    'nombre' => $tramite->estadoActual->nombre,
                    'codigo' => $tramite->estadoActual->codigo,
                    'color' => $tramite->estadoActual->color_hex,
                    'descripcion' => $tramite->estadoActual->descripcion,
                    'es_final' => $tramite->estadoActual->es_final,
                ],
                'fecha_solicitud' => $tramite->fecha_solicitud->format('Y-m-d H:i:s'),
                'fecha_ultima_actualizacion' => $tramite->fecha_ultima_actualizacion->format('Y-m-d H:i:s'),
                'puede_apelar' => $tramite->puedeApelar(),
                'observaciones' => $tramite->observaciones,
                'documentos' => $tramite->documentos->map(function($doc) {
                    return [
                        'id' => $doc->id,
                        'requisito' => $doc->requisito->nombre,
                        'nombre_archivo' => $doc->nombre_archivo,
                        'estado' => $doc->estado,
                        'observaciones' => $doc->observaciones,
                        'validado_en' => $doc->validado_en?->format('Y-m-d H:i:s'),
                    ];
                }),
                'historial' => $tramite->historial->map(function($hist) {
                    return [
                        'estado' => $hist->estadoNuevo->nombre,
                        'comentario' => $hist->comentario,
                        'fecha' => $hist->fecha_cambio->format('Y-m-d H:i:s'),
                        'tiempo_en_estado_anterior' => $hist->tiempo_en_estado_anterior_min ? number_format($hist->tiempo_en_estado_anterior_min / 60, 1) . ' horas' : null,
                    ];
                }),
            ]
        ]);
    }

    /**
     * Subir documento
     */
    public function subirDocumento(Request $request, $tramiteId)
    {
        $validator = Validator::make($request->all(), [
            'requisito_id' => 'required|exists:requisitos,id',
            'archivo' => 'required|file|max:10240', // 10MB máximo
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $tramite = $request->user()->tramites()->findOrFail($tramiteId);

        if (!$tramite->estadoActual->permite_edicion) {
            return response()->json([
                'success' => false,
                'message' => 'No se pueden subir documentos en el estado actual del trámite'
            ], 400);
        }

        $requisito = \App\Models\Requisito::findOrFail($request->requisito_id);
        $archivo = $request->file('archivo');

        // Validar tipo de archivo
        $extension = $archivo->getClientOriginalExtension();
        $tiposPermitidos = explode(',', $requisito->tipo_archivo);

        $extensionValida = false;
        foreach ($tiposPermitidos as $tipo) {
            if ($tipo === 'pdf' && $extension === 'pdf') $extensionValida = true;
            if ($tipo === 'image' && in_array($extension, ['jpg', 'jpeg', 'png'])) $extensionValida = true;
        }

        if (!$extensionValida) {
            return response()->json([
                'success' => false,
                'message' => 'Tipo de archivo no permitido para este requisito'
            ], 400);
        }

        // Guardar archivo
        $ruta = $archivo->store('documentos/' . $tramite->id, 'public');
        $hash = hash_file('sha256', $archivo->getRealPath());

        // Verificar si ya existe un documento para este requisito
        $documentoExistente = \App\Models\TramiteDocumento::where('tramite_id', $tramite->id)
            ->where('requisito_id', $requisito->id)
            ->first();

        if ($documentoExistente) {
            // Eliminar archivo anterior
            \Storage::disk('public')->delete($documentoExistente->ruta_archivo);

            $documentoExistente->update([
                'nombre_archivo' => $archivo->getClientOriginalName(),
                'ruta_archivo' => $ruta,
                'hash_archivo' => $hash,
                'estado' => 'pendiente',
                'observaciones' => null,
                'validado_por' => null,
                'validado_en' => null,
            ]);

            $documento = $documentoExistente;
        } else {
            $documento = \App\Models\TramiteDocumento::create([
                'tramite_id' => $tramite->id,
                'requisito_id' => $requisito->id,
                'nombre_archivo' => $archivo->getClientOriginalName(),
                'ruta_archivo' => $ruta,
                'hash_archivo' => $hash,
                'estado' => 'pendiente',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Documento subido exitosamente',
            'data' => [
                'id' => $documento->id,
                'requisito' => $requisito->nombre,
                'nombre_archivo' => $documento->nombre_archivo,
                'estado' => $documento->estado,
            ]
        ], 201);
    }

    /**
     * Crear apelación
     */
    public function apelar(Request $request, $id)
    {
        $tramite = $request->user()->tramites()->findOrFail($id);

        if (!$tramite->puedeApelar()) {
            return response()->json([
                'success' => false,
                'message' => 'Este trámite no puede ser apelado'
            ], 400);
        }

        $service = new \App\Services\TramiteService();
        $apelacion = $service->crearApelacion($tramite, $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Apelación creada exitosamente',
            'data' => [
                'id' => $apelacion->id,
                'codigo' => $apelacion->codigo,
                'estado' => $apelacion->estadoActual->nombre,
            ]
        ], 201);
    }
}
