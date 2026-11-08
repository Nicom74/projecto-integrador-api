<?php

namespace App\Models\Api;

use Illuminate\Database\Eloquent\Model;

class DetallePasantia extends Model
{
    protected $fillable = [
        'convenio_id', 'estudiante_id', 'tutor_academico_id', 'tutor_guia_id',
        'carrera', 'carga_horaria_semanal', 'asignacion_estimulo',
        'cobertura_seguro_art', 'fecha_inicio', 'fecha_fin',
    ];

    protected $casts = ['fecha_inicio' => 'date', 'fecha_fin' => 'date'];

    public function convenio() { return $this->belongsTo(Convenio::class); }
    public function estudiante() { return $this->belongsTo(PersonaVinculada::class, 'estudiante_id'); }
    public function tutorAcademico() { return $this->belongsTo(PersonaVinculada::class, 'tutor_academico_id'); }
    public function tutorGuia() { return $this->belongsTo(PersonaVinculada::class, 'tutor_guia_id'); }
}
