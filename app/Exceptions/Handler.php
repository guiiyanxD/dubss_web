<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * Registrar excepciones personalizadas para API
     */
    public function register()
    {
        $this->renderable(function (Throwable $e, $request) {
            if ($request->is('api/*')) {
                return $this->handleApiException($e, $request);
            }
        });
    }

    /**
     * Manejar excepciones en API
     */
    protected function handleApiException(Throwable $e, $request)
    {
        // Validación
        if ($e instanceof ValidationException) {
            return response()->json([
                'success' => false,
                'message' => 'Errores de validación',
                'errors' => $e->errors()
            ], 422);
        }

        // No autenticado
        if ($e instanceof AuthenticationException) {
            return response()->json([
                'success' => false,
                'message' => 'No autenticado. Por favor inicia sesión.'
            ], 401);
        }

        // Modelo no encontrado
        if ($e instanceof ModelNotFoundException) {
            return response()->json([
                'success' => false,
                'message' => 'Recurso no encontrado'
            ], 404);
        }

        // Ruta no encontrada
        if ($e instanceof NotFoundHttpException) {
            return response()->json([
                'success' => false,
                'message' => 'Endpoint no encontrado'
            ], 404);
        }

        // Método no permitido
        if ($e instanceof MethodNotAllowedHttpException) {
            return response()->json([
                'success' => false,
                'message' => 'Método HTTP no permitido'
            ], 405);
        }

        // Error genérico
        if (config('app.debug')) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }

        return response()->json([
            'success' => false,
            'message' => 'Error interno del servidor'
        ], 500);
    }
}
