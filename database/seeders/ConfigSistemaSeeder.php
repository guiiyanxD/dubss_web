<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ConfigSistema;

class ConfigSistemaSeeder extends Seeder
{
    public function run(): void
    {
        $configs = [
            [
                'clave' => 'duracion_turno_minutos',
                'valor' => '15',
                'tipo' => 'int',
                'descripcion' => 'Duración de cada turno en minutos',
            ],
            [
                'clave' => 'hora_inicio_turnos',
                'valor' => '07:00',
                'tipo' => 'string',
                'descripcion' => 'Hora de inicio para reserva de turnos',
            ],
            [
                'clave' => 'hora_fin_turnos',
                'valor' => '11:45',
                'tipo' => 'string',
                'descripcion' => 'Hora de fin para reserva de turnos',
            ],
            [
                'clave' => 'dias_anticipacion_turno',
                'valor' => '7',
                'tipo' => 'int',
                'descripcion' => 'Días de anticipación máxima para reservar turno',
            ],
            [
                'clave' => 'notificaciones_habilitadas',
                'valor' => 'true',
                'tipo' => 'boolean',
                'descripcion' => 'Habilitar/deshabilitar notificaciones',
            ],
        ];

        foreach ($configs as $config) {
            ConfigSistema::create($config);
        }
    }
}
