<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Api\Convenio;

class ParteController extends Controller
{
    public function index(Convenio $convenio)
    {
        return $convenio->partes()
            ->with('representante.unidadResponsable')
            ->get();
    }

    public function store(Request $request, Convenio $convenio)
    {
        $datos = $request->validate([
            'representante_id' => [
                'required',
                'exists:representantes,id',
                // no repetir el mismo representante en el mismo convenio
                Rule::unique('partes', 'representante_id')->where('convenio_id', $convenio->id),
            ],
        ]);

        $parte = $convenio->partes()->create($datos);

        return response()->json($parte->load('representante.unidadResponsable'), 201);
    }

    public function destroy(Convenio $convenio, Parte $parte)
    {
        $parte->delete();

        return response()->noContent();
    }
}
