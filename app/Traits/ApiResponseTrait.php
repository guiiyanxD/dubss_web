<?php

namespace App\Traits;

trait ApiResponseTrait
{
    /**
     * Respuesta exitosa
     */
    protected function successResponse($data = null, string $message = null, int $code = 200)
    {
        $response = [
            'success' => true,
        ];

        if ($message) {
            $response['message'] = $message;
        }

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $code);
    }

    /**
     * Respuesta de error
     */
    protected function errorResponse(string $message, int $code = 400, $errors = null)
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }

    /**
     * Respuesta de validación
     */
    protected function validationErrorResponse($errors)
    {
        return response()->json([
            'success' => false,
            'message' => 'Errores de validación',
            'errors' => $errors
        ], 422);
    }

    /**
     * Respuesta no encontrado
     */
    protected function notFoundResponse(string $message = 'Recurso no encontrado')
    {
        return response()->json([
            'success' => false,
            'message' => $message
        ], 404);
    }

    /**
     * Respuesta no autorizado
     */
    protected function unauthorizedResponse(string $message = 'No autorizado')
    {
        return response()->json([
            'success' => false,
            'message' => $message
        ], 401);
    }

    /**
     * Respuesta con paginación
     */
    protected function paginatedResponse($paginator, $resourceClass)
    {
        return response()->json([
            'success' => true,
            'data' => $resourceClass::collection($paginator->items()),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
            'links' => [
                'first' => $paginator->url(1),
                'last' => $paginator->url($paginator->lastPage()),
                'prev' => $paginator->previousPageUrl(),
                'next' => $paginator->nextPageUrl(),
            ]
        ]);
    }
}
