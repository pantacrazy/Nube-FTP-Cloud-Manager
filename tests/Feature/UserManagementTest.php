<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_puede_ver_lista_de_usuarios(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->user()->count(3)->create();

        $response = $this->actingAs($admin)->get('/users');

        $response->assertStatus(200);
        $response->assertViewIs('usuarios.index');
    }

    public function test_usuario_regular_no_puede_ver_lista_de_usuarios(): void
    {
        $user = User::factory()->user()->create();

        $response = $this->actingAs($user)->get('/users');

        $response->assertStatus(403);
    }

    public function test_admin_puede_crear_usuario(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/users', [
            'name' => 'NewUser',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role' => 'user',
        ]);

        $response->assertRedirect('/users');
        $this->assertDatabaseHas('users', [
            'name' => 'NewUser',
            'role' => 'user',
        ]);
    }

    public function test_admin_puede_ver_usuario(): void
    {
        $admin = User::factory()->admin()->create();
        $targetUser = User::factory()->user()->create(['name' => 'TargetUser']);

        $response = $this->actingAs($admin)->get('/user/'.$targetUser->id);

        $response->assertStatus(200);
        $response->assertViewIs('usuarios.show');
    }

    public function test_admin_puede_editar_usuario(): void
    {
        $admin = User::factory()->admin()->create();
        $targetUser = User::factory()->user()->create(['name' => 'TargetUser']);

        $response = $this->actingAs($admin)->put('/user/'.$targetUser->id, [
            'name' => 'UpdatedUser',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('users', [
            'name' => 'UpdatedUser',
        ]);
    }

    public function test_admin_puede_eliminar_usuario(): void
    {
        $admin = User::factory()->admin()->create();
        $targetUser = User::factory()->user()->create();

        $response = $this->actingAs($admin)->delete('/user/'.$targetUser->id);

        $response->assertRedirect('/users');
        $this->assertDatabaseMissing('users', [
            'id' => $targetUser->id,
        ]);
    }

    public function test_admin_no_puede_eliminarse_a_si_mismo(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->delete('/user/'.$admin->id);

        $response->assertStatus(403);
        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
        ]);
    }

    public function test_admin_puede_cambiar_rol_de_usuario(): void
    {
        $admin = User::factory()->admin()->create();
        $targetUser = User::factory()->user()->create(['role' => 'user']);

        $response = $this->actingAs($admin)->put('/user/'.$targetUser->id.'/edit-role', [
            'role' => 'admin',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('users', [
            'id' => $targetUser->id,
            'role' => 'admin',
        ]);
    }

    public function test_admin_puede_resetear_password_de_usuario(): void
    {
        $admin = User::factory()->admin()->create();
        $targetUser = User::factory()->user()->create();

        $response = $this->actingAs($admin)->put('/user/'.$targetUser->id.'/reset-password', [
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertSessionHas('success');
    }

    public function test_usuario_puede_editar_su_propio_perfil(): void
    {
        $user = User::factory()->user()->create(['name' => 'MyName']);

        $response = $this->actingAs($user)->put('/user/'.$user->id, [
            'name' => 'MyNewName',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('users', [
            'name' => 'MyNewName',
        ]);
    }

    public function test_usuario_no_puede_editar_otro_usuario(): void
    {
        $user = User::factory()->user()->create(['name' => 'UserOne']);
        $otherUser = User::factory()->user()->create(['name' => 'UserTwo']);

        $response = $this->actingAs($user)->put('/user/'.$otherUser->id, [
            'name' => 'HackedName',
        ]);

        $response->assertStatus(403);
    }

    public function test_crear_usuario_sin_nombre_falla(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/users', [
            'name' => '',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role' => 'user',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_crear_usuario_sin_password_falla(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/users', [
            'name' => 'NewUser',
            'password' => '',
            'password_confirmation' => '',
            'role' => 'user',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_crear_usuario_con_passwords_diferentes_falla(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/users', [
            'name' => 'NewUser',
            'password' => 'Password1!',
            'password_confirmation' => 'Password2!',
            'role' => 'user',
        ]);

        $response->assertSessionHasErrors('password');
    }
}
