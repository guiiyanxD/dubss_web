<?php

namespace App\States;

class EstadoDocumentosObservados extends TramiteState
{
    public function getNombre(): string { return 'Documentos Observados'; }
    public function getCodigo(): string { return 'DOCUMENTOS_OBSERVADOS'; }
    public function getColor(): string { return '#EF4444'; } // red
    public function permiteEdicion(): bool { return true; }
    public function esEstadoFinal(): bool { return false; }

    public function transicionesPermitidas(): array
    {
        // El estudiante puede corregir y volver a revisión
        return ['EN_REVISION', 'RECHAZADO'];
    }
}
