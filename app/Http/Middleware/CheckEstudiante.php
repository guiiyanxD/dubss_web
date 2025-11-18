<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckEstudiante
{
    /**
     * Verificar que el usuario sea estudiante
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->user() && $request->user()->rol !== 'estudiante') {
            return response()->json([
                'success' => false,
                'message' => 'Acceso no autorizado. Solo estudiantes pueden acceder a este recurso.'
            ], 403);
        }

        return $next($request);
    }
}
