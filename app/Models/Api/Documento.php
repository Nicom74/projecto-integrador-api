<?php

namespace App\Models\Api;

use Illuminate\Database\Eloquent\Model;

class Documento extends Model
{
    protected $fillable = ['archivo', 'convenio_id'];

    public function convenio() { return $this->belongsTo(Convenio::class); }
}
