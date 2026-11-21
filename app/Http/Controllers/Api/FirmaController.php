<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Api\Convenio;
use App\Models\Api\Firma;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class FirmaController extends Controller
{
    public function show(Convenio $convenio)
    {
        $firma = $convenio->firmas()->with('usuario:id,name')->latest('fecha_firma')->first();

        abort_if(! $firma, 404, 'Este convenio no tiene firma.');

        return $firma;
    }

    public function imagen(Firma $firma)
    {
        $ruta = 'firmas/' . $firma->imagen;

        abort_unless(Storage::exists($ruta), 404, 'La imagen de la firma no existe.');

        return response(Storage::get($ruta), 200, [
            'Content-Type'  => 'image/png',
            'Cache-Control' => 'private, max-age=86400',
        ]);
    }

    public function store(Request $request, Convenio $convenio)
    {
        abort_unless($request->user()->rol === 'decano', 403, 'Solo el decano puede firmar.');

        $datos = $request->validate([
            'firma'    => 'required|string|max:500000',   // data URI base64 del canvas
            'latitud'  => 'required|numeric|between:-90,90',
            'longitud' => 'required|numeric|between:-180,180',
        ]);

        // data:image/png;base64,AAAA...  ->  binario
        $base64 = preg_replace('#^data:image/\w+;base64,#i', '', $datos['firma']);
        $binario = base64_decode($base64, true);

        if ($binario === false || @getimagesizefromstring($binario) === false) {
            throw ValidationException::withMessages(['firma' => ['La firma no es una imagen válida.']]);
        }

        $firma = DB::transaction(function () use ($request, $convenio, $datos, $binario) {
            $convenio = Convenio::lockForUpdate()->findOrFail($convenio->id);

            if ($convenio->estado !== 'aprobado') {
                throw ValidationException::withMessages([
                    'estado' => ["Solo se puede firmar un convenio aprobado (estado actual: {$convenio->estado})."],
                ]);
            }

            $nombre = Str::uuid() . '.png';
            Storage::put('firmas/' . $nombre, $binario);

            $firma = Firma::create([
                'convenio_id' => $convenio->id,
                'user_id' => $request->user()->id,
                'imagen' => $nombre,
                'latitud' => $datos['latitud'],
                'longitud' => $datos['longitud'],
                'fecha_firma' => now(),
                'metodo' => 'huella',
            ]);

            // Dispara el ConvenioObserver (notificaciones) igual que el update normal
            $convenio->update(['estado' => 'firmado', 'firma_decano' => true]);

            $convenio->historialEstados()->create([
                'user_id' => $request->user()->id,
                'fundamento' => 'Firmado por el decano',   // columna de 45 chars
                'fecha_cambio' => now(),
                'estado_anterior' => 'aprobado',
                'estado_nuevo' => 'firmado',
            ]);

            return $firma;
        });

        return response()->json($firma->load('usuario:id,name'), 201);
    }
}