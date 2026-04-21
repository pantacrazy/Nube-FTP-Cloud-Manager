@extends('layouts.app')

@section('title', $nube->nombre)

@push('styles')
<style>
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}
</style>
@endpush

@section('content')
<div class="bg-gray-100 py-4 sm:py-8">
    <div class="max-w-6xl mx-auto">
        {{-- Header --}}
        <div class="bg-white rounded-lg shadow p-4 mb-4">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="min-w-0 flex flex-col gap-2 sm:flex-row sm:items-center sm:gap-3">
                    <a href="{{ route('nubes.index') }}" class="text-gray-500 hover:text-gray-700 text-sm">← Fuentes</a>
                    <h1 class="text-lg sm:text-xl font-bold text-gray-800 truncate">{{ $nube->nombre }}</h1>
                    <span class="self-start text-xs px-2 py-1 rounded text-white" style="background-color: {{ $nube->tipo_conexion === 'ftps' ? '#f59e0b' : ($nube->tipo_conexion === 'sftp' ? '#8b5cf6' : '#6b7280') }};">
                        {{ strtoupper($nube->tipo_conexion) }}
                    </span>
                </div>
                <div class="grid grid-cols-1 sm:flex sm:items-center gap-2">
                    @if($permissions['can_upload'])
                        <button id="uploadActionBtn" onclick="document.getElementById('uploadModal').classList.remove('hidden');document.getElementById('uploadModal').classList.add('flex')" class="text-white text-sm font-bold py-2 px-4 rounded shadow transition"
                            style="background-color: #2563eb;"
                            onmouseover="this.style.backgroundColor='#1d4ed8'"
                            onmouseout="this.style.backgroundColor='#2563eb'">
                            ↑ Subir archivo
                        </button>
                    @endif
                    @if($permissions['can_write'])
                        <button id="folderActionBtn" onclick="document.getElementById('folderModal').classList.remove('hidden');document.getElementById('folderModal').classList.add('flex')" class="text-white text-sm font-bold py-2 px-4 rounded shadow transition"
                            style="background-color: #16a34a;"
                            onmouseover="this.style.backgroundColor='#15803d'"
                            onmouseout="this.style.backgroundColor='#16a34a'">
                            + Nueva carpeta
                        </button>
                    @endif
                </div>
            </div>

            {{-- Breadcrumbs --}}
            <div class="flex items-center gap-1 mt-3 text-sm overflow-x-auto whitespace-nowrap pb-1">
                <a href="{{ route('nubes.browse', $nube) }}" class="text-blue-600 hover:underline">
                    📁 {{ $nube->ruta_raiz ?: '/' }}
                </a>
                @foreach($breadcrumbs as $crumb)
                    <span class="text-gray-400">/</span>
                    <a href="{{ route('nubes.browse', ['nube' => $nube, 'path' => $crumb['path']]) }}" class="text-blue-600 hover:underline">
                        {{ $crumb['name'] }}
                    </a>
                @endforeach
            </div>

            @if(session('success'))
                <div class="bg-green-100 text-green-700 text-sm px-4 py-2 rounded-lg mt-3">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="bg-red-100 text-red-700 text-sm px-4 py-2 rounded-lg mt-3">
                    {{ session('error') }}
                </div>
            @endif
            @if(isset($connectionError) && $connectionError)
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mt-3">
                    <div class="flex items-start gap-3">
                        <span class="text-3xl">
                            @if(isset($errorType) && $errorType === 'auth') 🔐
                            @elseif(isset($errorType) && $errorType === 'network') 🌐
                            @elseif(isset($errorType) && $errorType === 'ssl') 🔒
                            @else ⚠️
                            @endif
                        </span>
                        <div class="flex-1">
                            <h3 class="font-bold text-red-800">Conexión perdida</h3>
                            <p class="text-red-600 text-sm mt-1">{{ $connectionError }}</p>
                            <div class="grid grid-cols-1 sm:flex gap-2 sm:gap-3 mt-3">
                                <a href="{{ route('nubes.index') }}" class="inline-flex items-center gap-1 text-white text-sm font-bold py-2 px-4 rounded shadow transition"
                                    style="background-color: #6b7280;"
                                    onmouseover="this.style.backgroundColor='#4b5563'"
                                    onmouseout="this.style.backgroundColor='#6b7280'">
                                    ← Volver a Fuentes
                                </a>
                                <a href="{{ route('nubes.browse', ['nube' => $nube, 'path' => $path]) }}" class="inline-flex items-center gap-1 text-white text-sm font-bold py-2 px-4 rounded shadow transition"
                                    style="background-color: #2563eb;"
                                    onmouseover="this.style.backgroundColor='#1d4ed8'"
                                    onmouseout="this.style.backgroundColor='#2563eb'">
                                    🔄 Reintentar conexión
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div id="browseRefreshStatus" class="mb-3 text-xs text-gray-500" aria-live="polite"></div>

        {{-- File List --}}
        <div id="fileList"
             class="bg-white rounded-lg shadow overflow-hidden"
             data-refresh-url="{{ route('nubes.browse-items', $nube) }}"
             data-path="{{ $path }}"
             data-can-upload="{{ $permissions['can_upload'] ? '1' : '0' }}"
             data-can-write="{{ $permissions['can_write'] ? '1' : '0' }}"
             data-can-delete="{{ $permissions['can_delete'] ? '1' : '0' }}">
            @if(count($items) > 0)
                <div class="md:hidden divide-y divide-gray-100">
                    @if($path)
                        <a href="{{ route('nubes.browse', ['nube' => $nube, 'path' => dirname($path) === '.' ? '' : dirname($path)]) }}" class="block px-4 py-3 text-blue-600 hover:bg-gray-50 font-medium">
                            ← ..
                        </a>
                    @endif

                    @foreach($items as $item)
                        @php
                            $itemPath = $path ? "{$path}/{$item['name']}" : $item['name'];
                            $isDirectory = $item['type'] === 'directory';
                        @endphp
                        <article class="p-4">
                            <div class="min-w-0">
                                @if($isDirectory)
                                    <a href="{{ route('nubes.browse', ['nube' => $nube, 'path' => $itemPath]) }}" class="flex items-start gap-2 text-blue-600 hover:underline font-medium">
                                        <span class="shrink-0">📁</span>
                                        <span class="min-w-0 break-all">{{ $item['name'] }}</span>
                                    </a>
                                @else
                                    <button type="button"
                                        onclick="downloadFolder('{{ addslashes($itemPath) }}', '{{ addslashes($item['name']) }}', 'file')"
                                        class="w-full flex items-start gap-2 text-left text-blue-600 hover:underline font-medium">
                                        <span class="shrink-0">📄</span>
                                        <span class="min-w-0 break-all">{{ $item['name'] }}</span>
                                    </button>
                                @endif
                            </div>

                            <dl class="mt-3 grid grid-cols-2 gap-x-3 gap-y-1 text-xs text-gray-500">
                                <div class="min-w-0">
                                    <dt class="font-semibold text-gray-400">Tamaño</dt>
                                    <dd class="truncate">
                                        @if($isDirectory)
                                            <button type="button"
                                                    class="folder-size-trigger text-blue-600 hover:underline"
                                                    data-path="{{ $itemPath }}"
                                                    data-nube="{{ $nube->id }}">
                                                Calcular
                                            </button>
                                        @else
                                            @php
                                                $size = $item['size'];
                                                $unit = 'B';
                                                if ($size >= 1073741824) { $size /= 1073741824; $unit = 'GB'; }
                                                elseif ($size >= 1048576) { $size /= 1048576; $unit = 'MB'; }
                                                elseif ($size >= 1024) { $size /= 1024; $unit = 'KB'; }
                                            @endphp
                                            {{ number_format($size, 2) }} {{ $unit }}
                                        @endif
                                    </dd>
                                </div>
                                <div class="min-w-0">
                                    <dt class="font-semibold text-gray-400">Permisos</dt>
                                    <dd class="font-mono truncate">{{ $item['permissions'] }}</dd>
                                </div>
                                <div class="col-span-2 min-w-0">
                                    <dt class="font-semibold text-gray-400">Modificado</dt>
                                    <dd class="truncate">{{ $item['modified'] }}</dd>
                                </div>
                            </dl>

                            <div class="mt-4 grid grid-cols-3 gap-2">
                                <button class="btn-download-folder text-white text-xs font-bold py-2 px-2 rounded transition"
                                    style="background-color: #2563eb;"
                                    onmouseover="this.style.backgroundColor='#1d4ed8'"
                                    onmouseout="this.style.backgroundColor='#2563eb'"
                                    title="{{ $isDirectory ? 'Descargar carpeta como ZIP' : 'Descargar archivo' }}"
                                    data-path="{{ $itemPath }}"
                                    data-name="{{ $item['name'] }}"
                                    data-type="{{ $isDirectory ? 'directory' : 'file' }}">
                                    Descargar
                                </button>
                                @if($permissions['can_write'])
                                    <button onclick="showRename('{{ addslashes($itemPath) }}', '{{ addslashes($item['name']) }}')" class="text-white text-xs font-bold py-2 px-2 rounded transition"
                                        style="background-color: #f59e0b;"
                                        onmouseover="this.style.backgroundColor='#d97706'"
                                        onmouseout="this.style.backgroundColor='#f59e0b'" title="Renombrar">
                                        Editar
                                    </button>
                                @endif
                                @if($permissions['can_delete'])
                                    <button onclick="confirmDelete('{{ addslashes($itemPath) }}', '{{ $item['type'] }}', '{{ addslashes($item['name']) }}')" class="text-white text-xs font-bold py-2 px-2 rounded transition"
                                        style="background-color: #dc2626;"
                                        onmouseover="this.style.backgroundColor='#b91c1c'"
                                        onmouseout="this.style.backgroundColor='#dc2626'" title="Eliminar">
                                        Borrar
                                    </button>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="hidden md:block overflow-x-auto">
                <table class="w-full table-fixed">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Nombre</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 w-28">Tamaño</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 w-40">Modificado</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 w-28">Permisos</th>
                            <th class="px-3 py-3 text-center text-sm font-semibold text-gray-600 w-36">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Back button --}}
                        @if($path)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-4 py-3" colspan="5">
                                    <a href="{{ route('nubes.browse', ['nube' => $nube, 'path' => dirname($path) === '.' ? '' : dirname($path)]) }}" class="text-blue-600 hover:underline font-medium">
                                        ← ..
                                    </a>
                                </td>
                            </tr>
                        @endif

                        @foreach($items as $item)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-4 py-3 min-w-0">
                                    @if($item['type'] === 'directory')
                                        <a href="{{ route('nubes.browse', ['nube' => $nube, 'path' => $path ? "{$path}/{$item['name']}" : $item['name']]) }}" class="min-w-0 text-blue-600 hover:underline font-medium flex items-center gap-2">
                                            <span class="shrink-0">📁</span>
                                            <span class="min-w-0 truncate">{{ $item['name'] }}</span>
                                        </a>
                                    @else
                                        <a href="javascript:void(0)"
                                            onclick="downloadFolder('{{ addslashes($path ? "{$path}/{$item['name']}" : $item['name']) }}', '{{ addslashes($item['name']) }}', 'file')"
                                            class="min-w-0 text-blue-600 hover:underline font-medium flex items-center gap-2">
                                            <span class="shrink-0">📄</span>
                                            <span class="min-w-0 truncate">{{ $item['name'] }}</span>
                                        </a>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500 truncate">
                                    @if($item['type'] === 'directory')
                                        <button type="button"
                                                class="folder-size-trigger text-blue-600 hover:underline"
                                                data-path="{{ $path ? "{$path}/{$item['name']}" : $item['name'] }}"
                                                data-nube="{{ $nube->id }}">
                                            Calcular
                                        </button>
                                    @else
                                        @php
                                            $size = $item['size'];
                                            $unit = 'B';
                                            if ($size >= 1073741824) { $size /= 1073741824; $unit = 'GB'; }
                                            elseif ($size >= 1048576) { $size /= 1048576; $unit = 'MB'; }
                                            elseif ($size >= 1024) { $size /= 1024; $unit = 'KB'; }
                                        @endphp
                                        {{ number_format($size, 2) }} {{ $unit }}
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500 truncate">{{ $item['modified'] }}</td>
                                <td class="px-4 py-3 text-sm font-mono text-gray-500 truncate">{{ $item['permissions'] }}</td>
                                <td class="px-3 py-3">
                                    <div class="flex justify-center gap-1 flex-nowrap">
                                        @if($item['type'] === 'directory')
                                            <button class="btn-download-folder text-white text-xs font-bold py-1 px-2 rounded transition"
                                                style="background-color: #2563eb;"
                                                onmouseover="this.style.backgroundColor='#1d4ed8'"
                                                onmouseout="this.style.backgroundColor='#2563eb'" title="Descargar carpeta como ZIP"
                                                data-path="{{ $path ? "{$path}/{$item['name']}" : $item['name'] }}"
                                                data-name="{{ $item['name'] }}"
                                                data-type="directory">
                                                ↓
                                            </button>
                                        @else
                                            <button class="btn-download-folder text-white text-xs font-bold py-1 px-2 rounded transition"
                                                style="background-color: #2563eb;"
                                                onmouseover="this.style.backgroundColor='#1d4ed8'"
                                                onmouseout="this.style.backgroundColor='#2563eb'" title="Descargar archivo"
                                                data-path="{{ $path ? "{$path}/{$item['name']}" : $item['name'] }}"
                                                data-name="{{ $item['name'] }}"
                                                data-type="file">
                                                ↓
                                            </button>
                                        @endif
                                        @if($permissions['can_write'])
                                            <button onclick="showRename('{{ addslashes($path ? "{$path}/{$item['name']}" : $item['name']) }}', '{{ addslashes($item['name']) }}')" class="text-white text-xs font-bold py-1 px-2 rounded transition"
                                                style="background-color: #f59e0b;"
                                                onmouseover="this.style.backgroundColor='#d97706'"
                                                onmouseout="this.style.backgroundColor='#f59e0b'" title="Renombrar">
                                                ✎
                                            </button>
                                        @endif
                                        @if($permissions['can_delete'])
                                            <button onclick="confirmDelete('{{ addslashes($path ? "{$path}/{$item['name']}" : $item['name']) }}', '{{ $item['type'] }}', '{{ addslashes($item['name']) }}')" class="text-white text-xs font-bold py-1 px-2 rounded transition"
                                                style="background-color: #dc2626;"
                                                onmouseover="this.style.backgroundColor='#b91c1c'"
                                                onmouseout="this.style.backgroundColor='#dc2626'" title="Eliminar">
                                                ✕
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
            @else
                <p class="text-gray-500 text-center py-12">
                    📂 Esta carpeta está vacía
                </p>
            @endif
        </div>
    </div>
