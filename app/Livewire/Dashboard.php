<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Tramite;
use App\Models\Convocatoria;

class Dashboard extends Component
{
    public $estadisticas = [];
    public $convocatoriaSeleccionada;

    public function mount()
    {
        $this->cargarEstadisticas();
    }

    public function cargarEstadisticas()
    {
        $this->estadisticas = [
            'total_tramites_hoy' => Tramite::whereDate('created_at', today())->count(),
            'pendientes_revision' => Tramite::whereHas('estadoActual', function($q) {
                $q->where('codigo', 'EN_REVISION');
            })->count(),
            'aprobados_hoy' => Tramite::whereHas('estadoActual', function($q) {
                $q->where('codigo', 'APROBADO');
            })->whereDate('updated_at', today())->count(),
            'rechazados_hoy' => Tramite::whereHas('estadoActual', function($q) {
                $q->where('codigo', 'RECHAZADO');
            })->whereDate('updated_at', today())->count(),
            'tiempo_promedio_procesamiento' => $this->calcularTiempoPromedio(),
        ];
    }

    private function calcularTiempoPromedio()
    {
        $tramitesFinalizados = Tramite::whereHas('estadoActual', function($q) {
            $q->where('es_final', true);
        })->whereDate('updated_at', '>=', today()->subDays(7))->get();

        if ($tramitesFinalizados->isEmpty()) {
            return 0;
        }

        $tiempos = $tramitesFinalizados->map(function($tramite) {
            return $tramite->tiempoTotalProcesamiento();
        })->filter();

        return $tiempos->avg() ?? 0;
    }

    public function render()
    {
        $convocatoriasActivas = Convocatoria::activas()->get();

        return view('livewire.dashboard', [
            'convocatorias' => $convocatoriasActivas,
        ]);
    }
}
