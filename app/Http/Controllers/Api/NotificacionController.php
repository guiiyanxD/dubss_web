<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


class NotificacionController extends Controller
{
    /**
     * Obtener notificaciones del usuario
     */
    public function index(Request $request)
    {
        $notificaciones = $request->user()->notificaciones()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $notificaciones->map(function($notif) {
                return [
                    'id' => $notif->id,
                    'titulo' => $notif->titulo,
                    'mensaje' => $notif->mensaje,
                    'tipo' => $notif->tipo,
                    'leida' => $notif->leida,
                    'tramite_id' => $notif->tramite_id,
                    'fecha' => $notif->created_at->format('Y-m-d H:i:s'),
                ];
            }),
            'meta' => [
                'current_page' => $notificaciones->currentPage(),
                'total' => $notificaciones->total(),
                'per_page' => $notificaciones->perPage(),
            ]
        ]);
    }

    /**
     * Marcar notificación como leída
     */
    public function marcarLeida(Request $request, $id)
    {
        $notificacion = $request->user()->notificaciones()->findOrFail($id);
        $notificacion->marcarComoLeida();

        return response()->json([
            'success' => true,
            'message' => 'Notificación marcada como leída'
        ]);
    }

    /**
     * Marcar todas como leídas
     */
    public function marcarTodasLeidas(Request $request)
    {
        $request->user()->notificaciones()
            ->where('leida', false)
            ->update([
                'leida' => true,
                'leida_en' => now()
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Todas las notificaciones marcadas como leídas'
        ]);
    }

    /**
     * Contar notificaciones no leídas
     */
    public function noLeidas(Request $request)
    {
        $cantidad = $request->user()->notificaciones()->noLeidas()->count();

        return response()->json([
            'success' => true,
            'data' => [
                'cantidad' => $cantidad
            ]
        ]);
    }
}

