@props([
    'title' => 'Mi App',
])

@php
    $user = auth()->user();
@endphp

<nav class="bg-white shadow-sm">
    <div class="max-w-7xl mx-auto px-3 sm:px-4 py-3 flex flex-col gap-3 sm:flex-row sm:justify-between sm:items-center">
        <a href="{{ route('nubes.index') }}" class="text-lg font-bold text-gray-800 hover:text-blue-600 transition text-center sm:text-left">
            ☁️ {{ $title }}
        </a>
        
        <div class="w-full sm:w-auto flex flex-col sm:flex-row flex-wrap items-stretch sm:items-center justify-center gap-2 sm:gap-3">
            {{-- Primary Actions --}}
            <x-button href="{{ route('nubes.index') }}" variant="purple" size="sm">
                Fuentes de Datos
            </x-button>
            
            <x-button href="{{ route('profile') }}" variant="success" size="sm">
                Mi Perfil
            </x-button>
            
            {{-- User Info --}}
            <span class="text-center text-sm text-gray-600 sm:max-w-40 truncate">{{ $user?->name }}</span>
            
            <x-badge :variant="$user?->isAdmin() ? 'red' : 'gray'">
                {{ $user?->isAdmin() ? 'Admin' : 'Usuario' }}
            </x-badge>
            
            {{-- Logout --}}
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <x-button type="submit" variant="danger" size="sm">
                    Cerrar Sesión
                </x-button>
            </form>
        </div>
    </div>
</nav>
