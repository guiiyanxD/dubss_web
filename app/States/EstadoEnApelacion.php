<?php

namespace App\States;

class EstadoEnApelacion extends TramiteState
{
    public function getNombre(): string { return 'En Apelación'; }
    public function getCodigo(): string { return 'EN_APELACION'; }
    public function getColor(): string { return '#F97316'; } // orange
    public function permiteEdicion(): bool { return false; }
    public function esEstadoFinal(): bool { return false; }

    public function transicionesPermitidas(): array
    {
        return ['APROBADO', 'RECHAZADO'];
    }
}