</div>

{{-- Upload Modal --}}
<div id="uploadModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-3 sm:p-4">
    <div class="bg-white rounded-lg p-4 sm:p-6 max-w-md w-full max-h-[90vh] overflow-y-auto">
        <h3 class="text-lg font-bold mb-1">Subir archivo</h3>
        <p id="uploadStatus" class="text-sm text-gray-600 mb-4">Selecciona un archivo para enviarlo a esta carpeta.</p>
        <form id="uploadForm" action="{{ route('nubes.upload', $nube) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="path" value="{{ $path }}">
            <input id="uploadFileInput" type="file" name="file" class="w-full border rounded-lg px-4 py-2 text-sm mb-4" required>

            <div id="uploadProgressPanel" class="hidden mb-4">
                <div class="w-full bg-gray-200 rounded-full h-7 mb-2 overflow-hidden">
                    <div id="uploadProgressBar" class="h-7 w-full flex items-stretch">
                        <div id="uploadClientBar" class="h-7 transition-all duration-300 ease-out" style="width:0%; background:#2563eb;"></div>
                        <div id="uploadFtpBar" class="h-7 transition-all duration-300 ease-out" style="width:0%; background:#16a34a;"></div>
                    </div>
                </div>
                <div class="flex justify-between text-xs text-gray-500 mb-2">
                    <span id="uploadPercent">0%</span>
                    <span id="uploadSizes">0 B / 0 B</span>
                </div>
                <div id="uploadPhase" class="text-xs text-gray-700 mb-1">Fase: pendiente</div>
                <div id="uploadSpeed" class="text-xs text-gray-500">Velocidad: calculando...</div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                <button id="uploadSubmitBtn" type="submit" class="flex-1 text-white font-bold py-2 rounded transition"
                    style="background-color: #2563eb;">Subir</button>
                <button id="uploadCancelBtn" type="button" class="flex-1 bg-gray-300 font-bold py-2 rounded">Cancelar</button>
            </div>
        </form>
    </div>
</div>

