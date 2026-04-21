<?php

namespace Tests\Unit;

use App\Models\Nube;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NubeModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_nube_tiene_relacion_usuario(): void
    {
        $user = User::factory()->create();
        $nube = Nube::factory()->create([
            'user_id' => $user->id,
            'password' => encrypt('password'),
        ]);

        $this->assertInstanceOf(User::class, $nube->user);
    }

    public function test_tipo_conexion_label_ftp(): void
    {
        $nube = new Nube(['tipo_conexion' => 'ftp']);

        $this->assertEquals('FTP', $nube->getTipoConexionLabel());
    }

    public function test_tipo_conexion_label_ftps(): void
    {
        $nube = new Nube(['tipo_conexion' => 'ftps']);

        $this->assertEquals('FTPS (FTP sobre SSL)', $nube->getTipoConexionLabel());
    }

    public function test_tipo_conexion_label_sftp(): void
    {
        $nube = new Nube(['tipo_conexion' => 'sftp']);

        $this->assertEquals('SFTP (SSH File Transfer)', $nube->getTipoConexionLabel());
    }

    public function test_get_connection_string(): void
    {
        $nube = new Nube([
            'tipo_conexion' => 'ftp',
            'usuario' => 'testuser',
            'host' => 'ftp.example.com',
            'puerto' => 21,
            'ruta_raiz' => '/files',
        ]);

        $result = $nube->getConnectionString();

        $this->assertStringContainsString('ftp://', $result);
        $this->assertStringContainsString('testuser@', $result);
        $this->assertStringContainsString('ftp.example.com', $result);
        $this->assertStringContainsString(':21', $result);
    }

    public function test_categorize_error_auth(): void
    {
        $nube = new Nube;

        // Access private method via reflection
        $reflection = new \ReflectionClass($nube);
        $method = $reflection->getMethod('categorizeError');
        $method->setAccessible(true);

        $this->assertEquals('auth', $method->invoke($nube, 'Authentication failed'));
        $this->assertEquals('auth', $method->invoke($nube, 'Credenciales incorrectas'));
        $this->assertEquals('auth', $method->invoke($nube, 'Login failed'));
        $this->assertEquals('auth', $method->invoke($nube, 'Password incorrecta'));
    }

    public function test_categorize_error_network(): void
    {
        $nube = new Nube;

        $reflection = new \ReflectionClass($nube);
        $method = $reflection->getMethod('categorizeError');
        $method->setAccessible(true);

        $this->assertEquals('network', $method->invoke($nube, 'Connection refused'));
        $this->assertEquals('network', $method->invoke($nube, 'Connection timed out'));
        $this->assertEquals('network', $method->invoke($nube, 'Unable to connect'));
        $this->assertEquals('network', $method->invoke($nube, 'No route to host'));
    }

    public function test_categorize_error_ssl(): void
    {
        $nube = new Nube;

        $reflection = new \ReflectionClass($nube);
        $method = $reflection->getMethod('categorizeError');
        $method->setAccessible(true);

        $this->assertEquals('ssl', $method->invoke($nube, 'SSL certificate error'));
        $this->assertEquals('ssl', $method->invoke($nube, 'TLS connection failed'));
        $this->assertEquals('ssl', $method->invoke($nube, 'MAC is invalid'));
    }

    public function test_categorize_error_permission(): void
    {
        $nube = new Nube;

        $reflection = new \ReflectionClass($nube);
        $method = $reflection->getMethod('categorizeError');
        $method->setAccessible(true);

        $this->assertEquals('permission', $method->invoke($nube, 'Permission denied'));
        $this->assertEquals('permission', $method->invoke($nube, 'Access denied'));
    }

    public function test_get_human_readable_error_auth(): void
    {
        $nube = new Nube;

        $reflection = new \ReflectionClass($nube);
        $method = $reflection->getMethod('getHumanReadableError');
        $method->setAccessible(true);

        $result = $method->invoke($nube, 'Some error', 'auth');

        $this->assertStringContainsString('Credenciales', $result);
    }

    public function test_get_human_readable_error_network(): void
    {
        $nube = new Nube;

        $reflection = new \ReflectionClass($nube);
        $method = $reflection->getMethod('getHumanReadableError');
        $method->setAccessible(true);

        $result = $method->invoke($nube, 'Some error', 'network');

        $this->assertStringContainsString('conectar', $result);
    }

    public function test_get_human_readable_error_ssl(): void
    {
        $nube = new Nube;

        $reflection = new \ReflectionClass($nube);
        $method = $reflection->getMethod('getHumanReadableError');
        $method->setAccessible(true);

        $result = $method->invoke($nube, 'Some SSL error', 'ssl');

        $this->assertStringContainsString('SSL', $result);
    }

    public function test_nube_fillable(): void
    {
        $data = [
            'nombre' => 'Test Nube',
            'host' => 'ftp.example.com',
            'puerto' => 21,
            'usuario' => 'user',
            'password' => 'password',
            'ruta_raiz' => '/',
            'tipo_conexion' => 'ftp',
            'ssl_pasv' => true,
            'timeout' => 30,
            'activo' => true,
            'descripcion' => 'Test',
            'user_id' => 1,
        ];

        $nube = new Nube($data);

        foreach ($data as $key => $value) {
            $this->assertEquals($value, $nube->{$key});
        }
    }

    public function test_nube_casts(): void
    {
        $nube = new Nube([
            'puerto' => '21',
            'ssl_pasv' => '1',
            'timeout' => '30',
            'activo' => '1',
        ]);

        $this->assertIsInt($nube->puerto);
        $this->assertIsBool($nube->ssl_pasv);
        $this->assertIsInt($nube->timeout);
        $this->assertIsBool($nube->activo);
    }

    public function test_nube_hidden_password(): void
    {
        $user = User::factory()->create();
        $nube = Nube::factory()->create([
            'user_id' => $user->id,
            'password' => encrypt('secret'),
        ]);

        $hidden = $nube->getHidden();

        $this->assertContains('password', $hidden);
    }
}
