<?php

namespace App\Models\Api;

use Illuminate\Database\Eloquent\Model;

class TipoInstrumento extends Model
{
    protected $fillable = [
        'nombre',
        'categoria',
    ];
    public function convenios() { return $this->belongsToMany(Convenio::class); }
}