{{-- Folder Modal --}}
<div id="folderModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-3 sm:p-4">
    <div class="bg-white rounded-lg p-4 sm:p-6 max-w-sm w-full max-h-[90vh] overflow-y-auto">
        <h3 class="text-lg font-bold mb-4">Nueva carpeta</h3>
        <form action="{{ route('nubes.folder.create', $nube) }}" method="POST">
            @csrf
            <input type="hidden" name="path" value="{{ $path }}">
            <input type="text" name="name" placeholder="Nombre de la carpeta" class="w-full border rounded-lg px-4 py-2 text-sm mb-4" required>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                <button type="submit" class="flex-1 text-white font-bold py-2 rounded transition"
                    style="background-color: #16a34a;">Crear</button>
                <button type="button" onclick="document.getElementById('folderModal').classList.add('hidden');document.getElementById('folderModal').classList.remove('flex')" class="flex-1 bg-gray-300 font-bold py-2 rounded">Cancelar</button>
            </div>
        </form>
    </div>
</div>

{{-- Rename Modal --}}
<div id="renameModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-3 sm:p-4">
    <div class="bg-white rounded-lg p-4 sm:p-6 max-w-sm w-full max-h-[90vh] overflow-y-auto">
        <h3 class="text-lg font-bold mb-4">Renombrar</h3>
        <form action="{{ route('nubes.rename', $nube) }}" method="POST" id="renameForm">
            @csrf
            <input type="hidden" name="path" id="renamePath">
            <input type="text" name="new_name" id="renameName" class="w-full border rounded-lg px-4 py-2 text-sm mb-4" required>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                <button type="submit" class="flex-1 text-white font-bold py-2 rounded transition"
                    style="background-color: #f59e0b;">Renombrar</button>
                <button type="button" onclick="document.getElementById('renameModal').classList.add('hidden');document.getElementById('renameModal').classList.remove('flex')" class="flex-1 bg-gray-300 font-bold py-2 rounded">Cancelar</button>
            </div>
        </form>
    </div>
</div>

{{-- Delete Confirm Modal --}}
<div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-3 sm:p-4">
    <div class="bg-white rounded-lg p-4 sm:p-6 max-w-sm w-full max-h-[90vh] overflow-y-auto">
        <h3 class="text-lg font-bold mb-4">Confirmar eliminación</h3>
        <p class="text-sm text-gray-600 mb-4">¿Estás seguro de eliminar <strong id="deleteName"></strong>?</p>
        <form action="{{ route('nubes.delete', $nube) }}" method="POST" id="deleteForm">
            @csrf
            <input type="hidden" name="path" id="deletePath">
            <input type="hidden" name="type" id="deleteType">
            <input type="hidden" name="name" id="deleteNameInput">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                <button type="submit" class="flex-1 text-white font-bold py-2 rounded transition"
                    style="background-color: #dc2626;">Eliminar</button>
                <button type="button" onclick="document.getElementById('deleteModal').classList.add('hidden');document.getElementById('deleteModal').classList.remove('flex')" class="flex-1 bg-gray-300 font-bold py-2 rounded">Cancelar</button>
            </div>
        </form>
    </div>
</div>

{{-- Download Progress Modal --}}
<div id="downloadModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:8px; padding:clamp(16px, 4vw, 24px); max-width:480px; width:calc(100% - 24px); max-height:90vh; overflow-y:auto; margin:0 auto;">
        <h3 style="font-size:18px; font-weight:700; margin:0 0 8px 0; color:#111;">Descargando</h3>
        <p id="downloadStatus" style="font-size:14px; color:#666; margin:0 0 16px 0;">Calculando tamaño total...</p>

        <div style="width:100%; background:#e5e7eb; border-radius:9999px; height:28px; margin-bottom:8px; overflow:hidden;">
            <div id="downloadProgressBar" style="height:28px; width:100%; display:flex; align-items:stretch;">
                <div id="phaseFetchingBar" style="height:28px; width:0%; background:#6b7280; transition:width 0.3s ease;"></div>
                <div id="phaseCompressingBar" style="height:28px; width:0%; background:#f59e0b; transition:width 0.3s ease;"></div>
                <div id="phaseDownloadingBar" style="height:28px; width:0%; background:#2563eb; transition:width 0.3s ease;"></div>
            </div>
        </div>

        <div style="display:flex; justify-content:space-between; font-size:12px; color:#888; margin-bottom:16px;">
            <span id="downloadPercent">0%</span>
            <span id="downloadSizes">0 B / 0 B</span>
        </div>

        <div id="downloadPhase" style="font-size:12px; color:#444; margin-bottom:8px;">Fase: pendiente</div>
        <div id="downloadSpeed" style="font-size:12px; color:#666; margin-bottom:16px;">Velocidad: calculando...</div>
        <div id="downloadDiagnostics" style="font-size:11px; color:#777; margin-bottom:16px; line-height:1.4;"></div>

        <style>
            @keyframes zipFinalizePulse {
                0%, 100% { opacity: 0.45; }
                50% { opacity: 1; }
            }
            @keyframes uploadFtpPulse {
                0%, 100% { opacity: 0.55; }
                50% { opacity: 1; }
            }
            .zip-finalizing {
                animation: zipFinalizePulse 1s ease-in-out infinite;
            }
            .upload-ftp-processing {
                animation: uploadFtpPulse 0.8s ease-in-out infinite;
            }
        </style>

        <div style="text-align:center;">
            <button type="button" id="downloadCancelBtn" style="font-size:14px; color:#fff; background:#dc2626; border:none; cursor:pointer; border-radius:6px; padding:8px 14px; font-weight:700; transition:background-color 0.2s ease;">
                Cancelar descarga
            </button>
        </div>
    </div>
</div>

<script>
function showRename(path, name) {
    document.getElementById('renamePath').value = path;
    document.getElementById('renameName').value = name;
    document.getElementById('renameModal').classList.remove('hidden');
    document.getElementById('renameModal').classList.add('flex');
}

function confirmDelete(path, type, name) {
    document.getElementById('deletePath').value = path;
    document.getElementById('deleteType').value = type;
    document.getElementById('deleteName').textContent = name;
    document.getElementById('deleteNameInput').value = name;
    document.getElementById('deleteModal').classList.remove('hidden');
    document.getElementById('deleteModal').classList.add('flex');
}

function formatSize(bytes) {
    if (bytes >= 1073741824) return (bytes / 1073741824).toFixed(2) + ' GB';
    if (bytes >= 1048576) return (bytes / 1048576).toFixed(2) + ' MB';
    if (bytes >= 1024) return (bytes / 1024).toFixed(2) + ' KB';
    return bytes + ' B';
}

let activeUploadXhr = null;

function setUploadSegmentProgress(clientPercent, ftpPercent) {
    const clientBar = document.getElementById('uploadClientBar');
    const ftpBar = document.getElementById('uploadFtpBar');
    const totalPercent = Math.min(100, Math.max(0, (clientPercent * 0.85) + (ftpPercent * 0.15)));

    if (clientBar) clientBar.style.width = Math.min(85, Math.max(0, clientPercent * 0.85)) + '%';
    if (ftpBar) ftpBar.style.width = Math.min(15, Math.max(0, ftpPercent * 0.15)) + '%';

    return totalPercent;
}

function resetUploadProgress() {
    const status = document.getElementById('uploadStatus');
    const panel = document.getElementById('uploadProgressPanel');
    const percent = document.getElementById('uploadPercent');
    const sizes = document.getElementById('uploadSizes');
    const phase = document.getElementById('uploadPhase');
    const speed = document.getElementById('uploadSpeed');
    const ftpBar = document.getElementById('uploadFtpBar');
    const submitBtn = document.getElementById('uploadSubmitBtn');

    if (status) {
        status.textContent = 'Selecciona un archivo para enviarlo a esta carpeta.';
        status.className = 'text-sm text-gray-600 mb-4';
    }
    if (panel) panel.classList.add('hidden');
    if (percent) percent.textContent = '0%';
    if (sizes) sizes.textContent = '0 B / 0 B';
    if (phase) phase.textContent = 'Fase: pendiente';
    if (speed) speed.textContent = 'Velocidad: calculando...';
    if (ftpBar) ftpBar.classList.remove('upload-ftp-processing');
    if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.style.opacity = '1';
        submitBtn.textContent = 'Subir';
    }
    setUploadSegmentProgress(0, 0);
}

