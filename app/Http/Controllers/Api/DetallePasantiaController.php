<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Api\Convenio;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DetallePasantiaController extends Controller
{
    public function show(Convenio $convenio)
    {
        $detalle = $convenio->detallePasantia;

        abort_if(! $detalle, 404, 'Este convenio no tiene detalle de pasantía.');

        return $detalle->load(['estudiante', 'tutorAcademico', 'tutorGuia']);
    }

    public function store(Request $request, Convenio $convenio)
    {
        abort_if($convenio->detallePasantia()->exists(), 409, 'Este convenio ya tiene detalle de pasantía.');
        $this->verificarEditable($convenio);

        $datos = $request->validate([
            'estudiante_id' => ['required', Rule::exists('persona_vinculadas', 'id')->where('tipo_persona', 'estudiante')],
            'tutor_academico_id' => ['required', Rule::exists('persona_vinculadas', 'id')->where('tipo_persona', 'tutor_academico')],
            'tutor_guia_id' => ['required', Rule::exists('persona_vinculadas', 'id')->where('tipo_persona', 'tutor_guia')],            
            'carrera' => 'required|string|max:45',
            'carga_horaria_semanal' => 'required|string|max:45',
            'asignacion_estimulo' => 'required|string|max:45',
            'cobertura_seguro_art' => 'required|string|max:45',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
        ]);

        $detalle = $convenio->detallePasantia()->create($datos);

        return response()->json($detalle->load(['estudiante', 'tutorAcademico', 'tutorGuia']), 201);
    }

    public function update(Request $request, Convenio $convenio)
    {
        $detalle = $convenio->detallePasantia;

        abort_if(! $detalle, 404, 'Este convenio no tiene detalle de pasantía.');
        $this->verificarEditable($convenio);

        $datos = $request->validate([
            'estudiante_id' => ['required', Rule::exists('persona_vinculadas', 'id')->where('tipo_persona', 'estudiante')],
            'tutor_academico_id' => ['sometimes', Rule::exists('persona_vinculadas', 'id')->where('tipo_persona', 'tutor_academico')],
            'tutor_guia_id' => ['sometimes', Rule::exists('persona_vinculadas', 'id')->where('tipo_persona', 'tutor_guia')],
            'carrera' => 'sometimes|string|max:45',
            'carga_horaria_semanal' => 'sometimes|string|max:45',
            'asignacion_estimulo' => 'sometimes|string|max:45',
            'cobertura_seguro_art' => 'sometimes|string|max:45',
            'fecha_inicio' => 'sometimes|date',
            'fecha_fin' => 'sometimes|date|after:fecha_inicio',
        ]);

        $detalle->update($datos);

        return $detalle->load(['estudiante', 'tutorAcademico', 'tutorGuia']);
    }

    public function destroy(Convenio $convenio)
    {
        $this->verificarEditable($convenio);

        $convenio->detallePasantia()->delete();

        return response()->noContent();
    }
}