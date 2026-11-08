<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Api\PersonaVinculada;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PersonaVinculadaController extends Controller
{
    public function index(Request $request)
    {
        $f = $request->validate([
            'q' => 'nullable|string|max:100',
            'tipo_persona' => ['nullable', Rule::in(PersonaVinculada::TIPOS)],
            'por_pagina' => 'nullable|integer|min:1|max:50',
        ]);

        return PersonaVinculada::query()
            ->when($f['q'] ?? null, function ($q, $texto) {
                $q->where(function ($q) use ($texto) {
                    $q->where('nombre', 'like', "%{$texto}%")
                      ->orWhere('apellido', 'like', "%{$texto}%")
                      ->orWhere('dni', 'like', "%{$texto}%");
                });
            })
            ->when($f['tipo_persona'] ?? null, fn ($q, $tipo) => $q->where('tipo_persona', $tipo))
            ->orderBy('apellido')
            ->orderBy('nombre')
            ->paginate($f['por_pagina'] ?? 15)
            ->withQueryString();
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'nombre' => 'required|string|max:45',
            'apellido' => 'required|string|max:45',
            'dni' => 'required|digits_between:7,8|unique:persona_vinculadas,dni',
            'email' => 'required|email|max:45',
            'tipo_persona' => ['required', Rule::in(PersonaVinculada::TIPOS)],
        ]);

        return response()->json(PersonaVinculada::create($datos), 201);
    }

    public function show(PersonaVinculada $persona)
    {
        return response()->json($persona->toArray() + ['en_uso' => $persona->enUso()]);
    }

    public function update(Request $request, PersonaVinculada $persona)
    {
        $datos = $request->validate([
            'nombre' => 'sometimes|string|max:45',
            'apellido' => 'sometimes|string|max:45',
            'dni' => ['sometimes', 'digits_between:7,8', Rule::unique('persona_vinculadas', 'dni')->ignore($persona->id)],
            'email' => 'sometimes|email|max:45',
            'tipo_persona' => ['sometimes', Rule::in(PersonaVinculada::TIPOS)],
        ]);

        if (isset($datos['tipo_persona'])
            && $datos['tipo_persona'] !== $persona->tipo_persona
            && $persona->enUso()) {
            return response()->json(
                ['message' => 'No se puede cambiar el tipo: la persona figura en una pasantía.'],
                409
            );
        }

        $persona->update($datos);

        return $persona;
    }

    public function destroy(PersonaVinculada $persona)
    {
        if ($persona->enUso()) {
            return response()->json(
                ['message' => 'No se puede eliminar: la persona figura en una pasantía.'],
                409
            );
        }

        $persona->delete();

        return response()->noContent();
    }
}