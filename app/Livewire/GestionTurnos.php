<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\TramiteService;
use Aoo\Models\Convocatoria;
use Aoo\Models\Turno;
use Livewire\WithPagination;
use Illuminate\Support\Carbon;

class GestionTurnos extends Component
{
    use WithPagination;

    public $fechaSeleccionada;
    public $filtroEstado = '';
    public $estadisticas = [];
    public $mostrarModalReporte = false;

    public function mount()
    {
        $this->fechaSeleccionada = today()->format('Y-m-d');
        $this->cargarEstadisticas();
    }

    public function updatedFechaSeleccionada()
    {
        $this->cargarEstadisticas();
        $this->resetPage();
    }

    public function cargarEstadisticas()
    {
        $service = new \App\Services\TurnoService();
        $this->estadisticas = $service->generarReporte($this->fechaSeleccionada);
    }

    public function atenderTurno($turnoId)
    {
        try {
            $turno = \App\Models\Turno::findOrFail($turnoId);
            $service = new \App\Services\TurnoService();

            $service->atenderTurno($turno, auth()->id());

            // Si tiene trámite asociado, cambiar a RECEPCIONADO
            if ($turno->tramites->isNotEmpty()) {
                $tramiteService = new \App\Services\TramiteService();
                foreach ($turno->tramites as $tramite) {
                    if ($tramite->estadoActual->codigo === 'SOLICITADO') {
                        $tramiteService->transicionar(
                            $tramite,
                            'RECEPCIONADO',
                            auth()->user(),
                            'Documentos recepcionados en turno ' . $turno->codigo
                        );
                    }
                }
            }

            $this->cargarEstadisticas();
            $this->dispatch('turno-atendido', ['mensaje' => 'Turno marcado como atendido']);

        } catch (\Exception $e) {
            $this->dispatch('error', ['mensaje' => $e->getMessage()]);
        }
    }

    public function cancelarTurno($turnoId)
    {
        try {
            $turno = \App\Models\Turno::findOrFail($turnoId);
            $service = new \App\Services\TurnoService();

            $service->cancelarTurno($turno);

            $this->cargarEstadisticas();
            $this->dispatch('turno-cancelado', ['mensaje' => 'Turno cancelado']);

        } catch (\Exception $e) {
            $this->dispatch('error', ['mensaje' => $e->getMessage()]);
        }
    }

    public function liberarTurno($turnoId)
    {
        try {
            $turno = \App\Models\Turno::findOrFail($turnoId);
            $service = new \App\Services\TurnoService();

            $service->liberarTurno($turno);

            $this->cargarEstadisticas();
            $this->dispatch('turno-cancelado', ['mensaje' => 'Turno liberado']);

        } catch (\Exception $e) {
            $this->dispatch('error', ['mensaje' => $e->getMessage()]);
        }
    }

    public function cambiarFecha($direccion)
    {
        $fecha = Carbon::parse($this->fechaSeleccionada);

        if ($direccion === 'anterior') {
            $this->fechaSeleccionada = $fecha->subDay()->format('Y-m-d');
        } else {
            $this->fechaSeleccionada = $fecha->addDay()->format('Y-m-d');
        }

        $this->cargarEstadisticas();
    }

    public function render()
    {
        $turnos = \App\Models\Turno::with(['usuario', 'atendidoPor', 'tramites'])
            ->where('fecha', $this->fechaSeleccionada)
            ->when($this->filtroEstado, function($query) {
                $query->where('estado', $this->filtroEstado);
            })
            ->orderBy('hora_inicio')
            ->paginate(20);

        $service = new \App\Services\TurnoService();
        $slotsDisponibles = $service->generarTurnosDisponibles($this->fechaSeleccionada);

        return view('livewire.gestion-turnos', [
            'turnos' => $turnos,
            'slotsDisponibles' => $slotsDisponibles,
        ]);
    }
}
