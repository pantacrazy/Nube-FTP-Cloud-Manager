@extends('layouts.app')

@section('title', 'Editar Fuente de Datos')

@section('content')
<div class="bg-gray-100 flex items-center justify-center py-6 sm:py-12 px-3 sm:px-4 min-h-[calc(100vh-100px)]">
    <div class="max-w-lg w-full bg-white rounded-2xl shadow-lg p-5 sm:p-8">
        <div class="flex flex-col gap-2 sm:flex-row sm:justify-between sm:items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Editar Fuente de Datos</h2>
            <a href="{{ route('nubes.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Volver</a>
        </div>

        @if(session('success'))
            <div class="bg-green-100 text-green-700 text-sm px-4 py-3 rounded-lg mb-4">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('nubes.update', $nube) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre <span class="text-red-500">*</span></label>
                <input type="text" name="nombre" value="{{ old('nombre', $nube->nombre) }}"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('nombre') border-red-400 @enderror">
                @error('nombre')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Host <span class="text-red-500">*</span></label>
                <input type="text" name="host" value="{{ old('host', $nube->host) }}"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('host') border-red-400 @enderror">
                @error('host')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Puerto <span class="text-red-500">*</span></label>
                    <input type="number" name="puerto" value="{{ old('puerto', $nube->puerto) }}" min="1" max="65535"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('puerto') border-red-400 @enderror">
                    @error('puerto')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de conexión <span class="text-red-500">*</span></label>
                    <select name="tipo_conexion" class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('tipo_conexion') border-red-400 @enderror">
                        <option value="ftp" {{ old('tipo_conexion', $nube->tipo_conexion) === 'ftp' ? 'selected' : '' }}>FTP</option>
                        <option value="ftps" {{ old('tipo_conexion', $nube->tipo_conexion) === 'ftps' ? 'selected' : '' }}>FTPS</option>
                        <option value="sftp" {{ old('tipo_conexion', $nube->tipo_conexion) === 'sftp' ? 'selected' : '' }}>SFTP</option>
                    </select>
                    @error('tipo_conexion')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Usuario <span class="text-red-500">*</span></label>
                <input type="text" name="usuario" value="{{ old('usuario', $nube->usuario) }}"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('usuario') border-red-400 @enderror">
                @error('usuario')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Contraseña <span class="text-gray-400 font-normal">(dejar vacío para mantener)</span></label>
                <input type="password" name="password" placeholder="Nueva contraseña"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('password') border-red-400 @enderror">
                @error('password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Ruta raíz</label>
                <input type="text" name="ruta_raiz" value="{{ old('ruta_raiz', $nube->ruta_raiz) }}"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('ruta_raiz') border-red-400 @enderror">
                @error('ruta_raiz')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Timeout (seg)</label>
                    <input type="number" name="timeout" value="{{ old('timeout', $nube->timeout) }}" min="5" max="120"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('timeout') border-red-400 @enderror">
                    @error('timeout')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="flex items-end gap-4 pb-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="ssl_pasv" value="1" {{ old('ssl_pasv', $nube->ssl_pasv) ? 'checked' : '' }} class="h-4 w-4 text-blue-600 border-gray-300 rounded">
                        <span class="text-sm text-gray-700">SSL Pasv</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="activo" value="1" {{ old('activo', $nube->activo) ? 'checked' : '' }} class="h-4 w-4 text-blue-600 border-gray-300 rounded">
                        <span class="text-sm text-gray-700">Activo</span>
                    </label>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                <textarea name="descripcion" rows="3"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('descripcion') border-red-400 @enderror">{{ old('descripcion', $nube->descripcion) }}</textarea>
                @error('descripcion')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <button type="submit" class="w-full text-white font-bold py-3 rounded-lg transition"
                style="background-color: #16a34a;"
                onmouseover="this.style.backgroundColor='#15803d'"
                onmouseout="this.style.backgroundColor='#16a34a'">
                Actualizar fuente de datos
            </button>

        </form>

        <div class="mt-6 pt-6 border-t border-gray-200">
            <form action="{{ route('nubes.destroy', $nube) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar esta fuente de datos?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="w-full text-white font-bold py-3 rounded-lg transition"
                    style="background-color: #dc2626;"
                    onmouseover="this.style.backgroundColor='#b91c1c'"
                    onmouseout="this.style.backgroundColor='#dc2626'">
                    Eliminar fuente de datos
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
