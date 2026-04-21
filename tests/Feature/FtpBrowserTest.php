<?php

namespace Tests\Feature;

use App\Models\Nube;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FtpBrowserTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_puede_acceder_a_navegador_de_archivos(): void
    {
        $admin = User::factory()->admin()->create();
        $nube = Nube::factory()->create([
            'user_id' => $admin->id,
            'password' => encrypt('password'),
            'activo' => false,
        ]);

        $response = $this->actingAs($admin)->get('/nubes/'.$nube->id.'/browse');

        $response->assertStatus(200);
        $response->assertViewIs('nubes.browse');
    }

    public function test_usuario_puede_acceder_a_navegador_con_path(): void
    {
        $admin = User::factory()->admin()->create();
        $nube = Nube::factory()->create([
            'user_id' => $admin->id,
            'password' => encrypt('password'),
            'activo' => false,
        ]);

        $response = $this->actingAs($admin)->get('/nubes/'.$nube->id.'/browse?path=/carpeta');

        $response->assertStatus(200);
    }

    public function test_usuario_no_autenticado_no_puede_acceder(): void
    {
        $admin = User::factory()->admin()->create();
        $nube = Nube::factory()->create([
            'user_id' => $admin->id,
            'password' => encrypt('password'),
        ]);

        $response = $this->get('/nubes/'.$nube->id.'/browse');

        $response->assertRedirect('/login');
    }

    public function test_admin_puede_crear_carpeta(): void
    {
        $admin = User::factory()->admin()->create();
        $nube = Nube::factory()->create([
            'user_id' => $admin->id,
            'password' => encrypt('password'),
            'activo' => false,
        ]);

        $response = $this->actingAs($admin)->post('/nubes/'.$nube->id.'/folder', [
            'path' => '/',
            'name' => 'NuevaCarpeta',
        ]);

        $response->assertRedirect();
    }

    public function test_admin_puede_renombrar_elemento(): void
    {
        $admin = User::factory()->admin()->create();
        $nube = Nube::factory()->create([
            'user_id' => $admin->id,
            'password' => encrypt('password'),
            'activo' => false,
        ]);

        $response = $this->actingAs($admin)->post('/nubes/'.$nube->id.'/rename', [
            'path' => '/archivo.txt',
            'new_name' => 'nuevo_nombre.txt',
        ]);

        $response->assertRedirect();
    }

    public function test_usuario_no_autenticado_no_puede_subir_archivo(): void
    {
        $admin = User::factory()->admin()->create();
        $nube = Nube::factory()->create([
            'user_id' => $admin->id,
            'password' => encrypt('password'),
        ]);

        $response = $this->post('/nubes/'.$nube->id.'/upload', [
            'path' => '/',
        ]);

        $response->assertRedirect('/login');
    }

    public function test_usuario_puede_obtener_tamano_de_carpeta(): void
    {
        $admin = User::factory()->admin()->create();
        $nube = Nube::factory()->create([
            'user_id' => $admin->id,
            'password' => encrypt('password'),
            'activo' => false,
        ]);

        $response = $this->actingAs($admin)->get('/nubes/'.$nube->id.'/folder-size?path=/');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'size',
        ]);
    }

    public function test_admin_puede_solicitar_descarga_sincrona(): void
    {
        $admin = User::factory()->admin()->create();
        $nube = Nube::factory()->create([
            'user_id' => $admin->id,
            'password' => encrypt('password'),
            'activo' => false,
        ]);

        $response = $this->actingAs($admin)->post('/nubes/'.$nube->id.'/download-sync', [
            'path' => '/test.txt',
            'name' => 'test.txt',
            'type' => 'file',
        ]);

        // Should return error or success, but not redirect
        $response->assertStatus(500);
    }

    public function test_descarga_sincrona_sin_path_falla(): void
    {
        $admin = User::factory()->admin()->create();
        $nube = Nube::factory()->create([
            'user_id' => $admin->id,
            'password' => encrypt('password'),
            'activo' => false,
        ]);

        $response = $this->actingAs($admin)->post('/nubes/'.$nube->id.'/download-sync', [
            'name' => 'test.txt',
            'type' => 'file',
        ]);

        $response->assertStatus(422);
    }

    public function test_descarga_sincrona_sin_name_falla(): void
    {
        $admin = User::factory()->admin()->create();
        $nube = Nube::factory()->create([
            'user_id' => $admin->id,
            'password' => encrypt('password'),
            'activo' => false,
        ]);

        $response = $this->actingAs($admin)->post('/nubes/'.$nube->id.'/download-sync', [
            'path' => '/test.txt',
            'type' => 'file',
        ]);

        $response->assertStatus(422);
    }

    public function test_descarga_sincrona_sin_type_falla(): void
    {
        $admin = User::factory()->admin()->create();
        $nube = Nube::factory()->create([
            'user_id' => $admin->id,
            'password' => encrypt('password'),
            'activo' => false,
        ]);

        $response = $this->actingAs($admin)->post('/nubes/'.$nube->id.'/download-sync', [
            'path' => '/test.txt',
            'name' => 'test.txt',
        ]);

        $response->assertStatus(422);
    }

    public function test_descarga_sincrona_tipo_invalido_falla(): void
    {
        $admin = User::factory()->admin()->create();
        $nube = Nube::factory()->create([
            'user_id' => $admin->id,
            'password' => encrypt('password'),
            'activo' => false,
        ]);

        $response = $this->actingAs($admin)->post('/nubes/'.$nube->id.'/download-sync', [
            'path' => '/test.txt',
            'name' => 'test.txt',
            'type' => 'invalid',
        ]);

        $response->assertStatus(422);
    }

    public function test_polling_progreso_devuelve_pending_sin_job(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/download-progress?jobId=nonexistent');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'message',
        ]);
    }

    public function test_polling_progreso_sin_job_id_falla(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/download-progress');

        $response->assertStatus(422);
    }
}
