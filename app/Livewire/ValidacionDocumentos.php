<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Tramite;
use App\Models\TramiteDocumento;
use App\Services\TramiteService;

class ValidacionDocumentos extends Component
{
    use WithPagination;

    public $tramiteActual;
    public $documentos = [];
    public $indiceActual = 0;
    public $observaciones = [];

    public function mount()
    {
        $this->cargarSiguienteTramite();
    }

    public function cargarSiguienteTramite()
    {
        $this->tramiteActual = Tramite::whereHas('estadoActual', function($q) {
                $q->where('codigo', 'RECEPCIONADO');
            })
            ->with(['documentos.requisito', 'usuario', 'convocatoria'])
            ->where(function($q) {
                $q->whereNull('operador_asignado_id')
                  ->orWhere('operador_asignado_id', auth()->id());
            })
            ->oldest('fecha_solicitud')
            ->first();

        if ($this->tramiteActual) {
            // Asignar automáticamente
            if (!$this->tramiteActual->operador_asignado_id) {
                $this->tramiteActual->update([
                    'operador_asignado_id' => auth()->id(),
                ]);
            }

            $this->documentos = $this->tramiteActual->documentos->toArray();
            $this->indiceActual = 0;
            $this->observaciones = [];
        }
    }

    public function aprobarDocumentoActual()
    {
        if (!isset($this->documentos[$this->indiceActual])) {
            return;
        }

        $documento = TramiteDocumento::find($this->documentos[$this->indiceActual]['id']);
        $documento->aprobar(auth()->user());

        $this->siguienteDocumento();
    }

    public function rechazarDocumentoActual()
    {
        $this->validate([
            'observaciones.' . $this->indiceActual => 'required|string|max:500',
        ], [
            'observaciones.' . $this->indiceActual . '.required' => 'Debes especificar el motivo del rechazo',
        ]);

        if (!isset($this->documentos[$this->indiceActual])) {
            return;
        }

        $documento = TramiteDocumento::find($this->documentos[$this->indiceActual]['id']);
        $documento->rechazar(auth()->user(), $this->observaciones[$this->indiceActual]);

        $this->siguienteDocumento();
    }

    public function siguienteDocumento()
    {
        $this->indiceActual++;

        if ($this->indiceActual >= count($this->documentos)) {
            // Terminó la revisión de todos los documentos
            $this->finalizarRevisionTramite();
        }
    }

    public function anteriorDocumento()
    {
        if ($this->indiceActual > 0) {
            $this->indiceActual--;
        }
    }

    private function finalizarRevisionTramite()
    {
        // Verificar si todos fueron aprobados
        $todosAprobados = $this->tramiteActual->documentos()
            ->where('estado', 'aprobado')
            ->count() === $this->tramiteActual->documentos()->count();

        $service = new TramiteService();

        if ($todosAprobados) {
            $service->transicionar(
                $this->tramiteActual,
                'REVISION_COMPLETADA',
                auth()->user(),
                'Todos los documentos fueron aprobados'
            );
        } else {
            $service->transicionar(
                $this->tramiteActual,
                'DOCUMENTOS_OBSERVADOS',
                auth()->user(),
                'Hay documentos con observaciones que deben corregirse'
            );
        }

        $this->dispatch('tramite-revisado', ['mensaje' => 'Revisión completada']);

        // Cargar siguiente trámite
        $this->cargarSiguienteTramite();
    }

    public function saltarTramite()
    {
        // Liberar asignación
        if ($this->tramiteActual && $this->tramiteActual->operador_asignado_id === auth()->id()) {
            $this->tramiteActual->update([
                'operador_asignado_id' => null,
            ]);
        }

        $this->cargarSiguienteTramite();
    }

    public function render()
    {
        return view('livewire.validacion-documentos');
    }
}
