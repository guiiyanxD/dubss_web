<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TurnoService;

class MarcarTurnosVencidos extends Command
{
    protected $signature = 'turnos:marcar-vencidos';
    protected $description = 'Marcar turnos pasados como vencidos';

    public function handle(TurnoService $turnoService)
    {
        $cantidad = $turnoService->marcarTurnosVencidos();

        $this->info("Se marcaron {$cantidad} turnos como vencidos.");

        return 0;
    }
}
