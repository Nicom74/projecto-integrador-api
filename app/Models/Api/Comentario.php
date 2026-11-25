<?php

namespace App\Models\Api;

use Illuminate\Database\Eloquent\Model;

class Comentario extends Model
{
    protected $fillable = ['convenio_id', 'user_id', 'texto', 'ofensivo'];

    protected $casts = ['ofensivo' => 'boolean'];

    public function convenio() { return $this->belongsTo(Convenio::class); }
    public function usuario()  { return $this->belongsTo(User::class, 'user_id'); }
}