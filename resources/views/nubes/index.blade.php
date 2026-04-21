@extends('layouts.app')

@section('title', 'Fuentes de Datos')

@section('content')
<div class="bg-gray-100 py-6 sm:py-12">
    <div class="max-w-6xl mx-auto">
        <div class="flex flex-col gap-4 sm:flex-row sm:justify-between sm:items-center mb-6">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">Fuentes de Datos</h1>
            <div class="grid grid-cols-1 sm:flex sm:items-center gap-2 sm:gap-3">
                @can('create', App\Models\Nube::class)
                    <a href="{{ route('nubes.create') }}" class="inline-block text-center text-white font-bold py-2 px-4 rounded-lg shadow transition"
                        style="background-color: #2563eb;"
                        onmouseover="this.style.backgroundColor='#1d4ed8'"
                        onmouseout="this.style.backgroundColor='#2563eb'">
                        + Nueva Fuente
                    </a>
                @endcan
                <button id="refresh-status" class="inline-block text-center text-white font-bold py-2 px-4 rounded-lg shadow transition"
                    style="background-color: #059669;"
                    onmouseover="this.style.backgroundColor='#047857'"
                    onmouseout="this.style.backgroundColor='#059669'">
                    🔄 Actualizar estados
                </button>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-green-100 text-green-700 text-sm px-4 py-3 rounded-lg mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if($nubesWithStatus->isNotEmpty())
              <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
                  @foreach($nubesWithStatus as $nube)
                      @php
                          $isOnline = $nube->is_online;
                          $isUnknown = $isOnline === null;
                          $borderColor = $nube->activo ? '#16a34a' : '#dc2626';
                          $connectionTypeColor = $nube->tipo_conexion === 'ftps' ? '#f59e0b' : ($nube->tipo_conexion === 'sftp' ? '#8b5cf6' : '#6b7280');
                          $connectionStatus = $isOnline ? '● Conectado' : '● Desconectado';
                          $connectionStatusBg = $isOnline ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                          $linkClass = $isOnline ? 'hover:shadow-lg' : 'pointer-events-none opacity-50';
                          $href = $isOnline ? route('nubes.browse', $nube) : '#';
                      @endphp
                      <div class="relative">
                          <a href="{{ $isOnline === false ? '#' : route('nubes.browse', $nube) }}" 
                              class="bg-white rounded-lg shadow transition p-4 sm:p-6 block border-l-4 {{ $isOnline === false ? 'pointer-events-none opacity-50' : 'hover:shadow-lg cursor-pointer' }}"
                              data-nube-id="{{ $nube->id }}"
                              data-original-href="{{ route('nubes.browse', $nube) }}"
                              style="border-color: {{ $borderColor }};">
                              <div class="flex items-start justify-between gap-3 pr-10">
                                  <div class="min-w-0 flex items-center gap-3">
                                      <span class="text-3xl sm:text-4xl shrink-0">📁</span>
                                      <div class="min-w-0">
                                          <h3 class="text-base sm:text-lg font-bold text-gray-800 truncate">{{ $nube->nombre }}</h3>
                                          <p class="text-sm text-gray-500 truncate">{{ $nube->host }}:{{ $nube->puerto }}</p>
                                      </div>
                                  </div>
                                  <span class="shrink-0 text-xs px-2 py-1 rounded text-white" style="background-color: {{ $connectionTypeColor }};">
                                      {{ strtoupper($nube->tipo_conexion) }}
                                  </span>
                              </div>
                              <div class="mt-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                  <div class="min-w-0 flex-1 space-y-1">
                                      <span class="block text-xs text-gray-400 font-mono truncate">{{ $nube->ruta_raiz ?: '/' }}</span>
                                      @if($nube->activo)
                                          <span class="text-xs text-green-600 font-semibold">● Activo</span>
                                      @else
                                          <span class="text-xs text-red-600 font-semibold">● Inactivo</span>
                                      @endif
                                  </div>
                                   <div class="text-xs sm:text-right">
                                       <span data-status
                                             class="inline-block font-semibold px-2 py-1 rounded {{ $isUnknown ? 'bg-gray-100 text-gray-600' : ($isOnline ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-600') }}">
                                           @if($isUnknown)
                                               Verificando...
                                           @elseif($isOnline)
                                               ● Conectado
                                           @else
                                               ● Desconectado
                                           @endif
                                       </span>
                                   </div>
                              </div>
                          </a>
                          @can('update', $nube)
                              <a href="{{ route('nubes.edit', $nube) }}" 
                                 class="absolute top-2 right-2 text-gray-400 hover:text-blue-600 bg-white hover:bg-blue-50 transition p-2 rounded-lg shadow-sm z-10"
                                 title="Editar fuente">
                                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                  </svg>
                              </a>
                          @endcan
                      </div>
                  @endforeach
             </div>
        @else
            <div class="bg-white rounded-lg shadow p-4 sm:p-6 sm:p-12 text-center">
                <span class="text-6xl">☁️</span>
                <h3 class="text-xl font-bold text-gray-700 mt-4">No hay fuentes de datos</h3>
                <p class="text-gray-500 mt-2">Crea tu primera fuente de datos FTP para comenzar.</p>
                @can('create', App\Models\Nube::class)
                    <a href="{{ route('nubes.create') }}" class="inline-block mt-4 text-white font-bold py-2 px-6 rounded-lg shadow transition"
                        style="background-color: #2563eb;">
                        + Nueva Fuente
                    </a>
                @else
                    <p class="text-sm text-gray-500 mt-4">Solo un administrador puede añadir fuentes de datos.</p>
                @endcan
            </div>
        @endif

        @if(auth()->user()->isAdmin())
            <div class="mt-8 bg-white rounded-lg shadow p-4 sm:p-6">
                <h2 class="text-lg font-bold text-gray-800 mb-4">Administración</h2>
                <div class="flex gap-3 flex-wrap">
                    <a href="{{ route('users.index') }}" class="text-white text-sm font-bold py-2 px-4 rounded shadow transition"
                        style="background-color: #2563eb;"
                        onmouseover="this.style.backgroundColor='#1d4ed8'"
                        onmouseout="this.style.backgroundColor='#2563eb'">
                        Gestionar Usuarios
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
    let autoRefreshInterval = null;
    let isRefreshing = false;
    
    function resetRefreshButton() {
        const refreshBtn = document.getElementById('refresh-status');
        if (refreshBtn) {
            refreshBtn.innerHTML = '🔄 Actualizar estados';
            refreshBtn.disabled = false;
            refreshBtn.classList.remove('opacity-75', 'cursor-not-allowed');
        }
    }
    
    function setRefreshButtonLoading() {
        const refreshBtn = document.getElementById('refresh-status');
        if (refreshBtn) {
            refreshBtn.innerHTML = '⏳ Actualizando...';
            refreshBtn.disabled = true;
            refreshBtn.classList.add('opacity-75', 'cursor-not-allowed');
        }
    }
     
    function checkNubeStatus() {
        if (isRefreshing) return;
        
        setRefreshButtonLoading();
        isRefreshing = true;
        
        const nubeCards = document.querySelectorAll('[data-nube-id]');
        const totalCards = nubeCards.length;
        
        if (totalCards === 0) {
            resetRefreshButton();
            isRefreshing = false;
            return;
        }
        
        let completedRequests = 0;
        
        nubeCards.forEach(card => {
            const nubeId = card.getAttribute('data-nube-id');
            const statusElement = card.querySelector('[data-status]');
            const originalHref = card.getAttribute('data-original-href');
            
            if (!statusElement || !nubeId) {
                completedRequests++;
                return;
            }
            
            fetch(`/nubes/${nubeId}/status`)
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    if (data.online) {
                        statusElement.innerHTML = '● Conectado';
                        statusElement.classList.remove('bg-red-50', 'text-red-600', 'bg-yellow-50', 'text-yellow-600', 'bg-gray-100', 'text-gray-600');
                        statusElement.classList.add('bg-green-50', 'text-green-600');
                        
                        card.href = originalHref;
                        card.classList.remove('pointer-events-none', 'opacity-50');
                        card.classList.add('hover:shadow-lg');
                    } else {
                        statusElement.innerHTML = '● Desconectado ⚠️';
                        statusElement.title = data.error || '';
                        statusElement.classList.remove('bg-green-50', 'text-green-600', 'bg-yellow-50', 'text-yellow-600', 'bg-gray-100', 'text-gray-600');
                        statusElement.classList.add('bg-red-50', 'text-red-600');
                        
                        card.href = '#';
                        card.classList.add('pointer-events-none', 'opacity-50');
                        card.classList.remove('hover:shadow-lg');
                        
                        if (data.error) {
                            showErrorToast(data.error, data.error_type);
                        }
                    }
                })
                .catch(error => {
                    statusElement.innerHTML = '● Error de conexión';
                    statusElement.classList.remove('bg-green-50', 'text-green-600', 'bg-red-50', 'text-red-600');
                    statusElement.classList.add('bg-yellow-50', 'text-yellow-600');
                    showErrorToast('No se pudo verificar el estado del servidor', 'network');
                })
                .finally(() => {
                    completedRequests++;
                    if (completedRequests === totalCards) {
                        resetRefreshButton();
                        isRefreshing = false;
                    }
                });
        });
    }
    
    function startAutoRefresh() {
        if (autoRefreshInterval) clearInterval(autoRefreshInterval);
        autoRefreshInterval = setInterval(checkNubeStatus, 30000);
    }
    
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            if (autoRefreshInterval) {
                clearInterval(autoRefreshInterval);
                autoRefreshInterval = null;
            }
        } else {
            startAutoRefresh();
            checkNubeStatus();
        }
    });
    
    document.addEventListener('DOMContentLoaded', () => {
        setTimeout(checkNubeStatus, 1000);
        startAutoRefresh();
    });
    
    document.getElementById('refresh-status')?.addEventListener('click', (e) => {
        e.preventDefault();
        if (!isRefreshing) {
            checkNubeStatus();
        }
    });
    
    function showErrorToast(message, errorType) {
        const toast = document.createElement('div');
        toast.className = 'fixed bottom-4 right-4 bg-red-500 text-white px-6 py-4 rounded-lg shadow-lg max-w-md z-50 animate-slide-up';
        toast.style.animation = 'slideUp 0.3s ease-out';
        
        let icon = '⚠️';
        if (errorType === 'auth') icon = '🔐';
        if (errorType === 'network') icon = '🌐';
        if (errorType === 'ssl') icon = '🔒';
        if (errorType === 'config') icon = '⚙️';
        
        toast.innerHTML = `
            <div class="flex items-start gap-3">
                <span class="text-2xl">${icon}</span>
                <div class="flex-1">
                    <p class="font-bold">Error de conexión</p>
                    <p class="text-sm opacity-90">${message}</p>
                </div>
                <button onclick="this.parentElement.parentElement.remove()" class="text-white hover:text-gray-200 ml-2">✕</button>
            </div>
        `;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.style.animation = 'slideDown 0.3s ease-out forwards';
            setTimeout(() => toast.remove(), 300);
        }, 5000);
    }
    
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideUp {
            from { transform: translateY(100%); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        @keyframes slideDown {
            from { transform: translateY(0); opacity: 1; }
            to { transform: translateY(100%); opacity: 0; }
        }
    `;
    document.head.appendChild(style);
</script>
@endsection
