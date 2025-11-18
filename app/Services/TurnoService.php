<?php

namespace App\Services;

use App\Models\Turno;
use App\Models\ConfigSistema;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class TurnoService
{
    /**
     * Generar turnos disponibles para una fecha específica
     */
    public function generarTurnosDisponibles(string $fecha): Collection
    {
        $fechaCarbon = Carbon::parse($fecha);

        // Validar que no sea fin de semana (configurable)
        if ($this->esDiaNoLaboral($fechaCarbon)) {
            return collect([]);
        }

        // Obtener configuración
        $horaInicio = ConfigSistema::obtener('hora_inicio_turnos', '07:00');
        $horaFin = ConfigSistema::obtener('hora_fin_turnos', '11:45');
        $duracionMinutos = ConfigSistema::obtener('duracion_turno_minutos', 15);

        // Obtener turnos ya ocupados para esa fecha
        $turnosOcupados = Turno::where('fecha', $fecha)
            ->whereIn('estado', ['reservado', 'atendido'])
            ->pluck('hora_inicio')
            ->toArray();

        // Generar slots
        $slots = collect();
        $inicio = Carbon::parse($fecha . ' ' . $horaInicio);
        $fin = Carbon::parse($fecha . ' ' . $horaFin);

        while ($inicio->lt($fin)) {
            $horaInicioSlot = $inicio->format('H:i');
            $horaFinSlot = $inicio->copy()->addMinutes($duracionMinutos)->format('H:i');

            $slots->push([
                'fecha' => $fecha,
                'hora_inicio' => $horaInicioSlot,
                'hora_fin' => $horaFinSlot,
                'disponible' => !in_array($horaInicioSlot, $turnosOcupados),
                'en_pasado' => Carbon::parse($fecha . ' ' . $horaInicioSlot)->isPast(),
            ]);

            $inicio->addMinutes($duracionMinutos);
        }

        return $slots;
    }

    /**
     * Reservar un turno
     */
    public function reservarTurno(
        int $usuarioId,
        string $fecha,
        string $horaInicio,
        string $horaFin
    ): Turno {
        // Validar disponibilidad
        $turnoExistente = Turno::where('fecha', $fecha)
            ->where('hora_inicio', $horaInicio)
            ->whereIn('estado', ['reservado', 'atendido'])
            ->first();

        if ($turnoExistente) {
            throw new \Exception('Este turno ya no está disponible');
        }

        // Validar que el usuario no tenga un turno activo
        $turnoUsuario = Turno::where('usuario_id', $usuarioId)
            ->whereIn('estado', ['reservado'])
            ->where('fecha', '>=', now()->toDateString())
            ->first();

        if ($turnoUsuario) {
            throw new \Exception('Ya tienes un turno reservado para el ' .
                $turnoUsuario->fecha->format('d/m/Y'));
        }

        // Validar que no sea en el pasado
        if (Carbon::parse($fecha . ' ' . $horaInicio)->isPast()) {
            throw new \Exception('No se pueden reservar turnos en el pasado');
        }

        // Generar código único
        $codigo = $this->generarCodigoTurno($fecha);

        // Crear turno
        $turno = Turno::create([
            'usuario_id' => $usuarioId,
            'fecha' => $fecha,
            'hora_inicio' => $horaInicio,
            'hora_fin' => $horaFin,
            'codigo' => $codigo,
            'estado' => 'reservado',
            'duracion_minutos' => ConfigSistema::obtener('duracion_turno_minutos', 15),
        ]);

        return $turno;
    }

    /**
     * Cancelar turno
     */
    public function cancelarTurno(Turno $turno): bool
    {
        if (!$turno->puedeSerCancelado()) {
            throw new \Exception('No se puede cancelar este turno');
        }

        $turno->update(['estado' => 'cancelado']);

        return true;
    }

    /**
     * Marcar turno como atendido
     */
    public function atenderTurno(Turno $turno, int $operadorId): Turno
    {
        if ($turno->estado !== 'reservado') {
            throw new \Exception('Este turno no está en estado reservado');
        }

        $turno->update([
            'estado' => 'atendido',
            'atendido_por' => $operadorId,
            'atendido_en' => now(),
        ]);

        return $turno;
    }

    /**
     * Obtener turnos del día
     */
    public function turnosDelDia(string $fecha = null): Collection
    {
        $fecha = $fecha ?? now()->toDateString();

        return Turno::with(['usuario', 'atendidoPor'])
            ->where('fecha', $fecha)
            ->orderBy('hora_inicio')
            ->get();
    }

    /**
     * Obtener estadísticas de turnos
     */
    public function estadisticasTurnos(string $fechaInicio = null, string $fechaFin = null): array
    {
        $fechaInicio = $fechaInicio ?? now()->startOfMonth()->toDateString();
        $fechaFin = $fechaFin ?? now()->endOfMonth()->toDateString();

        $turnos = Turno::whereBetween('fecha', [$fechaInicio, $fechaFin])->get();

        return [
            'total' => $turnos->count(),
            'reservados' => $turnos->where('estado', 'reservado')->count(),
            'atendidos' => $turnos->where('estado', 'atendido')->count(),
            'cancelados' => $turnos->where('estado', 'cancelado')->count(),
            'vencidos' => $turnos->where('estado', 'vencido')->count(),
            'tasa_asistencia' => $turnos->where('estado', 'reservado')->count() > 0
                ? round(($turnos->where('estado', 'atendido')->count() /
                    ($turnos->where('estado', 'reservado')->count() +
                     $turnos->where('estado', 'atendido')->count())) * 100, 2)
                : 0,
        ];
    }

    /**
     * Marcar turnos vencidos automáticamente
     */
    public function marcarTurnosVencidos(): int
    {
        $ahora = now();
        $fechaHoy = $ahora->toDateString();
        $horaActual = $ahora->format('H:i:s');

        // Marcar turnos de días anteriores
        $vencidosAnteriores = Turno::where('estado', 'reservado')
            ->where('fecha', '<', $fechaHoy)
            ->update(['estado' => 'vencido']);

        // Marcar turnos del día actual que ya pasaron
        $vencidosHoy = Turno::where('estado', 'reservado')
            ->where('fecha', $fechaHoy)
            ->where('hora_fin', '<', $horaActual)
            ->update(['estado' => 'vencido']);

        return $vencidosAnteriores + $vencidosHoy;
    }

    /**
     * Generar código único para turno
     */
    private function generarCodigoTurno(string $fecha): string
    {
        $fechaFormateada = Carbon::parse($fecha)->format('Ymd');
        $ultimo = Turno::where('codigo', 'like', "TURN-{$fechaFormateada}-%")
            ->orderBy('codigo', 'desc')
            ->first();

        $numero = $ultimo
            ? ((int) substr($ultimo->codigo, -3)) + 1
            : 1;

        return sprintf("TURN-%s-%03d", $fechaFormateada, $numero);
    }

    /**
     * Verificar si es día no laboral
     */
    private function esDiaNoLaboral(Carbon $fecha): bool
    {
        // Obtener días laborables desde configuración
        $diasLaborables = ConfigSistema::obtener('dias_laborables', '1,2,3,4,5'); // Lun-Vie
        $diasArray = explode(',', $diasLaborables);

        // Carbon: 1=Lunes, 7=Domingo
        $diaSemana = $fecha->dayOfWeekIso;

        if (!in_array($diaSemana, $diasArray)) {
            return true;
        }

        // Verificar festivos (opcional)
        $festivos = ConfigSistema::obtener('dias_festivos', '[]');
        $festivosArray = json_decode($festivos, true) ?? [];

        return in_array($fecha->format('Y-m-d'), $festivosArray);
    }

    /**
     * Obtener fechas disponibles (próximos N días laborables)
     */
    public function fechasDisponibles(int $dias = 7): Collection
    {
        $fechas = collect();
        $fecha = Carbon::today();
        $diasAnticipacion = ConfigSistema::obtener('dias_anticipacion_turno', 7);
        $fechaLimite = Carbon::today()->addDays($diasAnticipacion);

        $contador = 0;
        while ($fecha->lte($fechaLimite) && $contador < $dias) {
            if (!$this->esDiaNoLaboral($fecha)) {
                $turnos = $this->generarTurnosDisponibles($fecha->format('Y-m-d'));
                $disponibles = $turnos->where('disponible', true)
                    ->where('en_pasado', false)
                    ->count();

                if ($disponibles > 0) {
                    $fechas->push([
                        'fecha' => $fecha->format('Y-m-d'),
                        'dia_semana' => $fecha->locale('es')->dayName,
                        'dia_mes' => $fecha->format('d/m'),
                        'turnos_disponibles' => $disponibles,
                        'es_hoy' => $fecha->isToday(),
                        'es_manana' => $fecha->isTomorrow(),
                    ]);
                    $contador++;
                }
            }
            $fecha->addDay();
        }

        return $fechas;
    }

    /**
     * Liberar turno (para cuando usuario no asiste)
     */
    public function liberarTurno(Turno $turno): bool
    {
        if ($turno->estado !== 'reservado') {
            throw new \Exception('Solo se pueden liberar turnos reservados');
        }

        $turno->update(['estado' => 'cancelado']);

        return true;
    }

    /**
     * Generar reporte de turnos
     */
    public function generarReporte(string $fecha): array
    {
        $turnos = $this->turnosDelDia($fecha);
        $slots = $this->generarTurnosDisponibles($fecha);

        return [
            'fecha' => $fecha,
            'total_slots' => $slots->count(),
            'slots_ocupados' => $turnos->whereIn('estado', ['reservado', 'atendido'])->count(),
            'slots_disponibles' => $slots->where('disponible', true)->where('en_pasado', false)->count(),
            'turnos_atendidos' => $turnos->where('estado', 'atendido')->count(),
            'turnos_pendientes' => $turnos->where('estado', 'reservado')->count(),
            'turnos_cancelados' => $turnos->where('estado', 'cancelado')->count(),
            'tasa_ocupacion' => $slots->count() > 0
                ? round(($turnos->whereIn('estado', ['reservado', 'atendido'])->count() / $slots->count()) * 100, 2)
                : 0,
        ];
    }
}