function closeUploadModal() {
    const modal = document.getElementById('uploadModal');
    if (!modal) return;
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function handleUploadSubmit(event) {
    const form = event.target;
    const fileInput = document.getElementById('uploadFileInput');
    if (!form || !fileInput || !fileInput.files || fileInput.files.length === 0) {
        return;
    }

    event.preventDefault();

    const file = fileInput.files[0];
    const status = document.getElementById('uploadStatus');
    const panel = document.getElementById('uploadProgressPanel');
    const percent = document.getElementById('uploadPercent');
    const sizes = document.getElementById('uploadSizes');
    const phase = document.getElementById('uploadPhase');
    const speed = document.getElementById('uploadSpeed');
    const ftpBar = document.getElementById('uploadFtpBar');
    const submitBtn = document.getElementById('uploadSubmitBtn');
    const csrfInput = form.querySelector('input[name="_token"]');
    const startedAt = Date.now();

    resetUploadProgress();
    if (panel) panel.classList.remove('hidden');
    if (status) {
        status.textContent = 'Subiendo "' + file.name + '" al servidor...';
        status.className = 'text-sm text-gray-600 mb-4';
    }
    if (sizes) sizes.textContent = '0 B / ' + formatSize(file.size);
    if (phase) phase.textContent = 'Fase: enviando archivo a Laravel';
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.style.opacity = '0.7';
        submitBtn.textContent = 'Subiendo...';
    }

    const xhr = new XMLHttpRequest();
    activeUploadXhr = xhr;

    xhr.upload.addEventListener('progress', function(e) {
        if (!e.lengthComputable || e.total <= 0) {
            if (phase) phase.textContent = 'Fase: enviando archivo';
            return;
        }

        const clientPercent = Math.min(100, (e.loaded / e.total) * 100);
        const totalPercent = setUploadSegmentProgress(clientPercent, 0);
        const elapsed = Math.max(1, (Date.now() - startedAt) / 1000);

        if (percent) percent.textContent = totalPercent.toFixed(1) + '%';
        if (sizes) sizes.textContent = formatSize(e.loaded) + ' / ' + formatSize(e.total);
        if (phase) phase.textContent = 'Fase: enviando archivo a Laravel';
        if (speed) speed.textContent = 'Velocidad: ' + formatSize(e.loaded / elapsed) + '/s';
    });

    xhr.addEventListener('load', function() {
        activeUploadXhr = null;
        if (ftpBar) ftpBar.classList.remove('upload-ftp-processing');

        let data = {};
        try {
            data = JSON.parse(xhr.responseText || '{}');
        } catch (error) {
            data = {};
        }

        if (xhr.status >= 200 && xhr.status < 300 && data.success !== false) {
            setUploadSegmentProgress(100, 100);
            if (percent) percent.textContent = '100%';
            if (sizes) sizes.textContent = formatSize(file.size) + ' / ' + formatSize(file.size);
            if (phase) phase.textContent = 'Fase: completada';
            if (speed) speed.textContent = 'Velocidad: finalizada';
            if (status) {
                status.textContent = data.message || 'Archivo subido exitosamente.';
                status.className = 'text-sm text-green-700 mb-4';
            }

            setTimeout(function() {
                window.location.href = data.redirect || window.location.href;
            }, 700);
            return;
        }

        const message = data.message || 'No se pudo subir el archivo.';
        if (status) {
            status.textContent = 'Error: ' + message;
            status.className = 'text-sm text-red-700 mb-4';
        }
        if (phase) phase.textContent = 'Fase: error';
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.style.opacity = '1';
            submitBtn.textContent = 'Reintentar';
        }
    });

    xhr.addEventListener('loadend', function() {
        if (activeUploadXhr === xhr) {
            activeUploadXhr = null;
        }
    });

    xhr.addEventListener('error', function() {
        activeUploadXhr = null;
        if (ftpBar) ftpBar.classList.remove('upload-ftp-processing');
        if (status) {
            status.textContent = 'Error: no se pudo conectar con el servidor.';
            status.className = 'text-sm text-red-700 mb-4';
        }
        if (phase) phase.textContent = 'Fase: error de conexión';
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.style.opacity = '1';
            submitBtn.textContent = 'Reintentar';
        }
    });

    xhr.addEventListener('abort', function() {
        activeUploadXhr = null;
        if (ftpBar) ftpBar.classList.remove('upload-ftp-processing');
        if (status) {
            status.textContent = 'Subida cancelada.';
            status.className = 'text-sm text-red-700 mb-4';
        }
        if (phase) phase.textContent = 'Fase: cancelada';
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.style.opacity = '1';
            submitBtn.textContent = 'Subir';
        }
    });

    xhr.upload.addEventListener('load', function() {
        setUploadSegmentProgress(100, 0);
        if (percent) percent.textContent = '85%';
        if (phase) phase.textContent = 'Fase: guardando en servidor FTP';
        if (status) status.textContent = 'Archivo recibido. Guardando en el servidor FTP...';
        if (ftpBar) {
            ftpBar.style.width = '8%';
            ftpBar.classList.add('upload-ftp-processing');
        }
    });

    xhr.open('POST', form.action);
    xhr.setRequestHeader('Accept', 'application/json');
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    if (csrfInput) {
        xhr.setRequestHeader('X-CSRF-TOKEN', csrfInput.value);
    }
    xhr.send(new FormData(form));
}

