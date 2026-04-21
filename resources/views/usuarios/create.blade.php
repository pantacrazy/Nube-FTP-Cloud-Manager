{{-- resources/views/usuarios/create.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="bg-gray-100 flex items-center justify-center py-6 sm:py-12 px-3 sm:px-4 min-h-[calc(100vh-100px)]">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-lg p-5 sm:p-8">

        <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Crear Usuario</h2>

        @if(session('success'))
            <div class="bg-green-100 text-green-700 text-sm px-4 py-3 rounded-lg mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 text-red-700 text-sm px-4 py-3 rounded-lg mb-4">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('user.store') }}" method="POST" class="space-y-5">
            @csrf

            {{-- Nombre --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                <input
                    type="text"
                    name="name"
                    value="{{ old('name') }}"
                    placeholder="Juan Pérez"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-400 @enderror"
                >
                @error('name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Contraseña --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Contraseña</label>
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

            {{-- Botón --}}
            <button
                type="submit"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 rounded-lg transition"
            >
                Guardar usuario
            </button>

        </form>

        <p class="text-center text-sm text-gray-500 mt-4">
            ¿Ya tienes cuenta? <a href="{{ route('users.index') }}" class="text-blue-600 hover:underline">Ver usuarios</a>
        </p>
    </div>
</div>
@endsection
