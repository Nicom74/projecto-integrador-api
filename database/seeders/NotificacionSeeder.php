<?php

namespace Database\Seeders;

use App\Models\Api\Convenio;
use App\Models\Api\Notificacion;
use App\Models\Api\User;
use Illuminate\Database\Seeder;

class NotificacionSeeder extends Seeder
{
    // Datos de demostración: tres notificaciones de ejemplo para el decano.
    public function run(): void
    {
        $decano = User::where('email', 'decano@test.com')->first();

        if (! $decano) {
            return;
        }

        $filas = [
            ['CONV-2026-106', 'nuevo_convenio', 'Nuevo convenio registrado', 'Se registró el convenio CONV-2026-106: Marco de transferencia tecnológica'],
            ['CONV-2026-102', 'cambio_datos', 'Cambio en un convenio', 'Se modificaron datos del convenio CONV-2026-102.'],
            ['CONV-2026-003', 'cancelado', 'Convenio cancelado', 'El convenio CONV-2026-003 fue rescindido.'],
        ];

        foreach ($filas as [$codigo, $tipo, $titulo, $mensaje]) {
            Notificacion::firstOrCreate(
                ['user_id' => $decano->id, 'tipo' => $tipo, 'mensaje' => $mensaje],
                ['convenio_id' => Convenio::where('codigo', $codigo)->value('id'), 'titulo' => $titulo]
            );
        }
    }
}