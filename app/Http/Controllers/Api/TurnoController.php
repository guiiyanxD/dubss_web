<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TurnoController extends Controller
{
    /**
     * Obtener turnos disponibles para una fecha
     */
    public function disponibles(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fecha' => 'required|date|after_or_equal:today',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $fecha = $request->fecha;
        $turnosOcupados = \App\Models\Turno::where('fecha', $fecha)
            ->whereIn('estado', ['reservado', 'atendido'])
            ->get();

        // Generar slots disponibles
        $horaInicio = \App\Models\ConfigSistema::obtener('hora_inicio_turnos', '07:00');
        $horaFin = \App\Models\ConfigSistema::obtener('hora_fin_turnos', '11:45');
        $duracion = \App\Models\ConfigSistema::obtener('duracion_turno_minutos', 15);

        $slots = [];
        $inicio = \Carbon\Carbon::parse($fecha . ' ' . $horaInicio);
        $fin = \Carbon\Carbon::parse($fecha . ' ' . $horaFin);

        while ($inicio->lt($fin)) {
            $horaInicioSlot = $inicio->format('H:i');
            $horaFinSlot = $inicio->copy()->addMinutes($duracion)->format('H:i');

            // Verificar si está ocupado
            $ocupado = $turnosOcupados->contains(function($turno) use ($horaInicioSlot) {
                return $turno->hora_inicio === $horaInicioSlot;
            });

            if (!$ocupado) {
                $slots[] = [
                    'hora_inicio' => $horaInicioSlot,
                    'hora_fin' => $horaFinSlot,
                    'disponible' => true,
                ];
            }

            $inicio->addMinutes($duracion);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'fecha' => $fecha,
                'slots' => $slots
            ]
        ]);
    }

    /**
     * Reservar un turno
     */
    public function reservar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fecha' => 'required|date|after_or_equal:today',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Verificar que el usuario no tenga ya un turno activo
        $turnoExistente = \App\Models\Turno::where('usuario_id', $request->user()->id)
            ->whereIn('estado', ['reservado'])
            ->where('fecha', '>=', now()->toDateString())
            ->first();

        if ($turnoExistente) {
            return response()->json([
                'success' => false,
                'message' => 'Ya tienes un turno reservado para el ' . $turnoExistente->fecha->format('d/m/Y')
            ], 400);
        }

        // Verificar disponibilidad
        $turnoOcupado = \App\Models\Turno::where('fecha', $request->fecha)
            ->where('hora_inicio', $request->hora_inicio)
            ->whereIn('estado', ['reservado', 'atendido'])
            ->exists();

        if ($turnoOcupado) {
            return response()->json([
                'success' => false,
                'message' => 'Este turno ya no está disponible'
            ], 400);
        }

        // Generar código único
        $codigo = 'TURN-' . now()->format('Ymd') . '-' . str_pad(\App\Models\Turno::whereDate('created_at', today())->count() + 1, 3, '0', STR_PAD_LEFT);

        $turno = \App\Models\Turno::create([
            'usuario_id' => $request->user()->id,
            'fecha' => $request->fecha,
            'hora_inicio' => $request->hora_inicio,
            'hora_fin' => $request->hora_fin,
            'codigo' => $codigo,
            'estado' => 'reservado',
            'duracion_minutos' => \App\Models\ConfigSistema::obtener('duracion_turno_minutos', 15),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Turno reservado exitosamente',
            'data' => [
                'id' => $turno->id,
                'codigo' => $turno->codigo,
                'fecha' => $turno->fecha->format('Y-m-d'),
                'hora_inicio' => $turno->hora_inicio,
                'hora_fin' => $turno->hora_fin,
                'estado' => $turno->estado,
            ]
        ], 201);
    }

    /**
     * Mis turnos
     */
    public function misTurnos(Request $request)
    {
        $turnos = $request->user()->turnos()
            ->orderBy('fecha', 'desc')
            ->orderBy('hora_inicio', 'desc')
            ->get()
            ->map(function($turno) {
                return [
                    'id' => $turno->id,
                    'codigo' => $turno->codigo,
                    'fecha' => $turno->fecha->format('Y-m-d'),
                    'hora_inicio' => $turno->hora_inicio,
                    'hora_fin' => $turno->hora_fin,
                    'estado' => $turno->estado,
                    'puede_cancelar' => $turno->puedeSerCancelado(),
                    'esta_vencido' => $turno->estaVencido(),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $turnos
        ]);
    }

    /**
     * Cancelar turno
     */
    public function cancelar(Request $request, $id)
    {
        $turno = \App\Models\Turno::where('usuario_id', $request->user()->id)
            ->findOrFail($id);

        if (!$turno->puedeSerCancelado()) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede cancelar este turno'
            ], 400);
        }

        $turno->update(['estado' => 'cancelado']);

        return response()->json([
            'success' => true,
            'message' => 'Turno cancelado exitosamente'
        ]);
    }
}