function downloadFolder(path, name, type) {
    console.log('downloadFolder called', path, name, type);

    const isDirectoryDownload = type === 'directory';
    const modal = document.getElementById('downloadModal');
    const progressBar = document.getElementById('downloadProgressBar');
    const phaseFetchingBar = document.getElementById('phaseFetchingBar');
    const phaseCompressingBar = document.getElementById('phaseCompressingBar');
    const phaseDownloadingBar = document.getElementById('phaseDownloadingBar');
    const percentText = document.getElementById('downloadPercent');
    const sizesText = document.getElementById('downloadSizes');
    const statusText = document.getElementById('downloadStatus');
    const phaseText = document.getElementById('downloadPhase');
    const speedText = document.getElementById('downloadSpeed');
    const diagnosticsText = document.getElementById('downloadDiagnostics');
    const debugPanel = document.getElementById('downloadDebugPanel');
    const debugLog = document.getElementById('downloadDebugLog');
    const cancelBtn = document.getElementById('downloadCancelBtn');

    if (!modal) {
        alert('Error: no se encontró el modal');
        return;
    }

    modal.style.display = 'flex';
    progressBar.style.opacity = '1';
    phaseCompressingBar.classList.remove('zip-finalizing');
    setSegmentProgress(0, 0, 0);
    percentText.textContent = '0%';
    sizesText.textContent = '0 B';
    statusText.textContent = 'Preparando descarga...';
    statusText.style.color = '#666';
    phaseText.textContent = 'Fase: pendiente';
    speedText.textContent = 'Velocidad: calculando...';
    diagnosticsText.textContent = '';
    if (debugPanel && debugLog) {
        debugPanel.style.display = type === 'directory' ? 'block' : 'none';
        debugLog.innerHTML = '';
    }
    cancelBtn.style.display = 'inline-block';
    cancelBtn.disabled = false;
    cancelBtn.textContent = 'Cancelar descarga';
    cancelBtn.style.backgroundColor = '#dc2626';
    cancelBtn.style.cursor = 'pointer';

    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const downloadUrl = '/nubes/{{ $nube->id }}/download-sync';
    const cancelUrl = '/nubes/{{ $nube->id }}/download-cancel';
    const jobId = 'dl_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);

    const formData = new FormData();
    formData.append('path', path);
    formData.append('name', name);
    formData.append('type', type);
    formData.append('jobId', jobId);

    let pollInterval = null;
    let downloadComplete = false;
    let pollCompleteReceived = false;
    let startedAt = Date.now();
    let lastSpeedAt = startedAt;
    let lastSpeedBytes = 0;
    let clientDownloadStarted = false;
    let debugStartedAt = startedAt;
    let pollCount = 0;
    let lastLoggedPollAt = 0;
    let lastLoggedPollStatus = null;
    let lastLoggedXhrAt = 0;
    let lastLoggedXhrPercent = -1;
    let userCancelled = false;

    function logDownloadEvent(event, payload) {
        if (type !== 'directory' || !debugPanel || !debugLog) {
            return;
        }

        const elapsedMs = Date.now() - debugStartedAt;
        const row = document.createElement('div');
        row.style.borderTop = debugLog.children.length ? '1px solid #e5e7eb' : '0';
        row.style.padding = '3px 0';

        let details = '';
        if (payload) {
            try {
                details = ' ' + JSON.stringify(payload).slice(0, 420);
            } catch (e) {
                details = ' ' + String(payload).slice(0, 420);
            }
        }

        row.textContent = '[' + elapsedMs + ' ms] ' + event + details;
        debugLog.appendChild(row);

        while (debugLog.children.length > 140) {
            debugLog.removeChild(debugLog.firstChild);
        }

        debugPanel.scrollTop = debugPanel.scrollHeight;
    }

    logDownloadEvent('download:start', {
        jobId: jobId,
        path: path,
        name: name,
        type: type
    });

    function formatSpeed(bytesPerSecond) {
        if (!isFinite(bytesPerSecond) || bytesPerSecond <= 0) {
            return 'calculando...';
        }

        return formatSize(bytesPerSecond) + '/s';
    }

    function updateDownloadSpeed(downloadedBytes, force) {
        const now = Date.now();
        const elapsedMs = now - lastSpeedAt;

        if (!force && elapsedMs < 500) {
            return;
        }

        const bytesDelta = Math.max((downloadedBytes || 0) - lastSpeedBytes, 0);
        const currentSpeed = elapsedMs > 0 ? (bytesDelta * 1000) / elapsedMs : 0;
        const totalElapsedMs = Math.max(now - startedAt, 1);
        const averageSpeed = ((downloadedBytes || 0) * 1000) / totalElapsedMs;

        speedText.textContent = 'Velocidad: ' + formatSpeed(currentSpeed) + ' (promedio ' + formatSpeed(averageSpeed) + ')';
        lastSpeedAt = now;
        lastSpeedBytes = downloadedBytes || 0;
    }

    function phasePercent(phases, key) {
        return phases && phases[key] && typeof phases[key].percent !== 'undefined'
            ? Math.max(0, Math.min(100, parseFloat(phases[key].percent) || 0))
            : 0;
    }

    function totalPercent(fetchingPercent, compressingPercent, downloadingPercent) {
        return (fetchingPercent * 0.5) + (compressingPercent * 0.3) + (downloadingPercent * 0.2);
    }

    function setSegmentProgress(fetchingPercent, compressingPercent, downloadingPercent) {
        const safeFetching = Math.max(0, Math.min(100, parseFloat(fetchingPercent) || 0));
        const safeCompressing = Math.max(0, Math.min(100, parseFloat(compressingPercent) || 0));
        const safeDownloading = Math.max(0, Math.min(100, parseFloat(downloadingPercent) || 0));

        if (isDirectoryDownload) {
            phaseFetchingBar.style.width = (safeFetching * 0.5) + '%';
            phaseCompressingBar.style.width = (safeCompressing * 0.3) + '%';
            phaseDownloadingBar.style.width = (safeDownloading * 0.2) + '%';
            return;
        }

        phaseFetchingBar.style.width = '0%';
        phaseCompressingBar.style.width = '0%';
        phaseDownloadingBar.style.width = safeDownloading + '%';
    }

    function setPhaseText(status, phasePercentValue) {
        const labels = isDirectoryDownload
            ? {
                pending: 'pendiente',
                preparing: 'preparando carpeta',
                fetching: 'leyendo archivos de la carpeta',
                compressing: 'compresion ZIP en servidor',
                zip_finalize: 'finalizando ZIP',
                ready_for_download: 'ZIP listo',
                downloading: 'descarga del ZIP al cliente',
                completed: 'carpeta descargada',
                failed: 'fallo'
            }
            : {
                pending: 'pendiente',
                preparing: 'preparando archivo',
                fetching: 'leyendo archivo',
                downloading: 'descarga del archivo',
                completed: 'archivo descargado',
                failed: 'fallo'
            };

        phaseText.textContent = 'Fase: ' + (labels[status] || status) + ' (' + Math.round(phasePercentValue) + '% de fase)';
    }

    function applyProgressState(data) {
        const fetching = phasePercent(data.phases, 'fetching');
        const compressing = phasePercent(data.phases, 'compressing');
        const downloading = phasePercent(data.phases, 'downloading');
        const total = typeof data.percent !== 'undefined'
            ? Math.max(0, Math.min(100, parseFloat(data.percent) || 0))
            : totalPercent(fetching, compressing, downloading);

        setSegmentProgress(fetching, compressing, downloading);
        percentText.textContent = total.toFixed(1) + '%';

        if (data.status === 'fetching') {
            setPhaseText(data.status, fetching);
        } else if (data.status === 'compressing') {
            setPhaseText(data.status, compressing);
        } else if (data.status === 'zip_finalize') {
            setPhaseText(data.status, 100);
        } else if (data.status === 'downloading') {
            setPhaseText(data.status, downloading);
        } else if (data.status === 'ready_for_download') {
            setPhaseText(data.status, 100);
        } else {
            setPhaseText(data.status || 'pending', total);
        }

        updateDiagnostics(data.metrics);
    }

    function updateDiagnostics(metrics) {
        if (!metrics) {
            diagnosticsText.textContent = '';
            return;
        }

        const parts = [];
        if (typeof metrics.files_total !== 'undefined') {
            parts.push('archivos ' + (metrics.files_processed || 0) + '/' + metrics.files_total);
        }
        if (typeof metrics.bytes_read !== 'undefined' && metrics.estimated_bytes_total) {
            parts.push('leidos ' + formatSize(metrics.bytes_read) + '/' + formatSize(metrics.estimated_bytes_total));
        }
        if (typeof metrics.compressed_files !== 'undefined' && metrics.files_total) {
            parts.push('comprimidos ' + metrics.compressed_files + '/' + metrics.files_total);
        }
        if (typeof metrics.updates_total !== 'undefined') {
            parts.push('updates ' + metrics.updates_total);
        }
        if (typeof metrics.last_update_delta_ms !== 'undefined' && metrics.last_update_delta_ms !== null) {
            parts.push('ultimo update ' + metrics.last_update_delta_ms + ' ms');
        }
        if (metrics.phase_durations_ms) {
            const durations = Object.keys(metrics.phase_durations_ms).map(function(key) {
                return key + ' ' + metrics.phase_durations_ms[key] + ' ms';
            });
            if (durations.length) {
                parts.push(durations.join(', '));
            }
        }
        if (typeof metrics.downloading_updates !== 'undefined') {
            parts.push('envio ZIP updates ' + metrics.downloading_updates);
        }

        diagnosticsText.textContent = parts.join(' | ');
    }

    function startPolling() {
        const pollEveryMs = 500;
        logDownloadEvent('poll:start', { interval_ms: pollEveryMs });

        pollInterval = setInterval(function() {
            if (downloadComplete) {
                stopPolling();
                return;
            }

            const pollUrl = '/download-progress?jobId=' + encodeURIComponent(jobId);
            const pollStartedAt = Date.now();
            let pollHttpStatus = null;
            pollCount++;
            
            fetch(pollUrl, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin',
                redirect: 'follow'
            })
                .then(function(r) {
                    pollHttpStatus = r.status;
                    const contentType = r.headers.get('content-type') || '';

                    if (!r.ok) {
                        return r.text().then(function(body) {
                            throw new Error('Polling HTTP ' + r.status + ' [' + contentType + ']: ' + body.slice(0, 200));
                        });
                    }

                    if (contentType.indexOf('application/json') === -1) {
                        return r.text().then(function(body) {
                            throw new Error('Polling devolvio contenido no JSON [' + contentType + ']: ' + body.slice(0, 200));
                        });
                    }

                    return r.json();
                })
                .then(function(data) {
                    if (!data) return;
                    
                    applyProgressState(data);

                    const now = Date.now();
                    const shouldLogPoll = data.status !== lastLoggedPollStatus
                        || now - lastLoggedPollAt >= 1000
                        || data.status === 'failed'
                        || data.status === 'completed';

                    if (shouldLogPoll) {
                        logDownloadEvent('poll:state', {
                            poll: pollCount,
                            http: pollHttpStatus,
                            latency_ms: now - pollStartedAt,
                            status: data.status,
                            percent: data.percent,
                            downloaded: data.downloaded,
                            total: data.total,
                            phases: data.phases,
                            metrics: data.metrics ? {
                                files_total: data.metrics.files_total,
                                files_processed: data.metrics.files_processed,
                                compressed_files: data.metrics.compressed_files,
                                estimated_bytes_total: data.metrics.estimated_bytes_total,
                                bytes_read: data.metrics.bytes_read,
                                updates_total: data.metrics.updates_total,
                                last_update_delta_ms: data.metrics.last_update_delta_ms,
                                phase_durations_ms: data.metrics.phase_durations_ms
                            } : null
                        });
                        lastLoggedPollAt = now;
                        lastLoggedPollStatus = data.status;
                    }

                    if (data.status === 'completed') {
                        pollCompleteReceived = true;
                        percentText.textContent = '100%';
                        if (data.total > 0) {
                            sizesText.textContent = formatSize(data.total);
                        }
                        if (typeof data.downloaded !== 'undefined') {
                            updateDownloadSpeed(data.downloaded, true);
                        }
                        statusText.textContent = data.message || 'Descarga completada';
                        statusText.style.color = '#16a34a';
                    } else if (data.status === 'failed') {
                        statusText.textContent = 'Error: ' + (data.message || 'La descarga fallo');
                        statusText.style.color = '#dc2626';
                        progressBar.style.opacity = '0.65';
                    } else if (['pending', 'preparing', 'fetching', 'compressing', 'zip_finalize', 'ready_for_download', 'downloading'].includes(data.status)) {
                        if (data.status === 'compressing') {
                            if (data.total > 0) {
                                sizesText.textContent = (data.downloaded || 0) + ' / ' + data.total + ' archivos';
                            } else {
                                sizesText.textContent = 'Comprimiendo archivos...';
                            }
                            speedText.textContent = 'Velocidad: pausada mientras se comprime';
                            phaseCompressingBar.classList.remove('zip-finalizing');
                        } else if (data.status === 'zip_finalize') {
                            sizesText.textContent = 'Finalizando estructura del ZIP...';
                            speedText.textContent = 'Velocidad: no medible durante finalizacion del ZIP';
                            phaseCompressingBar.classList.add('zip-finalizing');
                        } else if (data.status === 'ready_for_download') {
                            sizesText.textContent = data.total > 0 ? formatSize(data.total) : 'ZIP listo';
                            speedText.textContent = 'Velocidad: esperando descarga del ZIP';
                            phaseCompressingBar.classList.remove('zip-finalizing');
                        } else if (data.total > 0) {
                            sizesText.textContent = formatSize(data.downloaded) + ' / ' + formatSize(data.total);
                            updateDownloadSpeed(data.downloaded || 0, false);
                            phaseCompressingBar.classList.remove('zip-finalizing');
                        } else {
                            sizesText.textContent = formatSize(data.downloaded || 0);
                            updateDownloadSpeed(data.downloaded || 0, false);
                            phaseCompressingBar.classList.remove('zip-finalizing');
                        }

                        statusText.textContent = data.message || 'Descargando...';
                    }
                })
                .catch(function(error) {
                    logDownloadEvent('poll:error', {
                        poll: pollCount,
                        message: error.message
                    });
                    console.error('Download progress polling failed', {
                        jobId: jobId,
                        pollUrl: pollUrl,
                        error: error
                    });
                    statusText.textContent = 'Debug polling: ' + error.message;
                    statusText.style.color = '#dc2626';
                });
        }, pollEveryMs);
    }

    function stopPolling() {
        if (pollInterval) {
            clearInterval(pollInterval);
            pollInterval = null;
            logDownloadEvent('poll:stop', { poll_count: pollCount });
        }
    }

    const xhr = new XMLHttpRequest();

    cancelBtn.onclick = function() {
        if (downloadComplete || userCancelled) {
            return;
        }

        userCancelled = true;
        cancelBtn.disabled = true;
        cancelBtn.textContent = 'Cancelando...';
        cancelBtn.style.backgroundColor = '#991b1b';
        cancelBtn.style.cursor = 'not-allowed';
        stopPolling();
        xhr.abort();

        fetch(cancelUrl, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin',
            body: formData
        }).catch(function(error) {
            logDownloadEvent('cancel:request-error', { message: error.message });
        });

        logDownloadEvent('download:cancelled-by-user');
        statusText.textContent = 'Descarga cancelada';
        statusText.style.color = '#dc2626';
        speedText.textContent = 'Velocidad: cancelada';
        progressBar.style.opacity = '0.65';

        setTimeout(function() {
            modal.style.display = 'none';
        }, 1000);
    };

    if (isDirectoryDownload) {
        startPolling();
    }

    xhr.addEventListener('loadstart', function() {
        logDownloadEvent('xhr:loadstart');
    });

    xhr.addEventListener('load', function() {
        if (userCancelled) {
            return;
        }

        downloadComplete = true;
        stopPolling();
        cancelBtn.disabled = true;
        cancelBtn.style.display = 'none';
        logDownloadEvent('xhr:load', {
            status: xhr.status,
            response_size: xhr.response && xhr.response.size ? xhr.response.size : null,
            content_type: xhr.getResponseHeader('Content-Type'),
            content_length: xhr.getResponseHeader('Content-Length')
        });
        if (xhr.status >= 200 && xhr.status < 300) {
            if (isDirectoryDownload) {
                setSegmentProgress(100, 100, 100);
            } else {
                setSegmentProgress(0, 0, 100);
            }
            percentText.textContent = '100%';
            setPhaseText('completed', 100);
            statusText.textContent = isDirectoryDownload ? 'Guardando ZIP...' : 'Guardando archivo...';
            updateDownloadSpeed(xhr.response && xhr.response.size ? xhr.response.size : lastSpeedBytes, true);

            const disposition = xhr.getResponseHeader('Content-Disposition');
            let downloadFileName = name;
            if (isDirectoryDownload) downloadFileName += '.zip';

            if (disposition) {
                const match = disposition.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/);
                if (match && match[1]) {
                    downloadFileName = match[1].replace(/['"]/g, '');
                }
            }

            const blob = xhr.response;
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = downloadFileName;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            a.remove();

            percentText.textContent = '100%';
            statusText.textContent = 'Descarga completada';
            statusText.style.color = '#16a34a';
            speedText.textContent = speedText.textContent.replace('Velocidad:', 'Velocidad final:');

            setTimeout(function() {
                modal.style.display = 'none';
            }, 1500);
        } else {
            logDownloadEvent('xhr:http-error', { status: xhr.status });
            let errorMsg = 'Error en la descarga (status: ' + xhr.status + ')';
            if (xhr.response && xhr.response.size > 0) {
                const reader = new FileReader();
                reader.onload = function() {
                    try {
                        const err = JSON.parse(reader.result);
                        errorMsg = err.error || errorMsg;
                    } catch (e) {}
                    statusText.textContent = 'Error: ' + errorMsg;
                    statusText.style.color = '#dc2626';
                    progressBar.style.opacity = '0.65';
                };
                reader.readAsText(xhr.response);
            } else {
                statusText.textContent = 'Error: ' + errorMsg;
                statusText.style.color = '#dc2626';
                progressBar.style.opacity = '0.65';
            }
        }
    });

    xhr.addEventListener('error', function() {
        if (userCancelled) {
            return;
        }

        stopPolling();
        cancelBtn.disabled = true;
        logDownloadEvent('xhr:error');
        statusText.textContent = 'Error: No se pudo conectar al servidor';
        statusText.style.color = '#dc2626';
        progressBar.style.opacity = '0.65';
    });

    xhr.addEventListener('abort', function() {
        if (!userCancelled) {
            stopPolling();
            statusText.textContent = 'Descarga interrumpida';
            statusText.style.color = '#dc2626';
            progressBar.style.opacity = '0.65';
        }
    });

    xhr.addEventListener('progress', function(e) {
        if (e.lengthComputable && e.loaded > 0 && e.total > 0) {
            const clientPercent = Math.min(100, (e.loaded / e.total) * 100);
            if (isDirectoryDownload) {
                const now = Date.now();
                if (now - lastLoggedXhrAt >= 1000 || Math.abs(clientPercent - lastLoggedXhrPercent) >= 5) {
                    logDownloadEvent('xhr:progress', {
                        loaded: e.loaded,
                        total: e.total,
                        percent: Number(clientPercent.toFixed(2))
                    });
                    lastLoggedXhrAt = now;
                    lastLoggedXhrPercent = clientPercent;
                }

                if (!clientDownloadStarted) {
                    clientDownloadStarted = true;
                    startedAt = Date.now();
                    lastSpeedAt = startedAt;
                    lastSpeedBytes = 0;
                }
                setSegmentProgress(100, 100, clientPercent);
                percentText.textContent = totalPercent(100, 100, clientPercent).toFixed(1) + '%';
                setPhaseText('downloading', clientPercent);
            } else {
                setSegmentProgress(0, 0, clientPercent);
                percentText.textContent = Math.round(clientPercent) + '%';
                setPhaseText('downloading', clientPercent);
                stopPolling();
            }
            sizesText.textContent = formatSize(e.loaded) + ' / ' + formatSize(e.total);
            statusText.textContent = isDirectoryDownload ? 'Descargando ZIP al cliente...' : 'Recibiendo archivo...';
            updateDownloadSpeed(e.loaded, false);
        }
    });

    xhr.open('POST', downloadUrl);
    xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
    xhr.responseType = 'blob';
    xhr.send(formData);
}

