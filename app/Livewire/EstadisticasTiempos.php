<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\TramiteService;
use App\Models\Convocatoria;

class EstadisticasTiempos extends Component
{
    public $convocatoriaSeleccionada;
    public $estadisticas = [];
    public $rangoFechas = '30'; // últimos 30 días por defecto

    public function mount()
    {
        $this->convocatoriaSeleccionada = Convocatoria::activas()->first()?->id;
        $this->cargarEstadisticas();
    }

    public function updatedConvocatoriaSeleccionada()
    {
        $this->cargarEstadisticas();
    }

    public function updatedRangoFechas()
    {
        $this->cargarEstadisticas();
    }

    public function cargarEstadisticas()
    {
        if (!$this->convocatoriaSeleccionada) {
            return;
        }

        $convocatoria = Convocatoria::findOrFail($this->convocatoriaSeleccionada);
        $service = new TramiteService();

        $this->estadisticas = $service->calcularEstadisticasTiempos($convocatoria);
    }

    public function render()
    {
        $convocatorias = Convocatoria::all();

        return view('livewire.estadisticas-tiempos', [
            'convocatorias' => $convocatorias,
        ]);
    }
}

