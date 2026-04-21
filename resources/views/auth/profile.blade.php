@extends('layouts.app')

@section('title', 'Mi Perfil')

@section('content')
<div class="bg-gray-100 flex items-center justify-center py-6 sm:py-12 px-3 sm:px-4 min-h-[calc(100vh-100px)]">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-lg p-5 sm:p-8">
        <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Mi Perfil</h2>

        @if(session('success'))
            <div class="bg-green-100 text-green-700 text-sm px-4 py-3 rounded-lg mb-4">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('profile') }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            {{-- Nombre de usuario --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre de usuario</label>
                <input
                    type="text"
                    name="name"
                    value="{{ old('name', Auth::user()->name) }}"
                    placeholder="Tu nombre de usuario"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-400 @enderror"
                >
                @error('name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Contraseña actual --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Contraseña actual <span class="text-red-500">*</span></label>
                <input
                    type="password"
                    name="current_password"
                    placeholder="Ingresa tu contraseña actual"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('current_password') border-red-400 @enderror"
                >
                @error('current_password')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Nueva contraseña --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Nueva contraseña <span class="text-gray-400 font-normal">(opcional)</span>
                </label>
                <input
                    type="password"
                    name="password"
                    placeholder="Mínimo 8 caracteres"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('password') border-red-400 @enderror"
                >
                @error('password')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Confirmar nueva contraseña --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar nueva contraseña</label>
                <input
                    type="password"
                    name="password_confirmation"
                    placeholder="Repite la nueva contraseña"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
            </div>

            {{-- Botón --}}
            <button
                type="submit"
                class="w-full text-white font-bold py-3 rounded-lg transition"
                style="background-color: #059669;"
                onmouseover="this.style.backgroundColor='#047857'"
                onmouseout="this.style.backgroundColor='#059669'"
            >
                Guardar cambios
            </button>
        </form>

        <div class="mt-4 pt-4 border-t border-gray-200 text-center">
            <p class="text-xs text-gray-500">
                Rol: <span class="font-bold" style="color: {{ Auth::user()->isAdmin() ? '#dc2626' : '#6b7280' }};">
                    {{ Auth::user()->isAdmin() ? 'Administrador' : 'Usuario' }}
                </span>
            </p>
        </div>
    </div>
</div>
@endsection
