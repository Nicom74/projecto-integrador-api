<?php

namespace App\Models\Api;

use Illuminate\Database\Eloquent\Model;

class Firma extends Model
{
    protected $fillable = ['convenio_id', 'user_id', 'imagen', 'latitud', 'longitud', 'fecha_firma', 'metodo'];

    protected $casts = [
        'fecha_firma' => 'datetime',
        'latitud'     => 'float',
        'longitud'    => 'float',
    ];

    public function convenio() { return $this->belongsTo(Convenio::class); }
    public function usuario()  { return $this->belongsTo(User::class, 'user_id'); }
}
