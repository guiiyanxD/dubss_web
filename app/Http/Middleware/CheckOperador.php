<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckOperador
{
    /**
     * Verificar que el usuario sea operador o admin
     */
    public function handle(Request $request, Closure $next)
    {
        if (!$request->user() || !in_array($request->user()->rol, ['operador', 'admin'])) {
            abort(403, 'Acceso no autorizado. Solo operadores pueden acceder a esta sección.');
        }

        return $next($request);
    }
}