const browseState = {
    nubeId: {{ $nube->id }},
    refreshUrl: @json(route('nubes.browse-items', $nube)),
    currentPath: @json($path),
    refreshMs: 60000,
    minRefreshGapMs: 60000,
    lastRefreshAt: Date.now(),
    refreshTimer: null,
    refreshing: false,
    folderSizeCache: new Map(),
};

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function joinRemotePath(base, name) {
    return base ? base.replace(/\/+$/, '') + '/' + name : name;
}

function parentRemotePath(path) {
    const clean = String(path || '').replace(/^\/+|\/+$/g, '');
    if (!clean || clean.indexOf('/') === -1) return '';
    return clean.split('/').slice(0, -1).join('/');
}

function formatItemSize(item) {
    if (item.type === 'directory') {
        return '<button type="button" class="folder-size-trigger text-blue-600 hover:underline" data-path="' + escapeHtml(item.path) + '" data-nube="' + browseState.nubeId + '">Calcular</button>';
    }

    return escapeHtml(formatSize(Number(item.size || 0)));
}

function getCurrentPermissions() {
    const fileList = document.getElementById('fileList');
    return {
        can_upload: fileList ? fileList.dataset.canUpload === '1' : false,
        can_write: fileList ? fileList.dataset.canWrite === '1' : false,
        can_delete: fileList ? fileList.dataset.canDelete === '1' : false
    };
}

