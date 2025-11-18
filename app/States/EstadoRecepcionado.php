<?php
namespace App\States;

class EstadoRecepcionado extends TramiteState
{
    public function getNombre(): string { return 'Recepcionado'; }
    public function getCodigo(): string { return 'RECEPCIONADO'; }
    public function getColor(): string { return '#8B5CF6'; } // purple
    public function permiteEdicion(): bool { return false; }
    public function esEstadoFinal(): bool { return false; }

    public function transicionesPermitidas(): array
    {
        return ['EN_REVISION', 'RECHAZADO'];
    }
}
