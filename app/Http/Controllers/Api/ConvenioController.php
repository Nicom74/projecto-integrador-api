<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Api\Convenio;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ConvenioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filtros = $request->validate([
            'q' => 'nullable|string|max:100',
            'estado' => 'nullable|string|max:45',
            'tipo_instrumento_id' => 'nullable|integer|exists:tipo_instrumentos,id',
            'fecha_desde' => 'nullable|date',
            'fecha_hasta' => 'nullable|date|after_or_equal:fecha_desde',
            'orden' => 'nullable|in:fecha_creacion,titulo,estado,codigo',
            'direccion' => 'nullable|in:asc,desc',
            'por_pagina' => 'nullable|integer|min:1|max:50',
        ]);

        return Convenio::with('tipoInstrumento')
            ->filtrar($filtros)
            ->orderBy($filtros['orden'] ?? 'fecha_creacion', $filtros['direccion'] ?? 'desc')
            ->paginate($filtros['por_pagina'] ?? 10)
            ->withQueryString();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $datos = $request->validate([
            'fecha_creacion'      => 'required|date',
            'titulo'              => 'required|string|max:45',
            'codigo'              => 'required|string|max:45|unique:convenios,codigo',
            'firma_gestor'        => 'nullable|string|max:45',
            'firma_decano'        => 'nullable|string|max:45',
            'convenio_padre_id'   => 'nullable|exists:convenios,id',
            'tipo_instrumento_id' => 'required|exists:tipo_instrumentos,id',
        ]);

        $convenio = Convenio::create($datos + ['estado' => 'borrador']);

        return response()->json($convenio->load('tipoInstrumento'), 201);    
    }

    /**
     * Display the specified resource.
     */
    public function show(Convenio $convenio)
    {
        return $convenio->load([
            'tipoInstrumento',
            'representantes',
            'documentos',
            'historialEstados',
            'detallePasantia',
            'adendas',
        ]);
    }

    /**
     * Update the specified resource in storage.
     */

    public function update(Request $request, Convenio $convenio)
    {
        $datos = $request->validate([
            'fecha_creacion' => 'sometimes|date',
            'titulo' => 'sometimes|string|max:45',
            'codigo' => 'sometimes|string|max:45|unique:convenios,codigo,' . $convenio->id,
            'firma_gestor' => 'nullable|string|max:45',
            'firma_decano' => 'nullable|string|max:45',
            'convenio_padre_id' => 'nullable|exists:convenios,id',
            'tipo_instrumento_id' => 'sometimes|exists:tipo_instrumentos,id',

            // cambio de estado
            'estado' => ['sometimes', Rule::in(array_keys(Convenio::TRANSICIONES))],
            'fundamento' => 'nullable|string|max:45',
            'documento_id' => ['nullable', Rule::exists('documentos', 'id')->where('convenio_id', $convenio->id)],
        ]);

        $convenio = DB::transaction(function () use ($request, $convenio, $datos) {
            // Bloquea la fila para evitar dos cambios simultáneos
            $convenio = Convenio::lockForUpdate()->findOrFail($convenio->id);
            $anterior = $convenio->estado;
            $cambiaEstado = isset($datos['estado']) && $datos['estado'] !== $anterior;

            if ($cambiaEstado) {
                if (! in_array($datos['estado'], Convenio::TRANSICIONES[$anterior] ?? [])) {
                    throw ValidationException::withMessages([
                        'estado' => ["No se puede pasar de '{$anterior}' a '{$datos['estado']}'."],
                    ]);
                }

                if (empty($datos['fundamento'])) {
                    throw ValidationException::withMessages([
                        'fundamento' => ['El fundamento es obligatorio al cambiar el estado.'],
                    ]);
                }

                if ($datos['estado'] === 'aprobado' && $convenio->representantes()->count() < 2) {
                    throw ValidationException::withMessages([
                        'estado' => ['Para aprobar el convenio deben existir al menos dos partes.'],
                    ]);
                }
            }

            $convenio->update(Arr::except($datos, ['fundamento', 'documento_id']));

            if ($cambiaEstado) {
                $convenio->historialEstados()->create([
                    'user_id'         => $request->user()->id,
                    'documento_id'    => $datos['documento_id'] ?? null,
                    'fundamento'      => $datos['fundamento'],
                    'fecha_cambio'    => now(),
                    'estado_anterior' => $anterior,
                    'estado_nuevo'    => $datos['estado'],
                ]);
            }

            return $convenio;
        });

        return $convenio->load('tipoInstrumento');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Convenio $convenio)
    {

        if ($convenio->adendas()->exists()) {
            return response()->json(['message' => 'No se puede eliminar: tiene adendas asociadas.'], 409);
        }

        $convenio->delete();

        return response()->noContent();
    }
}