function itemActionsHtml(item, permissions, mobile) {
    const buttons = [];
    const sizeClass = mobile ? 'py-2 px-2' : 'py-1 px-2';
    const labelDownload = mobile ? 'Descargar' : '↓';
    const labelEdit = mobile ? 'Editar' : '✎';
    const labelDelete = mobile ? 'Borrar' : '✕';

    buttons.push('<button type="button" class="btn-download-folder text-white text-xs font-bold ' + sizeClass + ' rounded transition" style="background-color:#2563eb;" data-path="' + escapeHtml(item.path) + '" data-name="' + escapeHtml(item.name) + '" data-type="' + escapeHtml(item.type) + '" title="' + (item.type === 'directory' ? 'Descargar carpeta como ZIP' : 'Descargar archivo') + '">' + labelDownload + '</button>');

    if (permissions.can_write) {
        buttons.push('<button type="button" class="ftp-action-rename text-white text-xs font-bold ' + sizeClass + ' rounded transition" style="background-color:#f59e0b;" data-path="' + escapeHtml(item.path) + '" data-name="' + escapeHtml(item.name) + '" title="Renombrar">' + labelEdit + '</button>');
    }

    if (permissions.can_delete) {
        buttons.push('<button type="button" class="ftp-action-delete text-white text-xs font-bold ' + sizeClass + ' rounded transition" style="background-color:#dc2626;" data-path="' + escapeHtml(item.path) + '" data-name="' + escapeHtml(item.name) + '" data-type="' + escapeHtml(item.type) + '" title="Eliminar">' + labelDelete + '</button>');
    }

    const mobileGrid = buttons.length === 1 ? 'grid grid-cols-1 gap-2' : (buttons.length === 2 ? 'grid grid-cols-2 gap-2' : 'grid grid-cols-3 gap-2');
    const gridClass = mobile ? mobileGrid : 'flex justify-center gap-1 flex-nowrap';
    return '<div class="' + gridClass + '">' + buttons.join('') + '</div>';
}

function renderMobileList(items, permissions) {
    let html = '<div class="md:hidden divide-y divide-gray-100">';

    if (browseState.currentPath) {
        const backUrl = @json(route('nubes.browse', $nube)) + '?path=' + encodeURIComponent(parentRemotePath(browseState.currentPath));
        html += '<a href="' + backUrl + '" class="block px-4 py-3 text-blue-600 hover:bg-gray-50 font-medium">← ..</a>';
    }

    items.forEach(function(item) {
        const name = escapeHtml(item.name);
        const icon = item.type === 'directory' ? '📁' : '📄';
        const itemLink = item.type === 'directory'
            ? '<a href="' + @json(route('nubes.browse', $nube)) + '?path=' + encodeURIComponent(item.path) + '" class="flex items-start gap-2 text-blue-600 hover:underline font-medium"><span class="shrink-0">' + icon + '</span><span class="min-w-0 break-all">' + name + '</span></a>'
            : '<button type="button" class="btn-download-folder w-full flex items-start gap-2 text-left text-blue-600 hover:underline font-medium" data-path="' + escapeHtml(item.path) + '" data-name="' + name + '" data-type="file"><span class="shrink-0">' + icon + '</span><span class="min-w-0 break-all">' + name + '</span></button>';

        html += '<article class="p-4">'
            + '<div class="min-w-0">' + itemLink + '</div>'
            + '<dl class="mt-3 grid grid-cols-2 gap-x-3 gap-y-1 text-xs text-gray-500">'
            + '<div class="min-w-0"><dt class="font-semibold text-gray-400">Tamaño</dt><dd class="truncate">' + formatItemSize(item) + '</dd></div>'
            + '<div class="min-w-0"><dt class="font-semibold text-gray-400">Permisos</dt><dd class="font-mono truncate">' + escapeHtml(item.permissions || '') + '</dd></div>'
            + '<div class="col-span-2 min-w-0"><dt class="font-semibold text-gray-400">Modificado</dt><dd class="truncate">' + escapeHtml(item.modified || '') + '</dd></div>'
            + '</dl>'
            + '<div class="mt-4">' + itemActionsHtml(item, permissions, true) + '</div>'
            + '</article>';
    });

    return html + '</div>';
}

function renderDesktopTable(items, permissions) {
    let html = '<div class="hidden md:block overflow-x-auto"><table class="w-full table-fixed"><thead><tr class="bg-gray-100">'
        + '<th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Nombre</th>'
        + '<th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 w-28">Tamaño</th>'
        + '<th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 w-40">Modificado</th>'
        + '<th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 w-28">Permisos</th>'
        + '<th class="px-3 py-3 text-center text-sm font-semibold text-gray-600 w-36">Acciones</th>'
        + '</tr></thead><tbody>';

    if (browseState.currentPath) {
        const backUrl = @json(route('nubes.browse', $nube)) + '?path=' + encodeURIComponent(parentRemotePath(browseState.currentPath));
        html += '<tr class="border-b hover:bg-gray-50"><td class="px-4 py-3" colspan="5"><a href="' + backUrl + '" class="text-blue-600 hover:underline font-medium">← ..</a></td></tr>';
    }

    items.forEach(function(item) {
        const name = escapeHtml(item.name);
        const icon = item.type === 'directory' ? '📁' : '📄';
        const nameCell = item.type === 'directory'
            ? '<a href="' + @json(route('nubes.browse', $nube)) + '?path=' + encodeURIComponent(item.path) + '" class="min-w-0 text-blue-600 hover:underline font-medium flex items-center gap-2"><span class="shrink-0">' + icon + '</span><span class="min-w-0 truncate">' + name + '</span></a>'
            : '<button type="button" class="btn-download-folder min-w-0 text-blue-600 hover:underline font-medium flex items-center gap-2" data-path="' + escapeHtml(item.path) + '" data-name="' + name + '" data-type="file"><span class="shrink-0">' + icon + '</span><span class="min-w-0 truncate">' + name + '</span></button>';

        html += '<tr class="border-b hover:bg-gray-50">'
            + '<td class="px-4 py-3 min-w-0">' + nameCell + '</td>'
            + '<td class="px-4 py-3 text-sm text-gray-500 truncate">' + formatItemSize(item) + '</td>'
            + '<td class="px-4 py-3 text-sm text-gray-500 truncate">' + escapeHtml(item.modified || '') + '</td>'
            + '<td class="px-4 py-3 text-sm font-mono text-gray-500 truncate">' + escapeHtml(item.permissions || '') + '</td>'
            + '<td class="px-3 py-3">' + itemActionsHtml(item, permissions, false) + '</td>'
            + '</tr>';
    });

    return html + '</tbody></table></div>';
}

function normalizeBrowseItems(items) {
    return (items || []).map(function(item) {
        const path = joinRemotePath(browseState.currentPath, item.name);
        return Object.assign({}, item, { path: path });
    });
}

