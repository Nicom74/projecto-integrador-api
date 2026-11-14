<?php

namespace App\Observers;

use App\Models\Api\Convenio;
use App\Models\Api\Notificacion;
use App\Models\Api\User;

class ConvenioObserver
{
    // Se notifica a los usuarios con este rol (los que usan la app móvil).
    private const ROL_DESTINATARIO = 'decano';

    public function created(Convenio $convenio): void
    {
        $this->notificar(
            $convenio,
            'nuevo_convenio',
            'Nuevo convenio registrado',
            "Se registró el convenio {$convenio->codigo}: {$convenio->titulo}"
        );
    }

    public function updated(Convenio $convenio): void
    {
        $cambios = array_diff(array_keys($convenio->getChanges()), ['updated_at']);

        if (empty($cambios)) {
            return;
        }

        if ($convenio->wasChanged('estado') && $convenio->estado === 'rescindido') {
            $this->notificar($convenio, 'cancelado', 'Convenio cancelado', "El convenio {$convenio->codigo} fue rescindido.");
            return;
        }

        $mensaje = $convenio->wasChanged('estado')
            ? "El convenio {$convenio->codigo} pasó de {$convenio->getOriginal('estado')} a {$convenio->estado}."
            : "Se modificaron datos del convenio {$convenio->codigo}.";

        $this->notificar($convenio, 'cambio_datos', 'Cambio en un convenio', $mensaje);
    }

    private function notificar(Convenio $convenio, string $tipo, string $titulo, string $mensaje): void
    {
        // En seeders y tinker no se generan notificaciones.
        if (app()->runningInConsole()) {
            return;
        }

        // Quien hace el cambio no se notifica a sí mismo.
        $autor = request()->user()?->id;

        User::where('rol', self::ROL_DESTINATARIO)
            ->when($autor, fn ($q) => $q->where('id', '!=', $autor))
            ->get()
            ->each(fn ($usuario) => Notificacion::create([
                'user_id' => $usuario->id,
                'convenio_id' => $convenio->id,
                'tipo' => $tipo,
                'titulo' => $titulo,
                'mensaje' => $mensaje,
            ]));
    }
}