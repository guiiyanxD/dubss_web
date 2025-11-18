<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Tramite;
use Livewire\WithPagination;
use App\Models\Convocatoria;
use App\Models\FlujoEstado;

class GestionTramites extends Component
{
    use WithPagination;

    public $busqueda = '';
    public $filtroEstado = '';
    public $filtroConvocatoria = '';
    public $ordenarPor = 'fecha_solicitud';
    public $ordenDireccion = 'desc';

    protected $queryString = [
        'busqueda' => ['except' => ''],
        'filtroEstado' => ['except' => ''],
        'filtroConvocatoria' => ['except' => ''],
    ];

    public function updatingBusqueda()
    {
        $this->resetPage();
    }

    public function limpiarFiltros()
    {
        $this->busqueda = '';
        $this->filtroEstado = '';
        $this->filtroConvocatoria = '';
        $this->resetPage();
    }

    public function asignarmeTraMite($tramiteId)
    {
        $tramite = Tramite::findOrFail($tramiteId);

        if (!$tramite->operador_asignado_id) {
            $tramite->update([
                'operador_asignado_id' => auth()->id(),
            ]);

            $this->dispatch('tramite-asignado', ['mensaje' => 'Trámite asignado correctamente']);
        }
    }

    public function render()
    {
        $tramites = Tramite::with(['usuario', 'estadoActual', 'convocatoria', 'operadorAsignado'])
            ->when($this->busqueda, function($query) {
                $query->where(function($q) {
                    $q->where('codigo', 'like', '%' . $this->busqueda . '%')
                      ->orWhereHas('usuario', function($qu) {
                          $qu->where('nombre', 'like', '%' . $this->busqueda . '%')
                             ->orWhere('apellido', 'like', '%' . $this->busqueda . '%')
                             ->orWhere('ci', 'like', '%' . $this->busqueda . '%');
                      });
                });
            })
            ->when($this->filtroEstado, function($query) {
                $query->where('estado_actual_id', $this->filtroEstado);
            })
            ->when($this->filtroConvocatoria, function($query) {
                $query->where('convocatoria_id', $this->filtroConvocatoria);
            })
            ->orderBy($this->ordenarPor, $this->ordenDireccion)
            ->paginate(15);

        $estados = FlujoEstado::all();
        $convocatorias = Convocatoria::all();

        return view('livewire.gestion-tramite', [
            'tramites' => $tramites,
            'estados' => $estados,
            'convocatorias' => $convocatorias,
        ]);
    }
}
