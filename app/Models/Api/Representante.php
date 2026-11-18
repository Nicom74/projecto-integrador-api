<?php

namespace App\Models\Api;

use Illuminate\Database\Eloquent\Model;

class Representante extends Model
{
    protected $fillable = [
        'nombre',
        'apellido',
        'cargo',
        'dni',
        'email',
        'cuil',
        'telefono',
        'unidad_responsable_id',
    ];

    public function unidadResponsable() { return $this->belongsTo(UnidadResponsable::class); }
    public function convenios() { return $this->belongsToMany(Convenio::class, 'partes'); }
}
