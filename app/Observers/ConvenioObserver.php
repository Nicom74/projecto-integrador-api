<?php

namespace App\Observers;

use App\Models\Api\Convenio;
use App\Models\Api\Notificacion;
use App\Models\Api\User;

class ConvenioObserver
{
    // Rol fijo: la app móvil depende de este.
    private const ROL_DESTINATARIO = 'decano';

    // Roles ADICIONALES según el evento (el decano se notifica siempre, además de estos)
    private const ROLES_NUEVO_CONVENIO = ['gestion', 'revision', 'administracion'];
    private const ROLES_CAMBIO_DATOS   = ['gestion'];

    // Cuando cambia el estado, a quién más le interesa el nuevo estado
    private const ROLES_POR_ESTADO = [
        'revision' => ['revision'],                    // hay un convenio para revisar
        'borrador' => ['gestion'],                     // devuelto con observaciones
        'aprobado' => ['gestion', 'administracion'],
        'firmado' => ['gestion', 'administracion', 'consulta'],
        'rescindido' => ['gestion', 'administracion'],
        'archivado' => ['gestion'],
    ];

    public function created(Convenio $convenio): void
    {
        $this->notificar(
            $convenio,
            'nuevo_convenio',
            'Nuevo convenio registrado',
            "Se registró el convenio {$convenio->codigo}: {$convenio->titulo}",
            self::ROLES_NUEVO_CONVENIO
        );
    }

    public function updated(Convenio $convenio): void
    {
        $cambios = array_diff(array_keys($convenio->getChanges()), ['updated_at']);

        if (empty($cambios)) {
            return;
        }

        if ($convenio->wasChanged('estado') && $convenio->estado === 'rescindido') {
            $this->notificar(
                $convenio,
                'cancelado',
                'Convenio cancelado',
                "El convenio {$convenio->codigo} fue rescindido.",
                self::ROLES_POR_ESTADO['rescindido']
            );
            return;
        }

        if ($convenio->wasChanged('estado')) {
            $mensaje = "El convenio {$convenio->codigo} pasó de {$convenio->getOriginal('estado')} a {$convenio->estado}.";

            // Asignación a un revisor concreto
            if ($convenio->estado === 'revision' && $convenio->revisorAsignado) {
                // El decano recibe el aviso de siempre; los demás revisores no
                $this->notificar($convenio, 'cambio_datos', 'Cambio en un convenio', $mensaje);

                // Y el revisor elegido recibe un aviso propio, con el fundamento
                $this->notificarUsuario(
                    $convenio->revisorAsignado,
                    $convenio,
                    'cambio_datos',
                    'Convenio asignado para revisión',
                    "Se te asignó el convenio {$convenio->codigo}: {$convenio->titulo}. Fundamento: {$convenio->fundamentoCambio}"
                );
                return;
            }

            $extra = self::ROLES_POR_ESTADO[$convenio->estado] ?? [];
        } else {
            $mensaje = "Se modificaron datos del convenio {$convenio->codigo}.";
            $extra = self::ROLES_CAMBIO_DATOS;
        }

        $destinatarioId = $convenio->estado === 'revision' ? $convenio->revisor_id : null;
        $this->notificar(
            $convenio,
            'cambio_datos',
            'Cambio en un convenio',
            $mensaje,
            $extra,
            $destinatarioId
        );
    }

    private function notificar(
        Convenio $convenio,
        string $tipo,
        string $titulo,
        string $mensaje,
        array $rolesExtra = [],
        ?int $destinatarioId = null
    ): void {
        // En seeders y tinker no se generan notificaciones.
        if (app()->runningInConsole()) {
            return;
        }

        // Quien hace el cambio no se notifica a sí mismo.
        $autor = request()->user()?->id;

        // El decano siempre va; los demás roles se suman sin duplicar
        $roles = array_values(array_unique(array_merge([self::ROL_DESTINATARIO], $rolesExtra)));

        User::whereIn('rol', $roles)
            ->when(
                $destinatarioId,
                fn ($q) => $q->where(
                    fn ($destinatarios) => $destinatarios
                        ->where('rol', self::ROL_DESTINATARIO)
                        ->orWhere('id', $destinatarioId)
                )
            )
            ->when($autor, fn ($q) => $q->where('id', '!=', $autor))
            ->get()
            ->each(fn ($usuario) => Notificacion::create([
                'user_id'     => $usuario->id,
                'convenio_id' => $convenio->id,
                'tipo'        => $tipo,
                'titulo'      => $titulo,
                'mensaje'     => $mensaje,
            ]));
    }

    private function notificarUsuario(int $userId, Convenio $convenio, string $tipo, string $titulo, string $mensaje): void
    {
        // En seeders y tinker no se generan notificaciones.
        if (app()->runningInConsole()) {
            return;
        }

        // Quien hace el cambio no se notifica a sí mismo.
        if ($userId === request()->user()?->id) {
            return;
        }

        Notificacion::create([
            'user_id'     => $userId,
            'convenio_id' => $convenio->id,
            'tipo'        => $tipo,
            'titulo'      => $titulo,
            'mensaje'     => $mensaje,
        ]);
    }
}