<?php
namespace App\States;

class EstadoRevisionCompletada extends TramiteState
{
    public function getNombre(): string { return 'Revisión Completada'; }
    public function getCodigo(): string { return 'REVISION_COMPLETADA'; }
    public function getColor(): string { return '#10B981'; } // green
    public function permiteEdicion(): bool { return false; }
    public function esEstadoFinal(): bool { return false; }

    public function transicionesPermitidas(): array
    {
        return ['APROBADO', 'RECHAZADO'];
    }
}
