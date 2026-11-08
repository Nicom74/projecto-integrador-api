<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Api\Representante;
use App\Models\Api\UnidadResponsable;

class RepresentanteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $reps = [
            ['Laura Ficticia', 'rep.ing@test.com', '27-00000001-0', '+54 2302 000001', 'Facultad de Ingeniería'],
            ['Carlos Demo', 'rep.exactas@test.com', '20-00000002-0', '+54 2302 000002', 'Facultad de Ciencias Exactas y Naturales'],
            ['Rosa Ejemplo', 'rep.muni@test.com', '27-00000003-0', '+54 2302 000003', 'Municipalidad de Villa Ejemplo'],
            ['Pedro Prueba', 'rep.coop@test.com', '20-00000004-0', '+54 2302 000004', 'Cooperativa Sur Pampeano'],
            ['Silvia Muestra', 'rep.tecno@test.com', '27-00000005-0', '+54 2302 000005', 'TecnoPampa S.A.'],
        ];

        foreach ($reps as [$nombre, $email, $cuil, $tel, $unidad]) {
            Representante::firstOrCreate(
                ['email' => $email],
                [
                    'nombre' => $nombre,
                    'cuil' => $cuil,
                    'telefono' => $tel,
                    'unidad_responsable_id' => UnidadResponsable::where('nombre', $unidad)->value('id'),
                ]
            );
        }
    }
}
