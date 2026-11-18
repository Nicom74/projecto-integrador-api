<?php

namespace Tests\Feature;

use App\Models\Api\Convenio;
use App\Models\Api\TipoInstrumento;
use App\Models\Api\UnidadResponsable;
use App\Models\Api\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ConvenioDraftWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_draft_with_form_data_and_generated_metadata(): void
    {
        Sanctum::actingAs(User::create([
            'name' => 'Gestor',
            'email' => 'gestor@example.test',
            'password' => 'password',
            'rol' => 'gestion',
        ]));

        $unidad = UnidadResponsable::create(['nombre' => 'Ingeniería', 'tipo' => 'Facultad']);
        $tipo = TipoInstrumento::create(['nombre' => 'Pasantía', 'categoria' => 'Pasantía']);

        $response = $this->postJson('/api/convenios', [
            'titulo' => 'Convenio de prueba',
            'tipo_instrumento_id' => $tipo->id,
            'unidad_id' => $unidad->id,
            'representantes' => [[
                'nombre' => 'Ana',
                'apellido' => 'Pérez',
                'cargo' => 'Directora',
                'email' => 'ana@example.test',
                'dni' => '12345678',
                'cuil' => '20-12345678-9',
            ]],
            'detalle_pasantia' => [
                'fecha_inicio_pasantia' => '2027-01-10',
                'fecha_fin_pasantia' => '2027-06-10',
                'carga_horaria_semanal' => '20',
                'asignacion_estimulo' => '150000',
            ],
        ]);

        $response->assertCreated()
            ->assertJsonPath('estado', 'borrador')
            ->assertJsonPath('unidad.id', $unidad->id)
            ->assertJsonPath('representantes.0.apellido', 'Pérez');
        $this->assertSame(
            '2027-01-10',
            substr($response->json('detalle_pasantia.fecha_inicio'), 0, 10)
        );

        $convenioId = $response->json('id');
        $this->assertMatchesRegularExpression('/^CONV-\d{4}-[A-Z0-9]{8}$/', $response->json('codigo'));
        $this->assertDatabaseHas('convenios', [
            'id' => $convenioId,
            'estado' => 'borrador',
            'unidad_responsable_id' => $unidad->id,
        ]);
        $this->assertDatabaseHas('partes', ['convenio_id' => $convenioId]);
        $this->assertDatabaseHas('representantes', [
            'nombre' => 'Ana',
            'apellido' => 'Pérez',
            'dni' => '12345678',
        ]);
        $this->assertDatabaseHas('detalle_pasantias', [
            'convenio_id' => $convenioId,
            'carga_horaria_semanal' => '20',
            'asignacion_estimulo' => '150000',
        ]);

        $this->getJson('/api/convenios?estado=borrador')
            ->assertOk()
            ->assertJsonPath('data.0.id', $convenioId);

        Sanctum::actingAs(User::create([
            'name' => 'Administrador',
            'email' => 'admin@example.test',
            'password' => 'password',
            'rol' => 'administracion',
        ]));
        $this->getJson('/api/convenios?estado=borrador')
            ->assertOk()
            ->assertJsonPath('data.0.id', $convenioId);
    }

    public function test_it_persists_the_selected_reviewer_when_sending_a_draft_to_review(): void
    {
        $gestor = User::create([
            'name' => 'Gestor',
            'email' => 'gestor@example.test',
            'password' => 'password',
            'rol' => 'gestion',
        ]);
        $revisor = User::create([
            'name' => 'Revisor',
            'email' => 'revisor@example.test',
            'password' => 'password',
            'rol' => 'revision',
        ]);
        Sanctum::actingAs($gestor);

        $unidad = UnidadResponsable::create(['nombre' => 'Ingeniería', 'tipo' => 'Facultad']);
        $tipo = TipoInstrumento::create(['nombre' => 'Convenio marco', 'categoria' => 'Colaboración']);
        $convenio = $this->postJson('/api/convenios', [
            'titulo' => 'Convenio para revisión',
            'tipo_instrumento_id' => $tipo->id,
            'unidad_id' => $unidad->id,
        ])->assertCreated()->json();

        $this->getJson('/api/users')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', $revisor->id);

        $this->putJson("/api/convenios/{$convenio['id']}", [
            'estado' => 'revision',
            'revisor_id' => $revisor->id,
            'fundamento' => 'Asignación para revisión',
        ])->assertOk()
            ->assertJsonPath('estado', 'revision')
            ->assertJsonPath('revisor.id', $revisor->id);

        $this->assertDatabaseHas('convenios', [
            'id' => $convenio['id'],
            'estado' => 'revision',
            'revisor_id' => $revisor->id,
        ]);
    }

    public function test_convenio_listing_includes_status_history_for_returned_drafts(): void
    {
        $gestor = User::create([
            'name' => 'Gestor',
            'email' => 'gestor@example.test',
            'password' => 'password',
            'rol' => 'gestion',
        ]);
        $revisor = User::create([
            'name' => 'Revisor',
            'email' => 'revisor@example.test',
            'password' => 'password',
            'rol' => 'revision',
        ]);
        Sanctum::actingAs($gestor);

        $unidad = UnidadResponsable::create(['nombre' => 'Ingeniería', 'tipo' => 'Facultad']);
        $tipo = TipoInstrumento::create(['nombre' => 'Convenio marco', 'categoria' => 'Colaboración']);
        $convenio = Convenio::create([
            'fecha_creacion' => now()->toDateString(),
            'titulo' => 'Convenio devuelto para corregir',
            'estado' => 'borrador',
            'codigo' => 'CONV-DEVUELTO-001',
            'tipo_instrumento_id' => $tipo->id,
            'unidad_responsable_id' => $unidad->id,
        ]);
        $convenio->historialEstados()->create([
            'user_id' => $revisor->id,
            'fundamento' => 'Completar documentación',
            'fecha_cambio' => now(),
            'estado_anterior' => 'revision',
            'estado_nuevo' => 'borrador',
        ]);

        $this->getJson('/api/convenios?estado=borrador')
            ->assertOk()
            ->assertJsonPath('data.0.id', $convenio->id)
            ->assertJsonPath('data.0.historial_estados.0.estado_anterior', 'revision')
            ->assertJsonPath('data.0.historial_estados.0.estado_nuevo', 'borrador');
    }
}
