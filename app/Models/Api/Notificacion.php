<?php

namespace App\Models\Api;

use Illuminate\Database\Eloquent\Model;

class Notificacion extends Model
{
    protected $table = 'notificaciones';

    protected $fillable = ['user_id', 'convenio_id', 'tipo', 'titulo', 'mensaje', 'leida_en'];

    protected $casts = ['leida_en' => 'datetime'];
}