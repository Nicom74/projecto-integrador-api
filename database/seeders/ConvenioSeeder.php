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
use App\Models\Api\UnidadResponsable;
use Illuminate\Support\Carbon;

class ConvenioSeeder extends Seeder
{
   private const RUTA = ['borrador', 'revision', 'aprobado', 'firmado'];

    public function run(): void
    {
        $usuario = User::where('email', 'gestor@test.com')->firstOrFail();
        $revisor = User::where('email', 'revisor@test.com')->firstOrFail();
        $unidadId = UnidadResponsable::where('nombre', 'Facultad de Ingeniería')->firstOrFail()->id;
        $hoy = Carbon::today();

        $convenios = [
            ['codigo' => 'CONV-2026-001', 'titulo' => 'Marco de colaboración con el Municipio', 'fecha' => '2026-02-10',
             'estado' => 'firmado', 'tipo' => ['Convenio marco', 'Colaboración'],
             'vence' => '2028-02-10',
             'reps' => ['rep.ing@test.com', 'rep.muni@test.com']],

            ['codigo' => 'CONV-2026-002', 'titulo' => 'Específico de capacitación con TecnoPampa', 'fecha' => '2026-03-05',
             'estado' => 'aprobado', 'tipo' => ['Convenio específico', 'Colaboración'],
             'vence' => '2026-12-15',
             'reps' => ['rep.ing@test.com', 'rep.tecno@test.com']],

            ['codigo' => 'CONV-2026-003', 'titulo' => 'Marco de pasantías con TecnoPampa', 'fecha' => '2026-04-01',
             'estado' => 'firmado', 'tipo' => ['Convenio marco', 'Pasantía'],
             'vence' => '2026-10-01',
             'reps' => ['rep.ing@test.com', 'rep.tecno@test.com']],

            ['codigo' => 'CONV-2026-004', 'titulo' => 'Pasantía en desarrollo de software', 'fecha' => '2026-09-15',
             'estado' => 'revision', 'tipo' => ['Convenio específico', 'Pasantía'],
             'vence' => '2027-05-31',
             'reps' => ['rep.ing@test.com', 'rep.tecno@test.com'], 'pasantia' => true],

            ['codigo' => 'CONV-2026-005', 'titulo' => 'Adenda de prórroga - Marco Municipio', 'fecha' => '2026-10-20',
             'estado' => 'borrador', 'tipo' => ['Adenda', 'Adenda'],
             'reps' => [], 'padre' => 'CONV-2026-001'],

            ['codigo' => 'CONV-2026-101', 'titulo' => 'Marco de colaboración con Univ. Ejemplo', 'fecha' => '2026-10-02',
             'estado' => 'aprobado', 'tipo' => ['Convenio marco', 'Colaboración'],
             'reps' => ['rep.ing@test.com', 'rep.tecno@test.com']],

            ['codigo' => 'CONV-2026-102', 'titulo' => 'Específico de investigación aplicada', 'fecha' => '2026-10-05',
             'estado' => 'aprobado', 'tipo' => ['Convenio específico', 'Colaboración'],
             'reps' => ['rep.ing@test.com', 'rep.tecno@test.com']],

            ['codigo' => 'CONV-2026-103', 'titulo' => 'Marco de pasantías con Empresa Demo', 'fecha' => '2026-10-09',
             'estado' => 'aprobado', 'tipo' => ['Convenio marco', 'Pasantía'],
             'reps' => ['rep.ing@test.com', 'rep.tecno@test.com']],

            ['codigo' => 'CONV-2026-104', 'titulo' => 'Pasantía en soporte técnico', 'fecha' => '2026-10-14',
             'estado' => 'aprobado', 'tipo' => ['Convenio específico', 'Pasantía'],
             'reps' => ['rep.ing@test.com', 'rep.tecno@test.com'], 'pasantia' => true],

            ['codigo' => 'CONV-2026-105', 'titulo' => 'Adenda de ampliación - Marco Univ.', 'fecha' => '2026-10-22',
             'estado' => 'aprobado', 'tipo' => ['Adenda', 'Adenda'],
             'reps' => ['rep.ing@test.com', 'rep.tecno@test.com'], 'padre' => 'CONV-2026-101'],

            ['codigo' => 'CONV-2026-106', 'titulo' => 'Marco de transferencia tecnológica', 'fecha' => '2026-10-30',
             'estado' => 'aprobado', 'tipo' => ['Convenio marco', 'Colaboración'],
             'reps' => ['rep.ing@test.com', 'rep.tecno@test.com']],

            ['codigo' => 'DEMO-OPS-2026-001', 'titulo' => 'Prueba: borrador para asignar revisor',
             'fecha' => $hoy->copy()->subDays(3)->toDateString(),
             'estado' => 'borrador', 'tipo' => ['Convenio específico', 'Colaboración'],
             'unidad_id' => $unidadId, 'reps' => ['rep.ing@test.com', 'rep.exactas@test.com']],

            ['codigo' => 'DEMO-OPS-2026-002', 'titulo' => 'Prueba: borrador devuelto para corregir',
             'fecha' => $hoy->copy()->subDays(14)->toDateString(),
             'estado' => 'borrador', 'tipo' => ['Convenio marco', 'Colaboración'],
             'unidad_id' => $unidadId, 'devuelto' => true,
             'reps' => ['rep.ing@test.com', 'rep.muni@test.com']],

            ['codigo' => 'DEMO-OPS-2026-003', 'titulo' => 'Prueba: convenio pendiente de revisión',
             'fecha' => $hoy->copy()->subDays(6)->toDateString(),
             'estado' => 'revision', 'tipo' => ['Convenio específico', 'Colaboración'],
             'unidad_id' => $unidadId, 'asignar_revisor' => true,
             'reps' => ['rep.ing@test.com', 'rep.tecno@test.com']],

            ['codigo' => 'DEMO-OPS-2026-004', 'titulo' => 'Prueba: aprobado pendiente de firma',
             'fecha' => $hoy->copy()->subDays(15)->toDateString(),
             'estado' => 'aprobado', 'tipo' => ['Convenio marco', 'Colaboración'],
             'unidad_id' => $unidadId, 'reps' => ['rep.ing@test.com', 'rep.muni@test.com']],

            ['codigo' => 'DEMO-OPS-2026-005', 'titulo' => 'Prueba: firmado próximo a vencer',
             'fecha' => $hoy->copy()->subDays(20)->toDateString(),
             'estado' => 'firmado', 'tipo' => ['Convenio marco', 'Pasantía'],
             'vence' => $hoy->copy()->addDays(20)->toDateString(),
             'unidad_id' => $unidadId, 'reps' => ['rep.ing@test.com', 'rep.tecno@test.com']],

            ['codigo' => 'DEMO-OPS-2026-006', 'titulo' => 'Prueba: firmado para rescindir',
             'fecha' => $hoy->copy()->subDays(60)->toDateString(),
             'estado' => 'firmado', 'tipo' => ['Convenio específico', 'Colaboración'],
             'vence' => $hoy->copy()->addDays(300)->toDateString(),
             'unidad_id' => $unidadId, 'reps' => ['rep.ing@test.com', 'rep.coop@test.com']],
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
                    'fecha_vencimiento' => $d['vence'] ?? null,
                    'firma_decano'        => $firmado,
                    'tipo_instrumento_id' => $tipo->id,
                    'unidad_responsable_id' => $d['unidad_id'] ?? null,
                    'revisor_id'          => ! empty($d['asignar_revisor']) ? $revisor->id : null,
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

            $estadoHistorial = ! empty($d['devuelto']) ? 'revision' : $d['estado'];
            $this->historial($convenio, $usuario, $estadoHistorial, $d['fecha']);

            if (! empty($d['devuelto'])) {
                $convenio->historialEstados()->create([
                    'user_id' => $revisor->id,
                    'fundamento' => 'Completar documentación',
                    'fecha_cambio' => Carbon::parse($d['fecha'])->addDays(10),
                    'estado_anterior' => 'revision',
                    'estado_nuevo' => 'borrador',
                ]);
            }

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
