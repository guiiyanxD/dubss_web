<?php

namespace App\States;

class EstadoEnRevision extends TramiteState
{
    public function getNombre(): string { return 'En Revisión'; }
    public function getCodigo(): string { return 'EN_REVISION'; }
    public function getColor(): string { return '#F59E0B'; } // amber
    public function permiteEdicion(): bool { return false; }
    public function esEstadoFinal(): bool { return false; }

    public function transicionesPermitidas(): array
    {
        return ['DOCUMENTOS_OBSERVADOS', 'REVISION_COMPLETADA', 'RECHAZADO'];
    }
}
