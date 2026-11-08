<?php

namespace App\Models\Api;

use Illuminate\Database\Eloquent\Model;

class PersonaVinculada extends Model
{
    public const TIPOS = ['estudiante', 'tutor_academico', 'tutor_guia'];

    protected $fillable = ['nombre', 'apellido', 'dni', 'email', 'tipo_persona'];
    //Figura en alguna pasantía?
    public function enUso(): bool
    {
        return DetallePasantia::where('estudiante_id', $this->id)
            ->orWhere('tutor_academico_id', $this->id)
            ->orWhere('tutor_guia_id', $this->id)
            ->exists();
    }
}
