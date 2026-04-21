<?php

namespace App\Traits;

trait CategorizesFtpErrors
{
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
            str_contains($errorLower, 'errno 110') ||
            str_contains($errorLower, 'errno 111')) {
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
}
