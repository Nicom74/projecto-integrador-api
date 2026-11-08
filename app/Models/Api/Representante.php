<?php

namespace App\Models\Api;

use Illuminate\Database\Eloquent\Model;

class Representante extends Model
{
    protected $fillable = ['nombre', 'email', 'cuil', 'telefono', 'unidad_responsable_id'];

    public function unidadResponsable() { return $this->belongsTo(UnidadResponsable::class); }
}
