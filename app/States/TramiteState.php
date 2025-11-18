<?php

namespace App\States;

use App\Models\Tramite;
use App\Models\Usuario;
use App\Exceptions\TransicionInvalidaException;

// app/States/TramiteState.php
abstract class TramiteState
{
    protected Tramite $tramite;

    public function __construct(Tramite $tramite)
    {
        $this->tramite = $tramite;
    }

    abstract public function getNombre(): string;
    abstract public function getCodigo(): string;
    abstract public function getColor(): string;
    abstract public function permiteEdicion(): bool;
    abstract public function esEstadoFinal(): bool;
    abstract public function transicionesPermitidas(): array;

    public function puedeTransicionarA(string $estadoCodigo): bool
    {
        return in_array($estadoCodigo, $this->transicionesPermitidas());
    }

    public function transicionarA(string $nuevoEstadoCodigo, Usuario $operador, ?string $comentario = null): void
    {
        if (!$this->puedeTransicionarA($nuevoEstadoCodigo)) {
            throw new TransicionInvalidaException(
                "No se puede transicionar de {$this->getCodigo()} a {$nuevoEstadoCodigo}"
            );
        }

        $estadoNuevo = \App\Models\FlujoEstado::where('codigo', $nuevoEstadoCodigo)->firstOrFail();

        // Calcular tiempo en estado anterior
        $ultimoHistorial = $this->tramite->historial()->latest('fecha_cambio')->first();
        $tiempoEnEstadoAnterior = $ultimoHistorial
            ? $ultimoHistorial->fecha_cambio->diffInMinutes(now())
            : 0;

        // Registrar en historial
        \App\Models\TramiteHistorial::create([
            'tramite_id' => $this->tramite->id,
            'estado_anterior_id' => $this->tramite->estado_actual_id,
            'estado_nuevo_id' => $estadoNuevo->id,
            'usuario_id' => $operador->id,
            'fecha_cambio' => now(),
            'tiempo_en_estado_anterior_min' => $tiempoEnEstadoAnterior,
            'comentario' => $comentario,
        ]);

        // Actualizar estado actual
        $this->tramite->update([
            'estado_actual_id' => $estadoNuevo->id,
            'fecha_ultima_actualizacion' => now(),
        ]);

        // Verificar si requiere notificación
        $flujo = $this->tramite->convocatoria->flujos()
            ->where('estado_id', $estadoNuevo->id)
            ->first();

        if ($flujo && $flujo->requiere_notificacion) {
            $this->enviarNotificacion($estadoNuevo, $comentario);
        }
    }

    protected function enviarNotificacion(\App\Models\FlujoEstado $estado, ?string $comentario): void
    {
        \App\Models\Notificacion::create([
            'usuario_id' => $this->tramite->usuario_id,
            'tramite_id' => $this->tramite->id,
            'titulo' => "Tu trámite cambió a: {$estado->nombre}",
            'mensaje' => $comentario ?? "Tu trámite {$this->tramite->codigo} ha sido actualizado.",
            'tipo' => $estado->es_final ? 'success' : 'info',
            'canal' => 'app',
        ]);
    }

    public static function desde(Tramite $tramite): TramiteState
    {
        $estadoCodigo = $tramite->estadoActual->codigo;

        return match($estadoCodigo) {
            'SOLICITADO' => new EstadoSolicitado($tramite),
            'RECEPCIONADO' => new EstadoRecepcionado($tramite),
            'EN_REVISION' => new EstadoEnRevision($tramite),
            'DOCUMENTOS_OBSERVADOS' => new EstadoDocumentosObservados($tramite),
            'REVISION_COMPLETADA' => new EstadoRevisionCompletada($tramite),
            'APROBADO' => new EstadoAprobado($tramite),
            'RECHAZADO' => new EstadoRechazado($tramite),
            'EN_APELACION' => new EstadoEnApelacion($tramite),
            default => throw new \Exception("Estado desconocido: {$estadoCodigo}"),
        };
    }
}
