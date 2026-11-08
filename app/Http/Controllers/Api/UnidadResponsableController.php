<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Api\UnidadResponsable;
use Illuminate\Http\Request;

class UnidadResponsableController extends Controller
{
    public function index()
    {
        return UnidadResponsable::orderBy('nombre')->get();
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'nombre' => 'required|string|max:45',
            'tipo' => 'required|string|max:45',
        ]);

        return response()->json(UnidadResponsable::create($datos), 201);
    }

    public function show(UnidadResponsable $unidadResponsable)
    {
        return $unidadResponsable->load('representantes:id,nombre,email,unidad_responsable_id');
    }

    public function update(Request $request, UnidadResponsable $unidadResponsable)
    {
        $datos = $request->validate([
            'nombre' => 'sometimes|string|max:45',
            'tipo' => 'sometimes|string|max:45',
        ]);

        $unidadResponsable->update($datos);

        return $unidadResponsable;
    }

    public function destroy(UnidadResponsable $unidadResponsable)
    {
        if ($unidadResponsable->representantes()->exists()) {
            return response()->json(
                ['message' => 'No se puede eliminar: la unidad tiene representantes asociados.'],
                409
            );
        }

        $unidadResponsable->delete();

        return response()->noContent();
    }
}