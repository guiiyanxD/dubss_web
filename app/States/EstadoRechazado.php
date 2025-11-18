<?php

namespace App\States;

class EstadoRechazado extends TramiteState
{
    public function getNombre(): string { return 'Rechazado'; }
    public function getCodigo(): string { return 'RECHAZADO'; }
    public function getColor(): string { return '#DC2626'; } // red-600
    public function permiteEdicion(): bool { return false; }
    public function esEstadoFinal(): bool { return true; }

    public function transicionesPermitidas(): array
    {
        // Solo puede apelar si está dentro del plazo
        if ($this->tramite->puedeApelar()) {
            return ['EN_APELACION'];
        }
        return [];
    }
}
