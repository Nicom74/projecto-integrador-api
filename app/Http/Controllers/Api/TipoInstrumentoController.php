<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Api\TipoInstrumento;
use Illuminate\Http\Request;

class TipoInstrumentoController extends Controller
{
    public function index(Request $request)
    {
        return TipoInstrumento::query()
            ->when($request->query('categoria'), fn ($q, $c) => $q->where('categoria', $c))
            ->orderBy('categoria')
            ->orderBy('nombre')
            ->get();
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'nombre' => 'required|string|max:45',
            'categoria' => 'required|string|max:45',
        ]);

        return response()->json(TipoInstrumento::create($datos), 201);
    }

    public function show(TipoInstrumento $tipoInstrumento)
    {
        return $tipoInstrumento;
    }

    public function update(Request $request, TipoInstrumento $tipoInstrumento)
    {
        $datos = $request->validate([
            'nombre' => 'sometimes|string|max:45',
            'categoria' => 'sometimes|string|max:45',
        ]);

        $tipoInstrumento->update($datos);

        return $tipoInstrumento;
    }

    public function destroy(TipoInstrumento $tipoInstrumento)
    {
        if ($tipoInstrumento->convenios()->exists()) {
            return response()->json(
                ['message' => 'No se puede eliminar: hay convenios que usan este tipo.'],
                409
            );
        }

        $tipoInstrumento->delete();

        return response()->noContent();
    }
}