<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Api\Documento;
use Illuminate\Support\Facades\Storage;

class DocumentoController extends Controller
{
    // Todos los documentos (opcionalmente filtrados por convenio)
    public function index(Request $request)
    {
        return Documento::with('convenio:id,titulo,codigo')
            ->when($request->query('convenio_id'), fn ($q, $id) => $q->where('convenio_id', $id))
            ->latest()
            ->paginate(10);
    }

    public function store(Request $request)
    {
        $request->validate([
            'convenio_id' => 'required|exists:convenios,id',
            'archivo' => 'required|file|mimes:pdf|max:20480', // 20MB
        ]);

        $file = $request->file('archivo');
        $nombre = $file->hashName();
        $file->storeAs('documentos', $nombre);

        $documento = Documento::create([
            'convenio_id' => $request->convenio_id,
            'archivo'     => $nombre,
        ]);

        return response()->json($documento, 201);
    }

    public function show(Documento $documento)
    {
        return $documento->load('convenio:id,titulo,codigo');
    }

    public function download(Documento $documento)
    {
        return Storage::download('documentos/' . $documento->archivo);
    }

    public function destroy(Documento $documento)
    {
        Storage::delete('documentos/' . $documento->archivo);
        $documento->delete();

        return response()->noContent();
    }
}
