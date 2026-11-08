<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Api\UnidadResponsable;


class UnidadResponsableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $unidades = [
            ['Facultad de Ingeniería', 'Académica'],
            ['Facultad de Ciencias Exactas y Naturales', 'Académica'],
            ['Rectorado', 'Administrativa'],
            ['Municipalidad de Villa Ejemplo', 'Externa'],
            ['Cooperativa Sur Pampeano', 'Externa'],
            ['TecnoPampa S.A.',  'Externa'],
        ];

        foreach ($unidades as [$nombre, $tipo]) {
            UnidadResponsable::firstOrCreate(['nombre' => $nombre], ['tipo' => $tipo]);
        }
    }
}
