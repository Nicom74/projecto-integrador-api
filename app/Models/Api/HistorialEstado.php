<?php

namespace App\Models\Api;

use Illuminate\Database\Eloquent\Model;

class HistorialEstado extends Model
{
    protected $fillable = ['user_id', 'convenio_id', 'documento_id', 'fundamento',
                           'fecha_cambio', 'estado_anterior', 'estado_nuevo'];

    protected $casts = ['fecha_cambio' => 'datetime'];
    
    public function usuario() { return $this->belongsTo(User::class, 'user_id'); }
    public function convenio() { return $this->belongsTo(Convenio::class); }
    public function documento() { return $this->belongsTo(Documento::class); }
}
