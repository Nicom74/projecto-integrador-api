<?php

namespace App\Models\Api;

use Illuminate\Database\Eloquent\Model;

class Parte extends Model
{
    protected $table = 'partes';
    protected $fillable = ['convenio_id', 'representante_id'];
    
    public function convenio() { return $this->belongsTo(Convenio::class); }
    public function representante() { return $this->belongsTo(Representante::class); }
}
