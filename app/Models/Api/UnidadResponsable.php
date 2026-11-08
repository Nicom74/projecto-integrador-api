<?php

namespace App\Models\Api;

use Illuminate\Database\Eloquent\Model;

class UnidadResponsable extends Model
{
    protected $fillable = ['nombre', 'tipo'];

    public function representantes() { return $this->hasMany(Representante::class); }
}
