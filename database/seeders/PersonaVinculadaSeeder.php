<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Api\PersonaVinculada;

class PersonaVinculadaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         $personas = [
            ['Ana', 'Pérez', '40000001', 'estudiante'],
            ['Bruno', 'Gómez', '40000002', 'estudiante'],
            ['Camila', 'Ruiz', '40000003', 'estudiante'],
            ['Diego', 'Sosa', '40000004', 'estudiante'],
            ['Marta', 'Díaz', '30000001', 'tutor_academico'],
            ['Luis', 'Vega', '30000002', 'tutor_academico'],
            ['Jorge', 'Luna', '31000001', 'tutor_guia'],
            ['Elena', 'Paz', '31000002', 'tutor_guia'],
        ];

        foreach ($personas as [$nombre, $apellido, $dni, $tipo]) {
            PersonaVinculada::firstOrCreate(
                ['dni' => $dni],
                ['nombre' => $nombre, 'apellido' => $apellido, 'email' => "{$dni}@test.com", 'tipo_persona' => $tipo]
            );
        }
    }
}
