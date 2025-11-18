<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tramite;
use App\Models\TramiteHistorial;
use App\Models\User;
use App\Models\Convocatoria;
use App\Models\FlujoEstado;
use App\Services\TramiteService;
use Carbon\Carbon;

class TramiteConHistorialSeeder extends Seeder
{
    /**
     * Crear trámites con historial completo para generar estadísticas
     */
    public function run(): void
    {
        $estudiantes = User::where('rol', 'estudiante')->get();
        $operador = User::where('rol', 'operador')->orWhere('rol', 'admin')->first();
        $convocatorias = Convocatoria::all(); // Cambiado: Tomar TODAS las convocatorias

        $this->command->info('Verificando datos...');
        $this->command->info("Estudiantes: {$estudiantes->count()}");
        $this->command->info("Operadores: " . ($operador ? 1 : 0));
        $this->command->info("Convocatorias: {$convocatorias->count()}");

        if ($estudiantes->isEmpty()) {
            $this->command->error('❌ No hay estudiantes. Ejecuta: php artisan db:seed --class=UsuarioSeeder');
            return;
        }

        if (!$operador) {
            $this->command->error('❌ No hay operadores. Ejecuta: php artisan db:seed --class=UsuarioSeeder');
            return;
        }

        if ($convocatorias->isEmpty()) {
            $this->command->error('❌ No hay convocatorias. Ejecuta: php artisan db:seed --class=ConvocatoriaSeeder');
            return;
        }

        $estados = FlujoEstado::orderBy('id')->get();

        if ($estados->isEmpty()) {
            $this->command->error('❌ No hay estados de flujo. Ejecuta: php artisan db:seed --class=FlujoEstadoSeeder');
            return;
        }

        $tramiteService = new TramiteService();

        $this->command->info('✅ Datos base correctos. Creando trámites...');

        // Crear 20 trámites de prueba
        foreach (range(1, 20) as $index) {
            try {
                $estudiante = $estudiantes->random();
                $convocatoria = $convocatorias->random();

                // Crear trámite
                $tramite = $tramiteService->crear($estudiante, $convocatoria);

                // Simular paso por diferentes estados con tiempos realistas
                $this->simularFlujoTramite($tramite, $operador, $index);

                $this->command->info("  → Trámite {$index}/20 creado: {$tramite->codigo}");

            } catch (\Exception $e) {
                $this->command->warn("  ⚠ Error en trámite {$index}: " . $e->getMessage());
            }
        }

        $this->command->info('✅ Proceso completado');
    }

    /**
     * Simular flujo de un trámite
     */
    private function simularFlujoTramite($tramite, $operador, $index)
    {
        // Definir flujo con tiempos realistas (en horas)
        $flujos = [
            ['estado' => 'RECEPCIONADO', 'tiempo_min' => 30, 'tiempo_max' => 180],      // 0.5 - 3 horas
            ['estado' => 'EN_REVISION', 'tiempo_min' => 240, 'tiempo_max' => 2880],     // 4 - 48 horas
            ['estado' => 'REVISION_COMPLETADA', 'tiempo_min' => 120, 'tiempo_max' => 720], // 2 - 12 horas
        ];

        // 70% terminan aprobados, 30% rechazados
        $estadoFinal = rand(1, 10) <= 7 ? 'APROBADO' : 'RECHAZADO';

        $fechaBase = now()->subDays(rand(5, 30)); // Trámites de hace 5-30 días

        try {
            foreach ($flujos as $flujoIndex => $paso) {
                // Calcular tiempo en estado anterior
                $tiempoEnEstadoAnterior = rand($paso['tiempo_min'], $paso['tiempo_max']);
                $fechaBase->addMinutes($tiempoEnEstadoAnterior);

                // Registrar cambio de estado manualmente (simulando)
                $estadoAnterior = $tramite->estadoActual;
                $estadoNuevo = FlujoEstado::where('codigo', $paso['estado'])->first();

                // Actualizar trámite
                $tramite->update([
                    'estado_actual_id' => $estadoNuevo->id,
                    'fecha_ultima_actualizacion' => $fechaBase,
                    'operador_asignado_id' => $operador->id,
                ]);

                // Crear registro en historial
                TramiteHistorial::create([
                    'tramite_id' => $tramite->id,
                    'estado_anterior_id' => $estadoAnterior->id,
                    'estado_nuevo_id' => $estadoNuevo->id,
                    'usuario_id' => $operador->id,
                    'fecha_cambio' => $fechaBase,
                    'tiempo_en_estado_anterior_min' => $tiempoEnEstadoAnterior,
                    'comentario' => $this->generarComentario($paso['estado']),
                ]);
            }

            // Estado final
            $tiempoFinal = rand(60, 480); // 1-8 horas
            $fechaBase->addMinutes($tiempoFinal);

            $estadoAnterior = $tramite->estadoActual;
            $estadoNuevo = FlujoEstado::where('codigo', $estadoFinal)->first();

            $tramite->update([
                'estado_actual_id' => $estadoNuevo->id,
                'fecha_ultima_actualizacion' => $fechaBase,
            ]);

            TramiteHistorial::create([
                'tramite_id' => $tramite->id,
                'estado_anterior_id' => $estadoAnterior->id,
                'estado_nuevo_id' => $estadoNuevo->id,
                'usuario_id' => $operador->id,
                'fecha_cambio' => $fechaBase,
                'tiempo_en_estado_anterior_min' => $tiempoFinal,
                'comentario' => $estadoFinal === 'APROBADO'
                    ? 'Trámite aprobado. Todos los requisitos cumplidos.'
                    : 'Trámite rechazado. Documentación incompleta.',
            ]);

        } catch (\Exception $e) {
            $this->command->warn("Error en trámite {$tramite->codigo}: " . $e->getMessage());
        }
    }

    /**
     * Generar comentario según estado
     */
    private function generarComentario($estado): string
    {
        return match($estado) {
            'RECEPCIONADO' => 'Documentos recepcionados en ventanilla',
            'EN_REVISION' => 'Iniciando revisión de documentos',
            'DOCUMENTOS_OBSERVADOS' => 'Documentos con observaciones',
            'REVISION_COMPLETADA' => 'Revisión técnica completada',
            default => 'Cambio de estado',
        };
    }
}
