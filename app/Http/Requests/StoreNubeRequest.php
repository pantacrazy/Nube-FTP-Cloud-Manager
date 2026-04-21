<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNubeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:255',
            'host' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z0-9][a-zA-Z0-9\-\.]*[a-zA-Z0-9]$/'],
            'puerto' => 'required|integer|min:1|max:65535',
            'usuario' => 'required|string|max:255',
            'password' => ['required', 'string', 'min:4', 'max:255'],
            'ruta_raiz' => 'nullable|string|max:500',
            'tipo_conexion' => 'required|in:ftp,ftps,sftp',
            'ssl_pasv' => 'nullable|boolean',
            'timeout' => 'required|integer|min:5|max:120',
            'activo' => 'nullable|boolean',
            'descripcion' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'host.regex' => 'El host debe ser un hostname o IP válida (sin protocol, ej: ftp.ejemplo.com o 192.168.1.1).',
            'password.min' => 'La contraseña debe tener al menos 4 caracteres.',
        ];
    }
}
