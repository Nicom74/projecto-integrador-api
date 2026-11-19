<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Api\Convenio;
use App\Models\Api\Representante;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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
            'vence_antes' => 'nullable|date',
            'por_pagina' => 'nullable|integer|min:1|max:50',
        ]);

        return Convenio::with(['tipoInstrumento', 'unidad', 'revisor', 'historialEstados'])
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
            'titulo' => 'required|string|max:255',
            'fecha_vencimiento' => 'nullable|date',
            'convenio_padre_id' => 'nullable|exists:convenios,id',
            'tipo_instrumento_id' => 'required|exists:tipo_instrumentos,id',
            'unidad_id' => 'required|exists:unidad_responsables,id',
            'unidad_responsable_secundaria_id' => 'required|integer|exists:unidad_responsables,id|different:unidad_id',
            'representantes' => 'sometimes|array',
            'representantes.*.nombre' => 'nullable|string|max:45',
            'representantes.*.apellido' => 'nullable|string|max:45',
            'representantes.*.cargo' => 'nullable|string|max:45',
            'representantes.*.email' => 'nullable|email|max:45',
            'representantes.*.dni' => 'nullable|string|max:45',
            'representantes.*.cuil' => 'nullable|string|max:45',
            'detalle_pasantia' => 'sometimes|array',
            'detalle_pasantia.fecha_inicio_pasantia' => 'nullable|date',
            'detalle_pasantia.fecha_fin_pasantia' => [
                'nullable',
                'date',
                'after_or_equal:detalle_pasantia.fecha_inicio_pasantia',
            ],
            'detalle_pasantia.carga_horaria_semanal' => 'nullable|numeric|min:0',
            'detalle_pasantia.asignacion_estimulo' => 'nullable|string|max:45',
        ]);

        $convenio = DB::transaction(function () use ($datos) {
            $convenio = Convenio::create([
                'fecha_creacion' => now()->toDateString(),
                'titulo' => $datos['titulo'],
                'codigo' => sprintf('CONV-%s-%s', now()->format('Y'), Str::upper(Str::random(8))),
                'estado' => 'borrador',
                'fecha_vencimiento' => $datos['fecha_vencimiento'] ?? null,
                'convenio_padre_id' => $datos['convenio_padre_id'] ?? null,
                'tipo_instrumento_id' => $datos['tipo_instrumento_id'],
                'unidad_responsable_id' => $datos['unidad_id'],
                'unidad_responsable_secundaria_id' => $datos['unidad_responsable_secundaria_id'],
            ]);

            foreach ($datos['representantes'] ?? [] as $representante) {
                if (! collect($representante)->contains(fn ($valor) => filled($valor))) {
                    continue;
                }

                $nuevoRepresentante = Representante::create([
                    'nombre' => $representante['nombre'] ?? null,
                    'apellido' => $representante['apellido'] ?? null,
                    'cargo' => $representante['cargo'] ?? null,
                    'email' => $representante['email'] ?? null,
                    'dni' => $representante['dni'] ?? null,
                    'cuil' => $representante['cuil'] ?? null,
                    'unidad_responsable_id' => $datos['unidad_id'],
                ]);
                $convenio->representantes()->attach($nuevoRepresentante);
            }

            $detalle = $datos['detalle_pasantia'] ?? [];
            if (collect($detalle)->contains(fn ($valor) => filled($valor))) {
                $convenio->detallePasantia()->create([
                    'fecha_inicio' => $detalle['fecha_inicio_pasantia'] ?? null,
                    'fecha_fin' => $detalle['fecha_fin_pasantia'] ?? null,
                    'carga_horaria_semanal' => $detalle['carga_horaria_semanal'] ?? null,
                    'asignacion_estimulo' => $detalle['asignacion_estimulo'] ?? null,
                ]);
            }

            return $convenio->load([
                'tipoInstrumento',
                'unidad',
                'unidadResponsableSecundaria',
                'representantes',
                'detallePasantia',
            ]);
        });

        return response()->json($convenio, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Convenio $convenio)
    {
        return $convenio->load([
            'tipoInstrumento',
            'unidad',
            'unidadResponsableSecundaria',
            'revisor',
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
            'firma_decano' => 'sometimes|boolean',
            'fecha_vencimiento' => 'nullable|date',
            'convenio_padre_id' => 'nullable|exists:convenios,id',
            'tipo_instrumento_id' => 'sometimes|exists:tipo_instrumentos,id',
            'revisor_id' => [
                'required_if:estado,revision',
                'nullable',
                Rule::exists('users', 'id')->where('rol', 'revision'),
            ],

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
            $rol = strtolower((string) $request->user()->rol);

            if (
                array_key_exists('firma_decano', $datos)
                && ! in_array($rol, ['administracion', 'administrador', 'admin'], true)
            ) {
                abort(403, 'Solo Administración puede registrar la firma del convenio.');
            }

            if ($cambiaEstado) {
                if ($datos['estado'] === 'aprobado' && $rol !== 'revision') {
                    abort(403, 'Solo un usuario de Revisión puede aprobar el convenio.');
                }
                if (
                    $datos['estado'] === 'firmado'
                    && ! in_array($rol, ['administracion', 'administrador', 'admin'], true)
                ) {
                    abort(403, 'Solo Administración puede firmar el convenio.');
                }

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

                if (
                    $datos['estado'] === 'aprobado'
                    && (
                        ! $convenio->unidad_responsable_id
                        || ! $convenio->unidad_responsable_secundaria_id
                        || (string) $convenio->unidad_responsable_id === (string) $convenio->unidad_responsable_secundaria_id
                    )
                ) {
                    throw ValidationException::withMessages([
                        'estado' => ['Para aprobar el convenio debe tener dos unidades responsables distintas.'],
                    ]);
                }

                if ($datos['estado'] === 'borrador') {
                    $datos['revisor_id'] = null;
                }
                if ($datos['estado'] === 'firmado') {
                    $datos['firma_decano'] = true;
                }
            }

            $convenio->revisorAsignado  = $datos['revisor_id'] ?? null;
            $convenio->fundamentoCambio = $datos['fundamento'] ?? null;

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

        return $convenio->load(['tipoInstrumento', 'unidad', 'unidadResponsableSecundaria', 'revisor']);
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
