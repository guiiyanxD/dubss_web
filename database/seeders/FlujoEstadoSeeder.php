<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
//use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Seeder;
use App\Models\FlujoEstado;
//use App\Models\HasFactory;

class FlujoEstadoSeeder extends Seeder
{
    public function run(): void
    {
        $estados = [
            [
                'nombre' => 'Solicitado',
                'codigo' => 'SOLICITADO',
                'color_hex' => '#3B82F6',
                'es_inicial' => true,
                'es_final' => false,
                'permite_edicion' => true,
                'descripcion' => 'El trámite ha sido creado y está esperando turno',
            ],
            [
                'nombre' => 'Recepcionado',
                'codigo' => 'RECEPCIONADO',
                'color_hex' => '#8B5CF6',
                'es_inicial' => false,
                'es_final' => false,
                'permite_edicion' => false,
                'descripcion' => 'Los documentos fueron recibidos en ventanilla',
            ],
            [
                'nombre' => 'En Revisión',
                'codigo' => 'EN_REVISION',
                'color_hex' => '#F59E0B',
                'es_inicial' => false,
                'es_final' => false,
                'permite_edicion' => false,
                'descripcion' => 'Los documentos están siendo revisados por el operador',
            ],
            [
                'nombre' => 'Documentos Observados',
                'codigo' => 'DOCUMENTOS_OBSERVADOS',
                'color_hex' => '#EF4444',
                'es_inicial' => false,
                'es_final' => false,
                'permite_edicion' => true,
                'descripcion' => 'Hay observaciones en los documentos que deben corregirse',
            ],
            [
                'nombre' => 'Revisión Completada',
                'codigo' => 'REVISION_COMPLETADA',
                'color_hex' => '#10B981',
                'es_inicial' => false,
                'es_final' => false,
                'permite_edicion' => false,
                'descripcion' => 'La revisión técnica ha sido completada exitosamente',
            ],
            [
                'nombre' => 'Aprobado',
                'codigo' => 'APROBADO',
                'color_hex' => '#059669',
                'es_inicial' => false,
                'es_final' => true,
                'permite_edicion' => false,
                'descripcion' => 'El trámite ha sido aprobado',
            ],
            [
                'nombre' => 'Rechazado',
                'codigo' => 'RECHAZADO',
                'color_hex' => '#DC2626',
                'es_inicial' => false,
                'es_final' => true,
                'permite_edicion' => false,
                'descripcion' => 'El trámite ha sido rechazado',
            ],
            [
                'nombre' => 'En Apelación',
                'codigo' => 'EN_APELACION',
                'color_hex' => '#F97316',
                'es_inicial' => false,
                'es_final' => false,
                'permite_edicion' => false,
                'descripcion' => 'El trámite rechazado está en proceso de apelación',
            ],
        ];

        foreach ($estados as $estado) {
            FlujoEstado::create($estado);
        }
    }
}
