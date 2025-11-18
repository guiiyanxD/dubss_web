<?php

 namespace App\States;


class EstadoSolicitado extends TramiteState
{
    public function getNombre(): string { return 'Solicitado'; }
    public function getCodigo(): string { return 'SOLICITADO'; }
    public function getColor(): string { return '#3B82F6'; } // blue
    public function permiteEdicion(): bool { return true; }
    public function esEstadoFinal(): bool { return false; }

    public function transicionesPermitidas(): array
    {
        return ['RECEPCIONADO', 'RECHAZADO'];
    }
}


