<?php

namespace App\Models\Api;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;


class Convenio extends Model
{
    public const TRANSICIONES = [
        'borrador'   => ['revision'],
        'revision'   => ['borrador', 'aprobado'],
        'aprobado'   => ['firmado'],
        'firmado'    => ['rechazado', 'archivado'],
        'rechazado' => ['archivado'],
        'archivado'  => [],
    ];
    protected $fillable = ['fecha_creacion', 'firma_gestor', 'firma_decano',
                           'titulo', 'estado', 'codigo', 'convenio_padre_id'];

    public function padre() { return $this->belongsTo(Convenio::class, 'convenio_padre_id'); }
    public function tiposInstrumento() { return $this->belongsToMany(TipoInstrumento::class); }
    public function representantes() { return $this->belongsToMany(Representante::class, 'partes'); }

    public function documento() { return $this->hasOne(Documento::class); }
    public function historialEstados() { return $this->hasMany(HistorialEstado::class); }
    public function detallePasantia() { return $this->hasOne(DetallePasantia::class); }
    public function partes() { return $this->hasMany(Parte::class); }

    public function scopeFiltrar(Builder $query, array $f): Builder
    {
        return $query
            // Búsqueda por texto en título o código
            ->when($f['q'] ?? null, function ($q, $texto) {
                $q->where(function ($q) use ($texto) {
                    $q->where('titulo', 'like', "%{$texto}%")
                    ->orWhere('codigo', 'like', "%{$texto}%");
                });
            })
            // Estado
            ->when($f['estado'] ?? null, fn ($q, $estado) => $q->where('estado', $estado))
            // Tipo de instrumento
            ->when($f['tipo_instrumento_id'] ?? null, function ($q, $tipo) {
                $q->whereHas('tiposInstrumento', fn ($q) => $q->where('tipo_instrumentos.id', $tipo));
            })
            // Unidad responsable
            ->when($f['unidad_id'] ?? null, fn ($q, $unidad) => $q->where('unidad_responsable_id', $unidad))
            // Rango de fechas
            ->when($f['fecha_desde'] ?? null, fn ($q, $d) => $q->whereDate('fecha_creacion', '>=', $d))
            ->when($f['fecha_hasta'] ?? null, fn ($q, $h) => $q->whereDate('fecha_creacion', '<=', $h));
    }
}
