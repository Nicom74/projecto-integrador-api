<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Api\Convenio;
use App\Models\Api\Parte;
use App\Models\Api\PersonaVinculada;
use App\Models\Api\Representante;
use App\Models\Api\TipoInstrumento;
use App\Models\Api\User;
use Illuminate\Support\Carbon;

class ConvenioSeeder extends Seeder
{
   private const RUTA = ['borrador', 'revision', 'aprobado', 'firmado'];

    public function run(): void
    {
        $usuario = User::where('email', 'gestor@test.com')->firstOrFail();

        $convenios = [
            ['codigo' => 'CONV-2026-001', 'titulo' => 'Marco de colaboración con el Municipio', 'fecha' => '2026-02-10',
             'estado' => 'firmado', 'tipo' => ['Convenio marco', 'Colaboración'],
             'reps' => ['rep.ing@test.com', 'rep.muni@test.com']],

            ['codigo' => 'CONV-2026-002', 'titulo' => 'Específico de capacitación con TecnoPampa', 'fecha' => '2026-03-05',
             'estado' => 'aprobado', 'tipo' => ['Convenio específico', 'Colaboración'],
             'reps' => ['rep.ing@test.com', 'rep.tecno@test.com']],

            ['codigo' => 'CONV-2026-003', 'titulo' => 'Marco de pasantías con TecnoPampa', 'fecha' => '2026-04-01',
             'estado' => 'firmado', 'tipo' => ['Convenio marco', 'Pasantía'],
             'reps' => ['rep.ing@test.com', 'rep.tecno@test.com']],

            ['codigo' => 'CONV-2026-004', 'titulo' => 'Pasantía en desarrollo de software', 'fecha' => '2026-09-15',
             'estado' => 'revision', 'tipo' => ['Convenio específico', 'Pasantía'],
             'reps' => ['rep.ing@test.com', 'rep.tecno@test.com'], 'pasantia' => true],

            ['codigo' => 'CONV-2026-005', 'titulo' => 'Adenda de prórroga - Marco Municipio', 'fecha' => '2026-10-20',
             'estado' => 'borrador', 'tipo' => ['Adenda', 'Adenda'],
             'reps' => [], 'padre' => 'CONV-2026-001'],
        ];

        foreach ($convenios as $d) {
            $firmado = $d['estado'] === 'firmado';

            $tipo = TipoInstrumento::where('nombre', $d['tipo'][0])
                ->where('categoria', $d['tipo'][1])
                ->firstOrFail();

            $convenio = Convenio::firstOrCreate(
                ['codigo' => $d['codigo']],
                [
                    'titulo'              => $d['titulo'],
                    'fecha_creacion'      => $d['fecha'],
                    'estado'              => $d['estado'],
                    'firma_gestor'        => $firmado,
                    'firma_decano'        => $firmado,
                    'tipo_instrumento_id' => $tipo->id,
                    'convenio_padre_id'   => isset($d['padre'])
                        ? Convenio::where('codigo', $d['padre'])->value('id')
                        : null,
                ]
            );

            if (! $convenio->wasRecentlyCreated) {
                continue;
            }

            foreach ($d['reps'] as $email) {
                Parte::create([
                    'convenio_id' => $convenio->id,
                    'representante_id' => Representante::where('email', $email)->value('id'),
                ]);
            }

            $this->historial($convenio, $usuario, $d['estado'], $d['fecha']);

            if (! empty($d['pasantia'])) {
                $convenio->detallePasantia()->create([
                    'estudiante_id' => $this->persona('40000001'),
                    'tutor_academico_id' => $this->persona('30000001'),
                    'tutor_guia_id' => $this->persona('31000001'),
                    'carrera' => 'Ingeniería en Sistemas',
                    'carga_horaria_semanal' => '20 horas',
                    'asignacion_estimulo' => '150000',
                    'cobertura_seguro_art' => 'Sí',
                    'fecha_inicio' => '2026-12-01',
                    'fecha_fin' => '2027-05-31',
                ]);
            }
        }
    }

    // Genera las transiciones desde borrador hasta el estado actual
    private function historial(Convenio $convenio, User $usuario, string $estadoFinal, string $fechaBase): void
    {
        $pasos = array_slice(self::RUTA, 0, array_search($estadoFinal, self::RUTA) + 1);

        for ($i = 1; $i < count($pasos); $i++) {
            $convenio->historialEstados()->create([
                'user_id' => $usuario->id,
                'fundamento' => 'Dato de prueba',
                'fecha_cambio' => Carbon::parse($fechaBase)->addDays($i * 5),
                'estado_anterior' => $pasos[$i - 1],
                'estado_nuevo' => $pasos[$i],
            ]);
        }
    }

    private function persona(string $dni): int
    {
        return PersonaVinculada::where('dni', $dni)->value('id');
    }
}
