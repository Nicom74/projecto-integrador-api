<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Api\Notificacion;
use Illuminate\Http\Request;

class NotificacionController extends Controller
{
    // Notificaciones del usuario autenticado, las más nuevas primero.
    public function index(Request $request)
    {
        $usuario = $request->user();
        $porPagina = min(max((int) $request->query('por_pagina', 20), 1), 50);

        $pagina = Notificacion::where('user_id', $usuario->id)
            ->when($request->boolean('no_leidas'), fn ($q) => $q->whereNull('leida_en'))
            ->orderByDesc('id')
            ->paginate($porPagina);

        return response()->json(array_merge($pagina->toArray(), [
            'no_leidas' => Notificacion::where('user_id', $usuario->id)->whereNull('leida_en')->count(),
        ]));
    }

    public function marcarLeida(Request $request, Notificacion $notificacion)
    {
        abort_unless((int) $notificacion->user_id === (int) $request->user()->id, 403);

        if ($notificacion->leida_en === null) {
            $notificacion->update(['leida_en' => now()]);
        }

        return response()->json($notificacion);
    }

    public function marcarTodas(Request $request)
    {
        Notificacion::where('user_id', $request->user()->id)
            ->whereNull('leida_en')
            ->update(['leida_en' => now()]);

        return response()->json(['ok' => true]);
    }
}