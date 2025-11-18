<?php

namespace App\Services;

use App\Models\Tramite;
use App\Models\User;
use App\Models\Convocatoria;
use App\Models\Turno;
use App\States\TramiteState;
use Illuminate\Support\Facades\DB;

class TramiteService
{
    /**
     * Crear un nuevo trámite
     */
    public function crear(User $usuario, Convocatoria $convocatoria, ?Turno $turno = null): Tramite
    {
        return DB::transaction(function () use ($usuario, $convocatoria, $turno) {
            // Obtener estado inicial del flujo
            $estadoInicial = $convocatoria->estadoInicial();

            if (!$estadoInicial) {
                throw new \Exception("La convocatoria no tiene un estado inicial configurado");
            }

            // Generar código único
            $codigo = $this->generarCodigo($convocatoria);

            // Crear trámite
            $tramite = Tramite::create([
                'codigo' => $codigo,
                'usuario_id' => $usuario->id,
                'convocatoria_id' => $convocatoria->id,
                'estado_actual_id' => $estadoInicial->id,
                'turno_id' => $turno?->id,
                'fecha_solicitud' => now(),
                'fecha_ultima_actualizacion' => now(),
            ]);

            // Registrar historial inicial
            \App\Models\TramiteHistorial::create([
                'tramite_id' => $tramite->id,
                'estado_anterior_id' => null,
                'estado_nuevo_id' => $estadoInicial->id,
                'usuario_id' => $usuario->id,
                'fecha_cambio' => now(),
                'comentario' => 'Trámite creado',
            ]);

            // Notificación
            \App\Models\Notificacion::create([
                'usuario_id' => $usuario->id,
                'tramite_id' => $tramite->id,
                'titulo' => 'Trámite creado exitosamente',
                'mensaje' => "Tu trámite {$codigo} ha sido registrado. Turno: " . ($turno ? $turno->codigo : 'Sin turno asignado'),
                'tipo' => 'success',
                'canal' => 'app',
            ]);

            return $tramite->load('estadoActual', 'convocatoria');
        });
    }

    /**
     * Transicionar trámite a nuevo estado
     */
    public function transicionar(Tramite $tramite, string $nuevoEstadoCodigo, User $operador, ?string $comentario = null): Tramite
    {
        return DB::transaction(function () use ($tramite, $nuevoEstadoCodigo, $operador, $comentario) {
            $state = TramiteState::desde($tramite);
            $state->transicionarA($nuevoEstadoCodigo, $operador, $comentario);

            return $tramite->fresh()->load('estadoActual', 'historial');
        });
    }

    /**
     * Crear apelación
     */
    public function crearApelacion(Tramite $tramiteOriginal, User $usuario): Tramite
    {
        if (!$tramiteOriginal->puedeApelar()) {
            throw new \Exception("Este trámite no puede ser apelado");
        }

        return DB::transaction(function () use ($tramiteOriginal, $usuario) {
            $estadoApelacion = \App\Models\FlujoEstado::where('codigo', 'EN_APELACION')->firstOrFail();

            $apelacion = Tramite::create([
                'codigo' => $this->generarCodigo($tramiteOriginal->convocatoria, 'APE'),
                'usuario_id' => $usuario->id,
                'convocatoria_id' => $tramiteOriginal->convocatoria_id,
                'estado_actual_id' => $estadoApelacion->id,
                'fecha_solicitud' => now(),
                'fecha_ultima_actualizacion' => now(),
                'es_apelacion' => true,
                'tramite_original_id' => $tramiteOriginal->id,
            ]);

            \App\Models\TramiteHistorial::create([
                'tramite_id' => $apelacion->id,
                'estado_anterior_id' => null,
                'estado_nuevo_id' => $estadoApelacion->id,
                'usuario_id' => $usuario->id,
                'fecha_cambio' => now(),
                'comentario' => 'Apelación creada',
            ]);

            return $apelacion;
        });
    }

    /**
     * Calcular estadísticas de tiempos
     */
    public function calcularEstadisticasTiempos(Convocatoria $convocatoria): array
    {
        $tramites = $convocatoria->tramites()
            ->whereHas('estadoActual', fn($q) => $q->where('es_final', true))
            ->with('historial')
            ->get();

        $estadisticas = [];

        foreach ($convocatoria->flujos as $flujo) {
            $tiempos = $tramites->map(function($tramite) use ($flujo) {
                return $tramite->historial
                    ->where('estado_nuevo_id', $flujo->estado_id)
                    ->first()
                    ?->tiempo_en_estado_anterior_min;
            })->filter()->values();

            if ($tiempos->isNotEmpty()) {
                $estadisticas[$flujo->estado->nombre] = [
                    'promedio_minutos' => round($tiempos->avg(), 2),
                    'minimo_minutos' => $tiempos->min(),
                    'maximo_minutos' => $tiempos->max(),
                    'total_tramites' => $tiempos->count(),
                ];
            }
        }

        return $estadisticas;
    }

    /**
     * Generar código único para trámite
     */
    private function generarCodigo(Convocatoria $convocatoria, string $prefijo = 'TRAM'): string
    {
        $anio = now()->year;
        $ultimo = Tramite::where('codigo', 'like', "{$prefijo}-{$anio}-%")
            ->orderBy('codigo', 'desc')
            ->first();

        $numero = $ultimo ? ((int) substr($ultimo->codigo, -5)) + 1 : 1;

        return sprintf("%s-%d-%05d", $prefijo, $anio, $numero);
    }
}
