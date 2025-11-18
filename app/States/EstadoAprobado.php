<?php

namespace App\States;

class EstadoAprobado extends TramiteState
{
    public function getNombre(): string { return 'Aprobado'; }
    public function getCodigo(): string { return 'APROBADO'; }
    public function getColor(): string { return '#059669'; } // green-600
    public function permiteEdicion(): bool { return false; }
    public function esEstadoFinal(): bool { return true; }

    public function transicionesPermitidas(): array
    {
        return []; // Estado final
    }
}
