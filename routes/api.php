<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ConvocatoriaController;
use App\Http\Controllers\Api\TurnoController;
use App\Http\Controllers\Api\TramiteController;
use App\Http\Controllers\Api\NotificacionController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


// Rutas públicas
Route::prefix('v1')->group(function () {

    // Autenticación
    Route::post('/registro', [AuthController::class, 'registro']);
    Route::post('/login', [AuthController::class, 'login']);

    // Convocatorias (público - solo lectura)
    Route::get('/convocatorias', [ConvocatoriaController::class, 'index']);
    Route::get('/convocatorias/{id}', [ConvocatoriaController::class, 'show']);

    Route::prefix('turnos')->group(function () {
        Route::get('/disponibles', [TurnoController::class, 'disponibles']);
        Route::post('/reservar', [TurnoController::class, 'reservar']);
        Route::get('/mis-turnos', [TurnoController::class, 'misTurnos']);
        Route::put('/{id}/cancelar', [TurnoController::class, 'cancelar']);
    });
});

// Rutas protegidas (requieren autenticación)
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {

    // ====================================
    // Autenticación y Perfil
    // ====================================
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/perfil', [AuthController::class, 'perfil']);
    Route::put('/perfil', [AuthController::class, 'actualizarPerfil']);

    // ====================================
    // Turnos
    // ====================================


    // ====================================
    // Trámites
    // ====================================
    Route::prefix('tramites')->group(function () {
        Route::get('/', [TramiteController::class, 'index']);
        Route::post('/', [TramiteController::class, 'store']);
        Route::get('/{id}', [TramiteController::class, 'show']);
        Route::post('/{id}/documentos', [TramiteController::class, 'subirDocumento']);
        Route::post('/{id}/apelar', [TramiteController::class, 'apelar']);
    });

    // ====================================
    // Notificaciones
    // ====================================
    Route::prefix('notificaciones')->group(function () {
        Route::get('/', [NotificacionController::class, 'index']);
        Route::get('/no-leidas', [NotificacionController::class, 'noLeidas']);
        Route::put('/{id}/leer', [NotificacionController::class, 'marcarLeida']);
        Route::put('/leer-todas', [NotificacionController::class, 'marcarTodasLeidas']);
    });
});

// Ruta de prueba
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toIso8601String(),
        'version' => '1.0.0'
    ]);
});
