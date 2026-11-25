<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Api\Comentario;
use App\Models\Api\Convenio;
use App\Models\Api\Notificacion;
use App\Models\Api\User;
use App\Support\FiltroOfensivo;
use Illuminate\Http\Request;

class ComentarioController extends Controller
{
    public function index(Request $request, Convenio $convenio)
    {
        $yo = $request->user()->id;

        return $convenio->comentarios()
            ->with('usuario:id,name,rol')
            ->orderBy('id')
            ->limit(200)
            ->get()
            ->map(fn (Comentario $c) => $this->formato($c, $yo));
    }

    public function store(Request $request, Convenio $convenio)
    {
        $datos = $request->validate(['texto' => 'required|string|max:500']);

        $texto = trim($datos['texto']);
        $ofensivo = FiltroOfensivo::contiene($texto);
        $autor = $request->user();

        $comentario = $convenio->comentarios()->create([
            'user_id' => $autor->id,
            'texto' => $ofensivo ? 'comentario ofensivo' : $texto,
            'ofensivo' => $ofensivo,
        ]);

        // Avisar al decano, al revisor y a quienes ya comentaron (menos al autor)
        $participantes = $convenio->comentarios()->pluck('user_id')
            ->push($convenio->revisor_id)->filter()->unique();

        User::where('rol', 'decano')
            ->orWhereIn('id', $participantes)
            ->pluck('id')
            ->reject(fn ($id) => $id === $autor->id)
            ->each(fn ($id) => Notificacion::create([
                'user_id' => $id,
                'convenio_id' => $convenio->id,
                'tipo' => 'comentario',
                'titulo' => 'Nuevo comentario',
                'mensaje' => "{$autor->name} comentó en el convenio {$convenio->codigo}.",
            ]));

        return response()->json($this->formato($comentario->load('usuario:id,name,rol'), $autor->id), 201);
    }

    private function formato(Comentario $c, int $yo): array
    {
        return [
            'id' => $c->id,
            'texto' => $c->texto,
            'ofensivo' => $c->ofensivo,
            'created_at' => $c->created_at,
            'es_mio' => $c->user_id === $yo,
            'usuario' => $c->usuario ? ['name' => $c->usuario->name, 'rol' => $c->usuario->rol] : null,
        ];
    }
}