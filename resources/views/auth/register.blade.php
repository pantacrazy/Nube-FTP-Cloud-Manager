@extends('layouts.auth')

@section('title', 'Registrarse')

@section('content')
<div class="min-h-screen bg-gray-100 flex items-center justify-center py-6 sm:py-12 px-3 sm:px-4">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-lg p-5 sm:p-8">
        <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Crear Cuenta</h2>

        <form action="{{ route('register') }}" method="POST" class="space-y-5">
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
                class="w-full text-white font-bold py-3 rounded-lg transition"
                style="background-color: #16a34a;"
                onmouseover="this.style.backgroundColor='#15803d'"
                onmouseout="this.style.backgroundColor='#16a34a'"
            >
                Registrarse
            </button>

        </form>

        <p class="text-center text-sm text-gray-500 mt-6">
            ¿Ya tienes cuenta? <a href="{{ route('login') }}" class="text-blue-600 hover:underline">Inicia sesión</a>
        </p>
    </div>
</div>
@endsection
