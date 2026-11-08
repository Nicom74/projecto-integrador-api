<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Api\Convenio;

class HistorialEstadoController extends Controller
{
     public function index(Convenio $convenio)
    {
        return $convenio->historialEstados()
            ->with(['usuario:id,name', 'documento:id,archivo'])
            ->latest('fecha_cambio')
            ->paginate(15);
    }
}
