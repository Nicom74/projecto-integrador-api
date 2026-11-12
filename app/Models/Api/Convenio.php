<?php

namespace App\Models\Api;

use App\Models\Api\Documento;
use App\Models\Api\HistorialEstado;
use App\Models\Api\DetallePasantia;
use App\Models\Api\Representante;
use App\Models\Api\TipoInstrumento;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Convenio extends Model
{
    public const TRANSICIONES = [
        'borrador'   => ['revision'],
        'revision'   => ['borrador', 'aprobado'],
        'aprobado'   => ['firmado'],
        'firmado'    => ['rescindido', 'archivado'],
        'rescindido' => ['archivado'],
        'archivado'  => [],
    ];

    protected $fillable = [
        'fecha_creacion', 'firma_gestor', 'firma_decano', 'titulo',
        'estado', 'codigo', 'convenio_padre_id', 'tipo_instrumento_id',
    ];

    // Relaciones
    public function tipoInstrumento() { return $this->belongsTo(TipoInstrumento::class); }
    public function padre()           { return $this->belongsTo(Convenio::class, 'convenio_padre_id'); }
    public function adendas()         { return $this->hasMany(Convenio::class, 'convenio_padre_id'); }

    public function partes()          { return $this->hasMany(Parte::class); }
    public function representantes()  { return $this->belongsToMany(Representante::class, 'partes'); }

    public function documentos()        { return $this->hasMany(Documento::class); }
    public function historialEstados()  { return $this->hasMany(HistorialEstado::class); }
    public function detallePasantia()   { return $this->hasOne(DetallePasantia::class); }

    // Filtros de búsqueda
    public function scopeFiltrar(Builder $query, array $f): Builder
    {
        return $query
            ->when($f['q'] ?? null, function ($q, $texto) {
                $q->where(function ($q) use ($texto) {
                    $q->where('titulo', 'like', "%{$texto}%")
                      ->orWhere('codigo', 'like', "%{$texto}%");
                });
            })
            ->when($f['estado'] ?? null, fn ($q, $estado) => $q->where('estado', $estado))
            ->when($f['tipo_instrumento_id'] ?? null, fn ($q, $tipo) => $q->where('tipo_instrumento_id', $tipo))
            ->when($f['fecha_desde'] ?? null, fn ($q, $d) => $q->whereDate('fecha_creacion', '>=', $d))
            ->when($f['fecha_hasta'] ?? null, fn ($q, $h) => $q->whereDate('fecha_creacion', '<=', $h));
    }
}