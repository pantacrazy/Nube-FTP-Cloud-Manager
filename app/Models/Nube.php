<?php

namespace App\Models;

use App\Traits\CategorizesFtpErrors;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Nube extends Model
{
    use CategorizesFtpErrors, HasFactory;

    protected $fillable = [
        'nombre',
        'host',
        'puerto',
        'usuario',
        'password',
        'ruta_raiz',
        'tipo_conexion',
        'ssl_pasv',
        'timeout',
        'activo',
        'descripcion',
        'user_id',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'puerto' => 'integer',
        'ssl_pasv' => 'boolean',
        'timeout' => 'integer',
        'activo' => 'boolean',
    ];

    protected function setRutaRaizAttribute(?string $value): void
    {
        if ($value === null || trim($value) === '' || trim($value) === '/') {
            $this->attributes['ruta_raiz'] = '/';
        } else {
            $normalized = '/'.trim($value, '/');
            $this->attributes['ruta_raiz'] = $normalized;
        }
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getTipoConexionLabel(): string
    {
        return match ($this->tipo_conexion) {
            'ftps' => 'FTPS (FTP sobre SSL)',
            'sftp' => 'SFTP (SSH File Transfer)',
            default => 'FTP',
        };
    }

    public function getConnectionString(): string
    {
        return sprintf('%s://%s:%d%s', $this->tipo_conexion, $this->host, $this->puerto, $this->ruta_raiz);
    }

    public function isOnline(): bool
    {
        return $this->checkConnectionStatus()['online'];
    }

    public function checkConnectionStatus(): array
    {
        try {
            $this->getDecryptedPassword();

            $service = new \App\Services\FtpService($this);
            $service->connect();
            $service->disconnect();

            return [
                'online' => true,
                'error' => null,
                'error_type' => null,
            ];
        } catch (DecryptException $e) {
            $message = 'Error de configuración: La contraseña está corrupta. Por favor, actualiza la contraseña de esta fuente de datos.';

            return [
                'online' => false,
                'error' => $message,
                'error_type' => 'config',
            ];
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $errorType = $this->categorizeError($errorMessage);

            return [
                'online' => false,
                'error' => $this->getHumanReadableError($errorMessage, $errorType),
                'error_type' => $errorType,
            ];
        }
    }

    protected function getDecryptedPassword(): string
    {
        return decrypt($this->password);
    }

    protected function categorizeError(string $error): string
    {
        $errorLower = strtolower($error);

        if (str_contains($errorLower, 'authentication') ||
            str_contains($errorLower, 'credential') ||
            str_contains($errorLower, 'login') ||
            str_contains($errorLower, 'password') ||
            str_contains($errorLower, 'usuario') ||
            str_contains($errorLower, 'incorrecta')) {
            return 'auth';
        }

        if (str_contains($errorLower, 'connection refused') ||
            str_contains($errorLower, 'timeout') ||
            str_contains($errorLower, 'unable to connect') ||
            str_contains($errorLower, 'no route to host') ||
            str_contains($errorLower, 'network is unreachable') ||
            str_contains($errorLower, 'connection timed out') ||
            str_contains($errorLower, 'errno 110')) {
            return 'network';
        }

        if (str_contains($errorLower, 'ssl') ||
            str_contains($errorLower, 'certificate') ||
            str_contains($errorLower, 'tls') ||
            str_contains($errorLower, 'mac') ||
            str_contains($errorLower, 'mac is invalid')) {
            return 'ssl';
        }

        if (str_contains($errorLower, 'permission') ||
            str_contains($errorLower, 'denied') ||
            str_contains($errorLower, 'acceso')) {
            return 'permission';
        }

        return 'unknown';
    }

    protected function getHumanReadableError(string $error, string $type): string
    {
        return match ($type) {
            'auth' => 'Credenciales incorrectas. Verifica el usuario y contraseña.',
            'network' => 'No se puede conectar al servidor. Verifica que el servidor esté activo y la dirección IP/puerto sean correctos.',
            'ssl' => 'Error de conexión segura (SSL/TLS). El servidor puede tener una configuración de seguridad incompatible.',
            'permission' => 'Permiso denegado. Verifica que el usuario tenga permisos de acceso.',
            default => 'Error de conexión: '.$error,
        };
    }

    public function getConnectionError(): ?string
    {
        $status = $this->checkConnectionStatus();

        return $status['error'];
    }
}
