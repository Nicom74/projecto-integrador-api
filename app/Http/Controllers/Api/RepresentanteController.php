<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Api\Representante;
use Illuminate\Http\Request;

class RepresentanteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Representante::with('unidadResponsable')->paginate(10);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $datos = $request->validate([
            'nombre' => 'nullable|string|max:45',
            'apellido' => 'nullable|string|max:45',
            'cargo' => 'nullable|string|max:45',
            'dni' => 'nullable|string|max:45',
            'email' => 'nullable|email|max:45',
            'cuil' => 'nullable|string|max:45',
            'telefono' => 'nullable|string|max:45',
            'unidad_responsable_id' => 'required|exists:unidad_responsables,id',
        ]);

        return response()->json(Representante::create($datos), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Representante $representante)
    {
        return $representante->load('unidadResponsable', 'convenios:id,titulo,codigo');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Representante $representante)
    {
        $datos = $request->validate([
            'nombre' => 'sometimes|nullable|string|max:45',
            'apellido' => 'sometimes|nullable|string|max:45',
            'cargo' => 'sometimes|nullable|string|max:45',
            'dni' => 'sometimes|nullable|string|max:45',
            'email' => 'sometimes|nullable|email|max:45',
            'cuil' => 'sometimes|nullable|string|max:45',
            'telefono' => 'sometimes|nullable|string|max:45',
            'unidad_responsable_id' => 'sometimes|exists:unidad_responsables,id',
        ]);

        $representante->update($datos);

        return $representante->load('unidadResponsable');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Representante $representante)
    {
        $representante->delete();

        return response()->noContent();
    }
}
