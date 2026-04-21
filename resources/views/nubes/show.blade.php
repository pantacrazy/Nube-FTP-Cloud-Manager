@extends('layouts.app')

@section('title', 'Detalle de Fuente de Datos')

@section('content')
<div class="bg-gray-100 flex items-center justify-center py-6 sm:py-12 px-3 sm:px-4 min-h-[calc(100vh-100px)]">
    <div class="max-w-lg w-full bg-white rounded-2xl shadow-lg p-5 sm:p-8">
        <div class="flex flex-col gap-2 sm:flex-row sm:justify-between sm:items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">{{ $nube->nombre }}</h2>
            <a href="{{ route('nubes.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Volver</a>
        </div>

        <div class="space-y-4">
            <div class="flex flex-col gap-1 sm:flex-row sm:justify-between sm:items-center">
                <span class="text-sm font-medium text-gray-500">Host</span>
                <span class="text-gray-800">{{ $nube->host }}:{{ $nube->puerto }}</span>
            </div>

            <div class="flex flex-col gap-1 sm:flex-row sm:justify-between sm:items-center">
                <span class="text-sm font-medium text-gray-500">Tipo de conexión</span>
                <span class="text-gray-800">{{ $nube->getTipoConexionLabel() }}</span>
            </div>

            <div class="flex flex-col gap-1 sm:flex-row sm:justify-between sm:items-center">
                <span class="text-sm font-medium text-gray-500">Usuario</span>
                <span class="text-gray-800">{{ $nube->usuario }}</span>
            </div>

            <div class="flex flex-col gap-1 sm:flex-row sm:justify-between sm:items-center">
                <span class="text-sm font-medium text-gray-500">Ruta raíz</span>
                <span class="text-gray-800 font-mono bg-gray-100 px-2 py-1 rounded">{{ $nube->ruta_raiz }}</span>
            </div>

            <div class="flex flex-col gap-1 sm:flex-row sm:justify-between sm:items-center">
                <span class="text-sm font-medium text-gray-500">SSL Pasivo</span>
                <span class="text-gray-800">{{ $nube->ssl_pasv ? 'Sí' : 'No' }}</span>
            </div>

            <div class="flex flex-col gap-1 sm:flex-row sm:justify-between sm:items-center">
                <span class="text-sm font-medium text-gray-500">Timeout</span>
                <span class="text-gray-800">{{ $nube->timeout }} segundos</span>
            </div>

            <div class="flex flex-col gap-1 sm:flex-row sm:justify-between sm:items-center">
                <span class="text-sm font-medium text-gray-500">Estado</span>
                @if($nube->activo)
                    <span class="inline-block px-3 py-1 text-xs font-bold text-white rounded" style="background-color: #16a34a;">Activo</span>
                @else
                    <span class="inline-block px-3 py-1 text-xs font-bold text-white rounded" style="background-color: #dc2626;">Inactivo</span>
                @endif
            </div>

            <div class="flex flex-col gap-1 sm:flex-row sm:justify-between sm:items-center">
                <span class="text-sm font-medium text-gray-500">String de conexión</span>
                <span class="max-w-full break-all text-gray-800 font-mono text-xs bg-gray-100 px-2 py-1 rounded">{{ $nube->getConnectionString() }}</span>
            </div>

            @if($nube->descripcion)
                <div class="pt-4 border-t">
                    <span class="text-sm font-medium text-gray-500">Descripción</span>
                    <p class="text-gray-800 text-sm mt-1">{{ $nube->descripcion }}</p>
                </div>
            @endif

            <div class="pt-4 border-t flex flex-col gap-1 sm:flex-row sm:justify-between sm:items-center">
                <span class="text-sm font-medium text-gray-500">Creado</span>
                <span class="text-gray-800 text-sm">{{ $nube->created_at?->format('d/m/Y H:i') ?? 'N/A' }}</span>
            </div>

            <div class="flex flex-col gap-1 sm:flex-row sm:justify-between sm:items-center">
                <span class="text-sm font-medium text-gray-500">Actualizado</span>
                <span class="text-gray-800 text-sm">{{ $nube->updated_at?->format('d/m/Y H:i') ?? 'N/A' }}</span>
            </div>
        </div>

        @can('update', $nube)
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-8">
            <a href="{{ route('nubes.edit', $nube) }}" class="flex-1 text-center text-white font-semibold py-2 rounded-lg transition"
                style="background-color: #f59e0b;"
                onmouseover="this.style.backgroundColor='#d97706'"
                onmouseout="this.style.backgroundColor='#f59e0b'">
                Editar
            </a>
            <button onclick="testConnection({{ $nube->id }})" class="flex-1 text-white font-semibold py-2 rounded-lg transition"
                style="background-color: #8b5cf6;"
                onmouseover="this.style.backgroundColor='#7c3aed'"
                onmouseout="this.style.backgroundColor='#8b5cf6'">
                Probar conexión
            </button>
        </div>
        @endcan
    </div>
</div>

<div id="testModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-3 sm:p-4">
    <div class="bg-white rounded-lg p-4 sm:p-6 max-w-sm w-full max-h-[90vh] overflow-y-auto">
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
function testConnection(id) {
    const modal = document.getElementById('testModal');
    const loading = document.getElementById('testLoading');
    const result = document.getElementById('testResult');
    const message = document.getElementById('testMessage');

    modal.classList.remove('hidden');
    modal.classList.add('flex');
    loading.classList.remove('hidden');
    result.classList.add('hidden');

    fetch('/nubes/' + id + '/test', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        }
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
