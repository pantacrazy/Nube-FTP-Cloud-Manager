<?php

namespace Tests\Feature;

use App\Models\Nube;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NubeManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function createNubeData(): array
    {
        return [
            'nombre' => 'Test FTP Server',
            'host' => 'ftp.example.com',
            'puerto' => 21,
            'usuario' => 'testuser',
            'password' => 'testpassword',
            'ruta_raiz' => '/',
            'tipo_conexion' => 'ftp',
            'ssl_pasv' => false,
            'timeout' => 30,
            'activo' => true,
            'descripcion' => 'Test description',
        ];
    }

    public function test_admin_puede_ver_lista_de_nubes(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/nubes');

        $response->assertStatus(200);
        $response->assertViewIs('nubes.index');
    }

    public function test_usuario_regular_puede_ver_lista_de_nubes(): void
    {
        $user = User::factory()->user()->create();

        $response = $this->actingAs($user)->get('/nubes');

        $response->assertStatus(200);
    }

    public function test_usuario_no_autenticado_no_puede_ver_nubes(): void
    {
        $response = $this->get('/nubes');

        $response->assertRedirect('/login');
    }

    public function test_admin_puede_ver_formulario_de_crear_nube(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/nubes/create');

        $response->assertStatus(200);
        $response->assertViewIs('nubes.create');
    }

    public function test_usuario_regular_no_puede_ver_formulario_de_crear_nube(): void
    {
        $user = User::factory()->user()->create();

        $response = $this->actingAs($user)->get('/nubes/create');

        $response->assertStatus(403);
    }

    public function test_admin_puede_crear_nube(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/nubes', $this->createNubeData());

        $response->assertRedirect('/nubes');
        $this->assertDatabaseHas('nubes', [
            'nombre' => 'Test FTP Server',
            'host' => 'ftp.example.com',
        ]);
    }

    public function test_crear_nube_sin_nombre_falla(): void
    {
        $admin = User::factory()->admin()->create();

        $data = $this->createNubeData();
        unset($data['nombre']);

        $response = $this->actingAs($admin)->post('/nubes', $data);

        $response->assertSessionHasErrors('nombre');
    }

    public function test_crear_nube_sin_host_falla(): void
    {
        $admin = User::factory()->admin()->create();

        $data = $this->createNubeData();
        unset($data['host']);

        $response = $this->actingAs($admin)->post('/nubes', $data);

        $response->assertSessionHasErrors('host');
    }

    public function test_crear_nube_sin_password_falla(): void
    {
        $admin = User::factory()->admin()->create();

        $data = $this->createNubeData();
        unset($data['password']);

        $response = $this->actingAs($admin)->post('/nubes', $data);

        $response->assertSessionHasErrors('password');
    }

    public function test_crear_nube_con_tipo_invalido_falla(): void
    {
        $admin = User::factory()->admin()->create();

        $data = $this->createNubeData();
        $data['tipo_conexion'] = 'invalid_type';

        $response = $this->actingAs($admin)->post('/nubes', $data);

        $response->assertSessionHasErrors('tipo_conexion');
    }

    public function test_admin_puede_ver_nube(): void
    {
        $admin = User::factory()->admin()->create();
        $nube = Nube::factory()->create([
            'user_id' => $admin->id,
            'password' => encrypt('password'),
        ]);

        $response = $this->actingAs($admin)->get('/nubes/'.$nube->id);

        $response->assertStatus(200);
        $response->assertViewIs('nubes.show');
    }

    public function test_admin_puede_ver_formulario_de_editar_nube(): void
    {
        $admin = User::factory()->admin()->create();
        $nube = Nube::factory()->create([
            'user_id' => $admin->id,
            'password' => encrypt('password'),
        ]);

        $response = $this->actingAs($admin)->get('/nubes/'.$nube->id.'/edit');

        $response->assertStatus(200);
        $response->assertViewIs('nubes.edit');
    }

    public function test_usuario_regular_no_puede_editar_nube(): void
    {
        $user = User::factory()->user()->create();
        $nube = Nube::factory()->create([
            'user_id' => $user->id,
            'password' => encrypt('password'),
        ]);

        $response = $this->actingAs($user)->get('/nubes/'.$nube->id.'/edit');

        $response->assertStatus(403);
    }

    public function test_admin_puede_actualizar_nube(): void
    {
        $admin = User::factory()->admin()->create();
        $nube = Nube::factory()->create([
            'user_id' => $admin->id,
            'password' => encrypt('password'),
        ]);

        $response = $this->actingAs($admin)->put('/nubes/'.$nube->id, [
            'nombre' => 'Updated Name',
            'host' => $nube->host,
            'puerto' => $nube->puerto,
            'usuario' => $nube->usuario,
            'password' => '',
            'ruta_raiz' => $nube->ruta_raiz,
            'tipo_conexion' => $nube->tipo_conexion,
            'ssl_pasv' => $nube->ssl_pasv,
            'timeout' => $nube->timeout,
            'activo' => $nube->activo,
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('nubes', [
            'nombre' => 'Updated Name',
        ]);
    }

    public function test_admin_puede_eliminar_nube(): void
    {
        $admin = User::factory()->admin()->create();
        $nube = Nube::factory()->create([
            'user_id' => $admin->id,
            'password' => encrypt('password'),
        ]);

        $response = $this->actingAs($admin)->delete('/nubes/'.$nube->id);

        $response->assertRedirect('/nubes');
        $this->assertDatabaseMissing('nubes', [
            'id' => $nube->id,
        ]);
    }

    public function test_admin_puede_verificar_estado_de_nube(): void
    {
        $admin = User::factory()->admin()->create();
        $nube = Nube::factory()->create([
            'user_id' => $admin->id,
            'password' => encrypt('password'),
            'activo' => false,
        ]);

        $response = $this->actingAs($admin)->get('/nubes/'.$nube->id.'/status');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'online',
            'error',
            'error_type',
        ]);
    }

    public function test_nube_inactiva_muestra_estado_offline(): void
    {
        $admin = User::factory()->admin()->create();
        $nube = Nube::factory()->create([
            'user_id' => $admin->id,
            'password' => encrypt('password'),
            'activo' => false,
        ]);

        $response = $this->actingAs($admin)->get('/nubes/'.$nube->id.'/status');

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertFalse($data['online']);
    }
}
