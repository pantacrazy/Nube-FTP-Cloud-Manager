@extends('layouts.app')

@section('title', 'Nueva Fuente de Datos')

@section('content')
<div class="bg-gray-100 flex items-center justify-center py-6 sm:py-12 px-3 sm:px-4 min-h-[calc(100vh-100px)]">
    <div class="max-w-lg w-full bg-white rounded-2xl shadow-lg p-5 sm:p-8">
        <div class="flex flex-col gap-2 sm:flex-row sm:justify-between sm:items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Nueva Fuente de Datos</h2>
            <a href="{{ route('nubes.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Volver</a>
        </div>

        <form action="{{ route('nubes.store') }}" method="POST" class="space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre <span class="text-red-500">*</span></label>
                <input type="text" name="nombre" value="{{ old('nombre') }}" placeholder="Mi servidor FTP"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('nombre') border-red-400 @enderror">
                @error('nombre')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Host <span class="text-red-500">*</span></label>
                <input type="text" name="host" value="{{ old('host') }}" placeholder="ftp.ejemplo.com"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('host') border-red-400 @enderror">
                @error('host')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Puerto <span class="text-red-500">*</span></label>
                    <input type="number" name="puerto" value="{{ old('puerto', 21) }}" min="1" max="65535"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('puerto') border-red-400 @enderror">
                    @error('puerto')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de conexión <span class="text-red-500">*</span></label>
                    <select name="tipo_conexion" class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('tipo_conexion') border-red-400 @enderror">
                        <option value="ftp" {{ old('tipo_conexion') === 'ftp' ? 'selected' : '' }}>FTP</option>
                        <option value="ftps" {{ old('tipo_conexion') === 'ftps' ? 'selected' : '' }}>FTPS</option>
                        <option value="sftp" {{ old('tipo_conexion') === 'sftp' ? 'selected' : '' }}>SFTP</option>
                    </select>
                    @error('tipo_conexion')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Usuario <span class="text-red-500">*</span></label>
                <input type="text" name="usuario" value="{{ old('usuario') }}" placeholder="usuario_ftp"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('usuario') border-red-400 @enderror">
                @error('usuario')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Contraseña <span class="text-red-500">*</span></label>
                <input type="password" name="password" placeholder="Contraseña del servidor"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('password') border-red-400 @enderror">
                @error('password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Ruta raíz</label>
                <input type="text" name="ruta_raiz" value="{{ old('ruta_raiz', '/') }}" placeholder="/"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('ruta_raiz') border-red-400 @enderror">
                @error('ruta_raiz')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Timeout (seg)</label>
                    <input type="number" name="timeout" value="{{ old('timeout', 30) }}" min="5" max="120"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('timeout') border-red-400 @enderror">
                    @error('timeout')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="flex items-end gap-4 pb-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="hidden" name="ssl_pasv" value="0">
                        <input type="checkbox" name="ssl_pasv" value="1" {{ old('ssl_pasv') == '1' ? 'checked' : '' }} class="h-4 w-4 text-blue-600 border-gray-300 rounded">
                        <span class="text-sm text-gray-700">SSL Pasv</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="hidden" name="activo" value="0">
                        <input type="checkbox" name="activo" value="1" {{ old('activo', '1') == '1' ? 'checked' : '' }} class="h-4 w-4 text-blue-600 border-gray-300 rounded">
                        <span class="text-sm text-gray-700">Activo</span>
                    </label>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                <textarea name="descripcion" rows="3" placeholder="Descripción opcional de esta fuente de datos..."
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('descripcion') border-red-400 @enderror">{{ old('descripcion') }}</textarea>
                @error('descripcion')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <button type="submit" class="w-full text-white font-bold py-3 rounded-lg transition"
                style="background-color: #2563eb;"
                onmouseover="this.style.backgroundColor='#1d4ed8'"
                onmouseout="this.style.backgroundColor='#2563eb'">
                Guardar fuente de datos
            </button>

        </form>

        <div class="mt-4">
            <button type="button" onclick="testConnectionPreview()" id="testConnectionBtn"
                class="w-full text-white font-bold py-3 rounded-lg transition"
                style="background-color: #8b5cf6;"
                onmouseover="this.style.backgroundColor='#7c3aed'"
                onmouseout="this.style.backgroundColor='#8b5cf6'">
                Probar conexión antes de guardar
            </button>
        </div>
    </div>
</div>

<div id="testModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-3 sm:p-4">
    <div class="bg-white rounded-lg p-4 sm:p-6 max-w-sm w-full max-h-[90vh] overflow-y-auto mx-3 sm:mx-4">
        <h3 class="text-lg font-bold mb-4" id="testTitle">Probando conexión...</h3>
        <div class="flex items-center gap-3 mb-4" id="testLoading">
            <svg class="animate-spin h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-gray-600">Conectando al servidor...</span>
        </div>
        <div id="testResult" class="hidden">
            <p id="testMessage" class="text-sm"></p>
        </div>
        <button onclick="closeTestModal()" class="mt-4 w-full text-white font-bold py-2 rounded transition"
            style="background-color: #6b7280;">
            Cerrar
        </button>
    </div>
</div>

<script>
function testConnectionPreview() {
    const form = document.querySelector('form');
    const formData = new FormData(form);
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    
    const modal = document.getElementById('testModal');
    const loading = document.getElementById('testLoading');
    const result = document.getElementById('testResult');
    const message = document.getElementById('testMessage');

    modal.classList.remove('hidden');
    modal.classList.add('flex');
    loading.classList.remove('hidden');
    result.classList.add('hidden');

    fetch('/nubes/test-preview', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
        },
        body: JSON.stringify({
            host: formData.get('host'),
            puerto: formData.get('puerto'),
            usuario: formData.get('usuario'),
            password: formData.get('password'),
            ruta_raiz: formData.get('ruta_raiz'),
            tipo_conexion: formData.get('tipo_conexion'),
            timeout: formData.get('timeout'),
        }),
    })
    .then(res => res.json())
    .then(data => {
        loading.classList.add('hidden');
        result.classList.remove('hidden');
        message.textContent = data.message;
        message.className = data.success ? 'text-sm text-green-600 font-semibold' : 'text-sm text-red-600 font-semibold';
    })
    .catch(() => {
        loading.classList.add('hidden');
        result.classList.remove('hidden');
        message.textContent = 'Error de conexión con el servidor.';
        message.className = 'text-sm text-red-600 font-semibold';
    });
}

function closeTestModal() {
    const modal = document.getElementById('testModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}
</script>
@endsection
