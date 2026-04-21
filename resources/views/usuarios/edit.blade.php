@extends('layouts.app')

@section('title', 'Editar Usuario')

@section('content')
<div class="bg-gray-100 flex items-center justify-center py-6 sm:py-12 px-3 sm:px-4 min-h-[calc(100vh-100px)]">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-lg p-5 sm:p-8">
        <div class="flex flex-col gap-2 sm:flex-row sm:justify-between sm:items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Editar Usuario</h2>
            <a href="{{ route('users.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Volver</a>
        </div>

        @if(session('success'))
            <div class="bg-green-100 text-green-700 text-sm px-4 py-3 rounded-lg mb-4">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('user.update', $user->id) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            {{-- Nombre --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                <input
                    type="text"
                    name="name"
                    value="{{ old('name', $user->name) }}"
                    placeholder="Juan Pérez"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-400 @enderror"
                >
                @error('name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Contraseña --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Contraseña <span class="text-gray-400 font-normal">(dejar vacío para mantener la actual)</span>
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

            {{-- Confirmar Contraseña --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar Contraseña</label>
                <input
                    type="password"
                    name="password_confirmation"
                    placeholder="Repite la contraseña"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
            </div>

            {{-- Botón Actualizar --}}
            <button
                type="submit"
                class="w-full text-white font-bold py-3 rounded-lg transition"
                style="background-color: #16a34a;"
                onmouseover="this.style.backgroundColor='#15803d'"
                onmouseout="this.style.backgroundColor='#16a34a'"
            >
                Actualizar usuario
            </button>

        </form>

        <div class="mt-6 pt-6 border-t border-gray-200">
            <form action="{{ route('user.destroy', $user->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar este usuario?')">
                @csrf
                @method('DELETE')
                <button
                    type="submit"
                    class="w-full text-white font-bold py-3 rounded-lg transition"
                    style="background-color: #dc2626;"
                    onmouseover="this.style.backgroundColor='#b91c1c'"
                    onmouseout="this.style.backgroundColor='#dc2626'"
                >
                    Eliminar usuario
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
