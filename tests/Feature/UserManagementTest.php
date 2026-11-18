<?php

namespace Tests\Feature;

use App\Models\Api\Convenio;
use App\Models\Api\TipoInstrumento;
use App\Models\Api\UnidadResponsable;
use App\Models\Api\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_all_user_roles_without_exposing_passwords(): void
    {
        $admin = $this->createUser('Admin', 'admin@example.test', 'administracion');
        $manager = $this->createUser('Gestor', 'gestor@example.test', 'gestion');
        $reviewer = $this->createUser('Revisor', 'revisor@example.test', 'revision');
        $consultation = $this->createUser('Consulta', 'consulta@example.test', 'consulta');
        $dean = $this->createUser('Decano', 'decano@example.test', 'decano');
        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/users/manage')
            ->assertOk()
            ->assertJsonCount(4)
            ->assertJsonMissing(['id' => $admin->id]);

        foreach ($response->json() as $user) {
            $this->assertArrayNotHasKey('password', $user);
        }

        $response->assertJsonFragment(['id' => $manager->id, 'rol' => 'gestion'])
            ->assertJsonFragment(['id' => $reviewer->id, 'rol' => 'revision'])
            ->assertJsonFragment(['id' => $consultation->id, 'rol' => 'consulta'])
            ->assertJsonFragment(['id' => $dean->id, 'rol' => 'decano']);

        $this->getJson('/api/users')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', $reviewer->id);
    }

    public function test_non_admin_cannot_manage_users(): void
    {
        $manager = $this->createUser('Gestor', 'gestor@example.test', 'gestion');
        $target = $this->createUser('Usuario', 'usuario@example.test', 'consulta');
        Sanctum::actingAs($manager);

        $this->getJson('/api/users/manage')->assertForbidden();
        $this->putJson("/api/users/manage/{$target->id}", [
            'name' => 'Usuario modificado',
            'email' => 'usuario@example.test',
            'rol' => 'consulta',
        ])->assertForbidden();
        $this->deleteJson("/api/users/manage/{$target->id}")->assertForbidden();
    }

    public function test_admin_can_update_and_delete_a_user(): void
    {
        $admin = $this->createUser('Admin', 'admin@example.test', 'administracion');
        $target = $this->createUser('Usuario', 'usuario@example.test', 'consulta');
        Sanctum::actingAs($admin);

        $this->putJson("/api/users/manage/{$target->id}", [
            'name' => 'Usuario actualizado',
            'email' => 'actualizado@example.test',
            'rol' => 'gestion',
            'telefono' => '2302-555555',
        ])->assertOk()
            ->assertJsonPath('name', 'Usuario actualizado')
            ->assertJsonPath('rol', 'gestion')
            ->assertJsonPath('telefono', '2302-555555');

        $this->assertDatabaseHas('users', [
            'id' => $target->id,
            'email' => 'actualizado@example.test',
            'rol' => 'gestion',
        ]);

        $this->deleteJson("/api/users/manage/{$target->id}")->assertNoContent();
        $this->assertDatabaseMissing('users', ['id' => $target->id]);
    }

    public function test_admin_cannot_demote_or_delete_their_own_account(): void
    {
        $admin = $this->createUser('Admin', 'admin@example.test', 'administracion');
        Sanctum::actingAs($admin);

        $this->putJson("/api/users/manage/{$admin->id}", [
            'name' => $admin->name,
            'email' => $admin->email,
            'rol' => 'gestion',
        ])->assertUnprocessable();

        $this->deleteJson("/api/users/manage/{$admin->id}")->assertConflict();
    }

    public function test_user_with_status_history_cannot_be_deleted(): void
    {
        $admin = $this->createUser('Admin', 'admin@example.test', 'administracion');
        $gestor = $this->createUser('Gestor', 'gestor@example.test', 'gestion');
        Sanctum::actingAs($admin);

        $unidad = UnidadResponsable::create(['nombre' => 'Ingeniería', 'tipo' => 'Facultad']);
        $tipo = TipoInstrumento::create(['nombre' => 'Convenio marco', 'categoria' => 'Colaboración']);
        $convenio = Convenio::create([
            'fecha_creacion' => now()->toDateString(),
            'titulo' => 'Convenio con historial',
            'estado' => 'borrador',
            'codigo' => 'CONV-HISTORIAL-001',
            'tipo_instrumento_id' => $tipo->id,
            'unidad_responsable_id' => $unidad->id,
        ]);
        $convenio->historialEstados()->create([
            'user_id' => $gestor->id,
            'fundamento' => 'Prueba',
            'fecha_cambio' => now(),
            'estado_anterior' => 'revision',
            'estado_nuevo' => 'borrador',
        ]);

        $this->deleteJson("/api/users/manage/{$gestor->id}")
            ->assertConflict()
            ->assertJsonPath('message', 'No se puede eliminar este usuario porque figura en el historial de convenios.');

        $this->assertDatabaseHas('users', ['id' => $gestor->id]);
    }

    private function createUser(string $name, string $email, string $role): User
    {
        return User::create([
            'name' => $name,
            'email' => $email,
            'password' => 'password',
            'rol' => $role,
        ]);
    }
}