function renderFileList(items, permissions) {
    const fileList = document.getElementById('fileList');
    if (!fileList) return;

    const effectivePermissions = permissions || getCurrentPermissions();
    const normalizedItems = normalizeBrowseItems(items);

    if (normalizedItems.length === 0) {
        fileList.innerHTML = '<p class="text-gray-500 text-center py-12">📂 Esta carpeta está vacía</p>';
    } else {
        fileList.innerHTML = renderMobileList(normalizedItems, effectivePermissions) + renderDesktopTable(normalizedItems, effectivePermissions);
    }

    if (permissions) {
        fileList.dataset.canUpload = permissions.can_upload ? '1' : '0';
        fileList.dataset.canWrite = permissions.can_write ? '1' : '0';
        fileList.dataset.canDelete = permissions.can_delete ? '1' : '0';
    }
}

function setRefreshStatus(message, isError) {
    const status = document.getElementById('browseRefreshStatus');
    if (!status) return;
    status.textContent = message || '';
    status.className = 'mb-3 text-xs ' + (isError ? 'text-red-600' : 'text-gray-500');
}

function refreshBrowseItems(force) {
    if (browseState.refreshing || document.hidden) return;
    const now = Date.now();
    if (!force && now - browseState.lastRefreshAt < browseState.minRefreshGapMs) return;

    browseState.refreshing = true;

    const url = browseState.refreshUrl + '?path=' + encodeURIComponent(browseState.currentPath || '');

    fetch(url, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin'
    })
        .then(function(response) {
            return response.json().then(function(data) {
                if (!response.ok || data.success === false) {
                    throw new Error(data.message || 'No se pudo actualizar el listado FTP.');
                }
                return data;
            });
        })
        .then(function(data) {
            browseState.lastRefreshAt = Date.now();
            renderFileList(data.items || [], data.permissions || null);
            const refreshed = data.refreshed_at ? new Date(data.refreshed_at) : new Date();
            setRefreshStatus('Listado actualizado automáticamente a las ' + refreshed.toLocaleTimeString(), false);
        })
        .catch(function(error) {
            setRefreshStatus(error.message, true);
            if (error.message.indexOf('carpeta') !== -1 || error.message.indexOf('no existe') !== -1) {
                const fileList = document.getElementById('fileList');
                if (fileList) {
                    fileList.innerHTML = '<div class="p-6 text-center"><p class="font-bold text-red-700">Esta carpeta ya no está disponible.</p><p class="text-sm text-gray-600 mt-2">El contenido pudo cambiar en el servidor FTP. Vuelve a la carpeta anterior o refresca desde la fuente.</p><a href="' + @json(route('nubes.browse', $nube)) + '?path=' + encodeURIComponent(parentRemotePath(browseState.currentPath)) + '" class="inline-block mt-4 text-white font-bold py-2 px-4 rounded shadow" style="background-color:#2563eb;">Volver a la carpeta anterior</a></div>';
                }
            }
        })
        .finally(function() {
            browseState.refreshing = false;
        });
}

function startBrowseRefresh() {
    if (browseState.refreshTimer) return;
    browseState.refreshTimer = setInterval(refreshBrowseItems, browseState.refreshMs);
}

function stopBrowseRefresh() {
    if (browseState.refreshTimer) {
        clearInterval(browseState.refreshTimer);
        browseState.refreshTimer = null;
    }
}

function loadFolderSize(el) {
    if (!el || el.dataset.loading || el.dataset.loaded) return;
    const path = el.dataset.path || '';

    if (browseState.folderSizeCache.has(path)) {
        const cached = browseState.folderSizeCache.get(path);
        el.textContent = cached;
        el.title = 'Tamaño: ' + cached;
        el.dataset.loaded = '1';
        return;
    }

    el.dataset.loading = '1';
    el.textContent = 'Calculando...';

    fetch('/nubes/' + browseState.nubeId + '/folder-size?path=' + encodeURIComponent(path), {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin'
    })
        .then(function(response) {
            return response.json().then(function(data) {
                if (!response.ok || data.error) {
                    throw new Error(data.error || 'No se pudo calcular');
                }
                return data;
            });
        })
        .then(function(data) {
            const formatted = formatSize(Number(data.size || 0));
            browseState.folderSizeCache.set(path, formatted);
            el.textContent = formatted;
            el.title = 'Tamaño: ' + formatted;
            el.dataset.loaded = '1';
        })
        .catch(function() {
            el.textContent = 'No disponible';
        })
        .finally(function() {
            delete el.dataset.loading;
        });
}

document.addEventListener('click', function(event) {
    const folderSizeBtn = event.target.closest('.folder-size-trigger');
    if (folderSizeBtn) {
        event.preventDefault();
        loadFolderSize(folderSizeBtn);
        return;
    }

    const downloadBtn = event.target.closest('.btn-download-folder');
    if (downloadBtn) {
        event.preventDefault();
        downloadFolder(downloadBtn.dataset.path, downloadBtn.dataset.name, downloadBtn.dataset.type);
        return;
    }

    const renameBtn = event.target.closest('.ftp-action-rename');
    if (renameBtn) {
        event.preventDefault();
        showRename(renameBtn.dataset.path, renameBtn.dataset.name);
        return;
    }

    const deleteBtn = event.target.closest('.ftp-action-delete');
    if (deleteBtn) {
        event.preventDefault();
        confirmDelete(deleteBtn.dataset.path, deleteBtn.dataset.type, deleteBtn.dataset.name);
    }
});

let connectionCheckInterval = null;

function checkConnection() {
    const nubeId = {{ $nube->id }};

    fetch('/nubes/' + nubeId + '/status')
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (!data.online) {
                showConnectionLostError(data.error || 'Conexión perdida');
                stopConnectionCheck();
                stopBrowseRefresh();
            }
        })
        .catch(function() {
            showConnectionLostError('No se pudo verificar la conexión');
            stopConnectionCheck();
            stopBrowseRefresh();
        });
}

function showConnectionLostError(errorMessage) {
    if (document.getElementById('connection-lost-overlay')) return;

    const errorDiv = document.createElement('div');
    errorDiv.id = 'connection-lost-overlay';
    errorDiv.className = 'fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50 p-3';
    errorDiv.innerHTML = `
        <div class="bg-white rounded-lg shadow-xl p-4 sm:p-6 max-w-md mx-4">
            <div class="flex items-start gap-3">
                <span class="text-4xl">⚠️</span>
                <div class="flex-1">
                    <h3 class="font-bold text-gray-800 text-lg">Conexión perdida</h3>
                    <p class="text-gray-600 text-sm mt-2">Se perdió la conexión con el servidor FTP.</p>
                    <p class="text-red-500 text-sm mt-1 font-medium">${escapeHtml(errorMessage)}</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-4">
                        <a href="{{ route('nubes.index') }}" class="text-center text-white text-sm font-bold py-2 px-4 rounded shadow transition" style="background-color:#6b7280;">← Volver a Fuentes</a>
                        <button onclick="location.reload()" class="text-white text-sm font-bold py-2 px-4 rounded shadow transition" style="background-color:#2563eb;">🔄 Reintentar</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    document.body.appendChild(errorDiv);

    const fileList = document.getElementById('fileList');
    if (fileList) fileList.style.opacity = '0.3';
}

function startConnectionCheck() {
    if (connectionCheckInterval) return;
    connectionCheckInterval = setInterval(checkConnection, 30000);
}

function stopConnectionCheck() {
    if (connectionCheckInterval) {
        clearInterval(connectionCheckInterval);
        connectionCheckInterval = null;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const uploadForm = document.getElementById('uploadForm');
    const uploadCancelBtn = document.getElementById('uploadCancelBtn');
    const uploadActionBtn = document.getElementById('uploadActionBtn');

    if (uploadForm) {
        uploadForm.addEventListener('submit', handleUploadSubmit);
    }

    if (uploadCancelBtn) {
        uploadCancelBtn.addEventListener('click', function() {
            if (activeUploadXhr) {
                activeUploadXhr.abort();
                return;
            }

            closeUploadModal();
            resetUploadProgress();
        });
    }

    if (uploadActionBtn) {
        uploadActionBtn.addEventListener('click', resetUploadProgress);
    }

    startBrowseRefresh();
});

document.addEventListener('visibilitychange', function() {
    if (document.hidden) {
        stopConnectionCheck();
        stopBrowseRefresh();
    } else {
        refreshBrowseItems();
        startBrowseRefresh();
    }
});
</script>
@endsection
