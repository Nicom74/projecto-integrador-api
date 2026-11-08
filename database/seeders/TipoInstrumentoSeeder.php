<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Api\TipoInstrumento;

class TipoInstrumentoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tipos = [
            ['nombre' => 'Convenio marco',      'categoria' => 'Colaboración'],
            ['nombre' => 'Convenio específico', 'categoria' => 'Colaboración'],
            ['nombre' => 'Convenio marco',      'categoria' => 'Pasantía'],
            ['nombre' => 'Convenio específico', 'categoria' => 'Pasantía'],
            ['nombre' => 'Adenda',              'categoria' => 'Adenda'],
        ];

        foreach ($tipos as $tipo) {
            TipoInstrumento::firstOrCreate($tipo);
        }
    }
}
