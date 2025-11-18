<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Turno;
use App\Models\User;
use Carbon\Carbon;

class TurnoSeeder extends Seeder
{
    /**
     * Crear turnos de prueba
     */
    public function run(): void
    {
        // Obtener estudiantes
        $estudiantes = User::where('rol', 'estudiante')->get();

        if ($estudiantes->isEmpty()) {
            $this->command->warn('No hay estudiantes en la BD. Ejecuta primero UsuarioSeeder.');
            return;
        }

        // Crear turnos para HOY
        $this->crearTurnosParaDia(Carbon::today(), $estudiantes, 'hoy');

        // Crear turnos para MAÑANA
        $this->crearTurnosParaDia(Carbon::tomorrow(), $estudiantes, 'mañana');

        // Crear turnos para PASADO MAÑANA
        $this->crearTurnosParaDia(Carbon::today()->addDays(2), $estudiantes, 'pasado mañana');

        $this->command->info('✅ Turnos de prueba creados correctamente');
    }

    /**
     * Crear turnos para un día específico
     */
    private function crearTurnosParaDia(Carbon $fecha, $estudiantes, string $label): void
    {
        $horaInicio = Carbon::parse($fecha->format('Y-m-d') . ' 07:00');
        $horaFin = Carbon::parse($fecha->format('Y-m-d') . ' 11:45');
        $duracion = 15; // minutos

        $contador = 1;
        $totalTurnos = 0;

        while ($horaInicio->lt($horaFin)) {
            $horaInicioStr = $horaInicio->format('H:i');
            $horaFinStr = $horaInicio->copy()->addMinutes($duracion)->format('H:i');

            // Decidir estado del turno (simulación realista)
            $random = rand(1, 10);

            if ($random <= 3) {
                // 30% sin reservar (disponible)
                $horaInicio->addMinutes($duracion);
                continue;
            }

            $estado = match(true) {
                $fecha->isPast() => 'atendido',              // Pasado: atendido
                $fecha->isToday() && $horaInicio->isPast() => 'atendido', // Hoy pasado: atendido
                $random <= 7 => 'reservado',                 // 40% reservado
                default => 'atendido',                       // 30% atendido
            };

            // Seleccionar estudiante aleatorio
            $estudiante = $estudiantes->random();

            // Código único
            $codigo = sprintf("TURN-%s-%03d", $fecha->format('Ymd'), $contador);

            // Crear turno
            $turno = Turno::create([
                'usuario_id' => $estudiante->id,
                'fecha' => $fecha->format('Y-m-d'),
                'hora_inicio' => $horaInicioStr,
                'hora_fin' => $horaFinStr,
                'codigo' => $codigo,
                'estado' => $estado,
                'duracion_minutos' => $duracion,
                'atendido_por' => $estado === 'atendido' ? User::where('rol', 'operador')->first()?->id : null,
                'atendido_en' => $estado === 'atendido' ? $fecha->copy()->setTimeFromTimeString($horaInicioStr) : null,
            ]);

            $horaInicio->addMinutes($duracion);
            $contador++;
            $totalTurnos++;
        }

        $this->command->info("  → {$totalTurnos} turnos creados para {$label} ({$fecha->format('d/m/Y')})");
    }
}
