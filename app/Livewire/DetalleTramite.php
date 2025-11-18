<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\TramiteService;
use App\Models\Tramite;
use App\Models\FlujoEstado;


class DetalleTramite extends Component
{
    public Tramite $tramite;
    public $mostrarModalCambioEstado = false;
    public $nuevoEstado = '';
    public $comentario = '';
    public $documentoSeleccionado = null;
    public $observacionDocumento = '';

    protected $rules = [
        'comentario' => 'nullable|string|max:500',
        'observacionDocumento' => 'required_if:accion,rechazar|string|max:500',
    ];

    public function mount(Tramite $tramite)
    {
        $this->tramite = $tramite->load([
            'usuario',
            'estadoActual',
            'convocatoria.requisitos',
            'documentos.requisito',
            'historial.estadoNuevo',
            'historial.usuario',
            'operadorAsignado',
        ]);
    }

    public function abrirModalCambioEstado()
    {
        $this->mostrarModalCambioEstado = true;
        $this->nuevoEstado = '';
        $this->comentario = '';
    }

    public function cambiarEstado()
    {
        $this->validate([
            'nuevoEstado' => 'required|exists:flujo_estados,codigo',
            'comentario' => 'nullable|string|max:500',
        ]);

        try {
            $service = new TramiteService();
            $service->transicionar(
                $this->tramite,
                $this->nuevoEstado,
                auth()->user(),
                $this->comentario
            );

            $this->mostrarModalCambioEstado = false;
            $this->dispatch('tramite-actualizado', ['mensaje' => 'Estado actualizado correctamente']);

            // Recargar trámite
            $this->tramite->refresh();

        } catch (\Exception $e) {
            $this->dispatch('error', ['mensaje' => $e->getMessage()]);
        }
    }

    public function aprobarDocumento($documentoId)
    {
        $documento = TramiteDocumento::findOrFail($documentoId);
        $documento->aprobar(auth()->user());

        $this->tramite->refresh();
        $this->dispatch('documento-actualizado', ['mensaje' => 'Documento aprobado']);
    }

    public function rechazarDocumento($documentoId)
    {
        $this->validate([
            'observacionDocumento' => 'required|string|max:500',
        ]);

        $documento = TramiteDocumento::findOrFail($documentoId);
        $documento->rechazar(auth()->user(), $this->observacionDocumento);

        $this->tramite->refresh();
        $this->documentoSeleccionado = null;
        $this->observacionDocumento = '';
        $this->dispatch('documento-actualizado', ['mensaje' => 'Documento rechazado']);
    }

    public function seleccionarDocumento($documentoId)
    {
        $this->documentoSeleccionado = $documentoId;
        $this->observacionDocumento = '';
    }

    public function getEstadosPosiblesProperty()
    {
        $state = \App\States\TramiteState::desde($this->tramite);
        $transicionesPermitidas = $state->transicionesPermitidas();

        return FlujoEstado::whereIn('codigo', $transicionesPermitidas)->get();
    }

    public function render()
    {
        return view('livewire.detalle-tramite');
    }
}
