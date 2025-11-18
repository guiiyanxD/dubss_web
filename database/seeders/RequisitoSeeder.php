<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Requisito;

class RequisitoSeeder extends Seeder
{
    public function run(): void
    {
        $requisitos = [
            // Documentos comunes
            [
                'nombre' => 'Fotocopia de CI',
                'codigo' => 'DOC_CI',
                'descripcion' => 'Fotocopia simple de Cédula de Identidad vigente',
                'es_comun' => true,
                'tipo_archivo' => 'pdf,image',
                'tamano_max_mb' => 2,
            ],
            [
                'nombre' => 'Certificado de Notas',
                'codigo' => 'DOC_CERT_NOTAS',
                'descripcion' => 'Certificado de notas del último semestre cursado',
                'es_comun' => true,
                'tipo_archivo' => 'pdf',
                'tamano_max_mb' => 5,
            ],
            [
                'nombre' => 'Carnet Universitario',
                'codigo' => 'DOC_CARNET',
                'descripcion' => 'Fotocopia del carnet universitario vigente',
                'es_comun' => true,
                'tipo_archivo' => 'pdf,image',
                'tamano_max_mb' => 2,
            ],

            // Becas socioeconómicas
            [
                'nombre' => 'Certificado de Ingresos Familiares',
                'codigo' => 'DOC_INGRESOS',
                'descripcion' => 'Certificado de ingresos económicos del grupo familiar',
                'es_comun' => false,
                'tipo_archivo' => 'pdf',
                'tamano_max_mb' => 3,
            ],
            [
                'nombre' => 'Factura de Servicios Básicos',
                'codigo' => 'DOC_SERVICIOS',
                'descripcion' => 'Factura de luz, agua o gas del domicilio',
                'es_comun' => false,
                'tipo_archivo' => 'pdf,image',
                'tamano_max_mb' => 2,
            ],

            // Becas académicas
            [
                'nombre' => 'Carta de Motivación',
                'codigo' => 'DOC_CARTA_MOTIV',
                'descripcion' => 'Carta explicando motivación para la beca',
                'es_comun' => false,
                'tipo_archivo' => 'pdf',
                'tamano_max_mb' => 3,
            ],
            [
                'nombre' => 'Certificado de Excelencia Académica',
                'codigo' => 'DOC_EXCELENCIA',
                'descripcion' => 'Certificado de rendimiento académico destacado',
                'es_comun' => false,
                'tipo_archivo' => 'pdf',
                'tamano_max_mb' => 3,
            ],
            [
                'nombre' => 'Propuesta de Investigación',
                'codigo' => 'DOC_PROPUESTA_INV',
                'descripcion' => 'Propuesta de proyecto de investigación científica',
                'es_comun' => false,
                'tipo_archivo' => 'pdf',
                'tamano_max_mb' => 10,
            ],

            // Extensión universitaria
            [
                'nombre' => 'Plan de Actividades',
                'codigo' => 'DOC_PLAN_ACT',
                'descripcion' => 'Plan detallado de actividades de extensión',
                'es_comun' => false,
                'tipo_archivo' => 'pdf',
                'tamano_max_mb' => 5,
            ],

            // Internado rotatorio
            [
                'nombre' => 'Certificado Médico',
                'codigo' => 'DOC_CERT_MEDICO',
                'descripcion' => 'Certificado médico de aptitud',
                'es_comun' => false,
                'tipo_archivo' => 'pdf',
                'tamano_max_mb' => 3,
            ],
            [
                'nombre' => 'Carta de Aceptación del Centro',
                'codigo' => 'DOC_ACEPTACION_CENTRO',
                'descripcion' => 'Carta de aceptación del centro de salud',
                'es_comun' => false,
                'tipo_archivo' => 'pdf',
                'tamano_max_mb' => 3,
            ],
        ];

        foreach ($requisitos as $requisito) {
            Requisito::create($requisito);
        }
    }
}
