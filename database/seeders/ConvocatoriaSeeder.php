<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Convocatoria;
use App\Models\ConvocatoriaFlujo;
use App\Models\FlujoEstado;
use App\Models\Requisito;


class ConvocatoriaSeeder extends Seeder
{
    public function run(): void
    {
        // Beca de Estudio 2025
        $becaEstudio = Convocatoria::create([
            'nombre' => 'Becas Socioeconómicas - Estudio 2025-1',
            'codigo' => 'BECA-EST-2025-1',
            'tipo_beca' => 'socioeconomica',
            'subtipo' => 'estudio',
            'descripcion' => 'Beca para estudiantes de bajos recursos económicos',
            'fecha_inicio' => now()->addDays(5),
            'fecha_fin' => now()->addDays(35),
            'fecha_limite_apelacion' => now()->addDays(50),
            'activa' => true,
            'version' => '1.0.0',
        ]);

        // Configurar flujo
        $this->configurarFlujoBasico($becaEstudio);

        // Asociar requisitos
        $becaEstudio->requisitos()->attach([
            Requisito::where('codigo', 'DOC_CI')->first()->id => [
                'es_obligatorio' => true,
                'orden' => 1,
                'instrucciones_especificas' => 'Ambas caras en una sola hoja',
            ],
            Requisito::where('codigo', 'DOC_CERT_NOTAS')->first()->id => [
                'es_obligatorio' => true,
                'orden' => 2,
                'instrucciones_especificas' => 'Debe ser del último semestre',
            ],
            Requisito::where('codigo', 'DOC_CARNET')->first()->id => [
                'es_obligatorio' => true,
                'orden' => 3,
                'instrucciones_especificas' => null,
            ],
            Requisito::where('codigo', 'DOC_INGRESOS')->first()->id => [
                'es_obligatorio' => true,
                'orden' => 4,
                'instrucciones_especificas' => 'Sellado por autoridad competente',
            ],
            Requisito::where('codigo', 'DOC_SERVICIOS')->first()->id => [
                'es_obligatorio' => true,
                'orden' => 5,
                'instrucciones_especificas' => 'No mayor a 3 meses',
            ],
        ]);

        // Beca Académica - Investigación 2025
        $becaInvestigacion = Convocatoria::create([
            'nombre' => 'Becas Académicas - Investigación Científica 2025-1',
            'codigo' => 'BECA-INV-2025-1',
            'tipo_beca' => 'academica',
            'subtipo' => 'investigacion',
            'descripcion' => 'Beca para estudiantes con proyectos de investigación científica',
            'fecha_inicio' => now()->addDays(10),
            'fecha_fin' => now()->addDays(40),
            'fecha_limite_apelacion' => now()->addDays(55),
            'activa' => true,
            'version' => '1.0.0',
        ]);

        $this->configurarFlujoBasico($becaInvestigacion);

        $becaInvestigacion->requisitos()->attach([
            Requisito::where('codigo', 'DOC_CI')->first()->id => [
                'es_obligatorio' => true,
                'orden' => 1,
                'instrucciones_especificas' => null,
            ],
            Requisito::where('codigo', 'DOC_CERT_NOTAS')->first()->id => [
                'es_obligatorio' => true,
                'orden' => 2,
                'instrucciones_especificas' => null,
            ],
            Requisito::where('codigo', 'DOC_CARNET')->first()->id => [
                'es_obligatorio' => true,
                'orden' => 3,
                'instrucciones_especificas' => null,
            ],
            Requisito::where('codigo', 'DOC_PROPUESTA_INV')->first()->id => [
                'es_obligatorio' => true,
                'orden' => 4,
                'instrucciones_especificas' => 'Máximo 20 páginas, incluir bibliografía',
            ],
            Requisito::where('codigo', 'DOC_CARTA_MOTIV')->first()->id => [
                'es_obligatorio' => true,
                'orden' => 5,
                'instrucciones_especificas' => null,
            ],
        ]);

        // Beca Alimentación 2025
        $becaAlimentacion = Convocatoria::create([
            'nombre' => 'Becas Socioeconómicas - Alimentación 2025-1',
            'codigo' => 'BECA-ALI-2025-1',
            'tipo_beca' => 'socioeconomica',
            'subtipo' => 'alimentacion',
            'descripcion' => 'Beca de alimentación para el comedor universitario',
            'fecha_inicio' => now()->addDays(3),
            'fecha_fin' => now()->addDays(30),
            'fecha_limite_apelacion' => now()->addDays(45),
            'activa' => true,
            'version' => '1.0.0',
        ]);

        $this->configurarFlujoBasico($becaAlimentacion);

        $becaAlimentacion->requisitos()->attach([
            Requisito::where('codigo', 'DOC_CI')->first()->id => [
                'es_obligatorio' => true,
                'orden' => 1,
                'instrucciones_especificas' => null,
            ],
            Requisito::where('codigo', 'DOC_CARNET')->first()->id => [
                'es_obligatorio' => true,
                'orden' => 2,
                'instrucciones_especificas' => null,
            ],
            Requisito::where('codigo', 'DOC_INGRESOS')->first()->id => [
                'es_obligatorio' => true,
                'orden' => 3,
                'instrucciones_especificas' => null,
            ],
        ]);
    }

    private function configurarFlujoBasico(Convocatoria $convocatoria): void
    {
        $flujo = [
            ['codigo' => 'SOLICITADO', 'orden' => 1, 'tiempo' => 1, 'siguiente' => 'RECEPCIONADO'],
            ['codigo' => 'RECEPCIONADO', 'orden' => 2, 'tiempo' => 2, 'siguiente' => 'EN_REVISION'],
            ['codigo' => 'EN_REVISION', 'orden' => 3, 'tiempo' => 48, 'siguiente' => 'REVISION_COMPLETADA'],
            ['codigo' => 'DOCUMENTOS_OBSERVADOS', 'orden' => 4, 'tiempo' => 24, 'siguiente' => 'EN_REVISION'],
            ['codigo' => 'REVISION_COMPLETADA', 'orden' => 5, 'tiempo' => 12, 'siguiente' => 'APROBADO'],
            ['codigo' => 'APROBADO', 'orden' => 6, 'tiempo' => 0, 'siguiente' => null],
            ['codigo' => 'RECHAZADO', 'orden' => 7, 'tiempo' => 0, 'siguiente' => null],
            ['codigo' => 'EN_APELACION', 'orden' => 8, 'tiempo' => 72, 'siguiente' => 'APROBADO'],
        ];

        foreach ($flujo as $item) {
            $estado = FlujoEstado::where('codigo', $item['codigo'])->first();
            $estadoSiguiente = $item['siguiente']
                ? FlujoEstado::where('codigo', $item['siguiente'])->first()
                : null;

            ConvocatoriaFlujo::create([
                'convocatoria_id' => $convocatoria->id,
                'estado_id' => $estado->id,
                'estado_siguiente_id' => $estadoSiguiente?->id,
                'orden' => $item['orden'],
                'tiempo_estimado_horas' => $item['tiempo'],
                'requiere_notificacion' => true,
            ]);
        }
    }
}
