<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ConvenioController;
use App\Http\Controllers\Api\DocumentoController;
use App\Http\Controllers\Api\HistorialEstadoController;
use App\Http\Controllers\Api\ParteController;
use App\Http\Controllers\Api\RepresentanteController;
use App\Http\Controllers\Api\DetallePasantiaController;
use App\Http\Controllers\Api\TipoInstrumentoController;
use App\Http\Controllers\Api\UnidadResponsableController;
use App\Http\Controllers\Api\PersonaVinculadaController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::apiResource('convenios', ConvenioController::class); //Aca genera todas las rutas para ver, crear, eliminar, modificar los convenios (aca, al modificar un convenio y cambiar el estado se guarda tambien en HistorialEstado)
    Route::apiResource('documentos', DocumentoController::class)->except('update');
    Route::get('documentos/{documento}/download', [DocumentoController::class, 'download']); //Para descargar documentos, se guardan en storage/documentos, esto es temporal porque por ahora no se donde guardarlos.
    Route::get('convenios/{convenio}/historial', [HistorialEstadoController::class, 'index']);
    Route::apiResource('convenios.partes', ParteController::class)->only(['index', 'store', 'destroy'])->scoped();    
    Route::apiResource('representantes', RepresentanteController::class);
    Route::apiResource('unidades', UnidadResponsableController::class)->parameters(['unidades' => 'unidad_responsable']);
    Route::apiResource('tipos-instrumento', TipoInstrumentoController::class)->parameters(['tipos-instrumento' => 'tipo_instrumento']);
    Route::get('convenios/{convenio}/pasantia', [DetallePasantiaController::class, 'show']);
    Route::post('convenios/{convenio}/pasantia', [DetallePasantiaController::class, 'store']);
    Route::put('convenios/{convenio}/pasantia', [DetallePasantiaController::class, 'update']);
    Route::delete('convenios/{convenio}/pasantia', [DetallePasantiaController::class, 'destroy']);
    Route::apiResource('personas', PersonaVinculadaController::class)->parameters(['personas' => 'persona']);
});