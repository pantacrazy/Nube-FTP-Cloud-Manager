<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function getCredentials(): array
    {
        return [
            'name' => 'TestUser',
            'password' => 'TestPassword123!',
        ];
    }

    public function test_usuario_puede_ver_pagina_de_login(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
    }

    public function test_usuario_puede_ver_pagina_de_registro(): void
    {
        $response = $this->get('/register');
        $response->assertStatus(200);
        $response->assertViewIs('auth.register');
    }

    public function test_usuario_puede_registrarse(): void
    {
        $credentials = array_merge($this->getCredentials(), [
            'password_confirmation' => 'TestPassword123!',
        ]);

        $response = $this->post('/register', $credentials);

        $response->assertRedirect('/');
        $this->assertDatabaseHas('users', [
            'name' => 'TestUser',
            'role' => 'admin',
        ]);
    }

    public function test_usuario_no_puede_registrarse_con_passwords_diferentes(): void
    {
        $response = $this->post('/register', [
            'name' => 'TestUser',
            'password' => 'TestPassword123!',
            'password_confirmation' => 'DifferentPassword!',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_usuario_no_puede_registrarse_sin_nombre(): void
    {
        $response = $this->post('/register', [
            'name' => '',
            'password' => 'TestPassword123!',
            'password_confirmation' => 'TestPassword123!',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_usuario_no_puede_registrarse_sin_password(): void
    {
        $response = $this->post('/register', [
            'name' => 'TestUser',
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_usuario_puede_iniciar_sesion(): void
    {
        $user = User::factory()->create([
            'name' => 'TestUser',
            'password' => 'TestPassword123!',
            'role' => 'admin',
        ]);

        $response = $this->post('/login', [
            'name' => 'TestUser',
            'password' => 'TestPassword123!',
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticated();
    }

    public function test_usuario_no_puede_iniciar_sesion_con_password_incorrecto(): void
    {
        $user = User::factory()->create([
            'name' => 'TestUser',
            'password' => 'CorrectPassword!',
            'role' => 'admin',
        ]);

        $response = $this->post('/login', [
            'name' => 'TestUser',
            'password' => 'WrongPassword!',
        ]);

        $response->assertSessionHasErrors();
        $this->assertGuest();
    }

    public function test_usuario_no_puede_iniciar_sesion_con_usuario_incorrecto(): void
    {
        $user = User::factory()->create([
            'name' => 'ExistingUser',
            'password' => 'TestPassword123!',
            'role' => 'admin',
        ]);

        $response = $this->post('/login', [
            'name' => 'NonExistentUser',
            'password' => 'TestPassword123!',
        ]);

        $response->assertSessionHasErrors();
        $this->assertGuest();
    }

    public function test_usuario_puede_cerrar_sesion(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->actingAs($user);
        $this->assertAuthenticated();

        $response = $this->post('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_usuario_autenticado_puede_ver_perfil(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($user)->get('/profile');

        $response->assertStatus(200);
        $response->assertViewIs('auth.profile');
    }

    public function test_usuario_autenticado_puede_actualizar_perfil(): void
    {
        $user = User::factory()->create([
            'name' => 'OldName',
            'role' => 'admin',
        ]);

        $response = $this->actingAs($user)->put('/profile', [
            'name' => 'NewName',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('users', [
            'name' => 'NewName',
        ]);
    }

    public function test_usuario_puede_ver_pagina_de_cambio_de_password(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($user)->get('/change-password');

        $response->assertStatus(200);
    }

    public function test_usuario_puede_cambiar_su_password(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('OldPassword123!'),
            'role' => 'admin',
        ]);

        $response = $this->actingAs($user)->put('/change-password', [
            'current_password' => 'OldPassword123!',
            'password' => 'NewPassword456!',
            'password_confirmation' => 'NewPassword456!',
        ]);

        $response->assertSessionHas('success');
    }

    public function test_usuario_no_puede_cambiar_password_con_password_incorrecto(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('CorrectPassword!'),
            'role' => 'admin',
        ]);

        $response = $this->actingAs($user)->put('/change-password', [
            'current_password' => 'WrongPassword!',
            'password' => 'NewPassword456!',
            'password_confirmation' => 'NewPassword456!',
        ]);

        $response->assertSessionHasErrors('current_password');
    }

    public function test_invitado_es_redirigido_a_login(): void
    {
        $response = $this->get('/nubes');
        $response->assertRedirect('/login');
    }
}
