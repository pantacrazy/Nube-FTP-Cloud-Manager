@extends('layouts.auth')

@section('title', 'Iniciar Sesión')

@section('content')
<div class="min-h-screen bg-gray-100 flex items-center justify-center py-6 sm:py-12 px-3 sm:px-4">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-lg p-5 sm:p-8">
        <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Iniciar Sesión</h2>

        @if(session('success'))
            <div class="bg-green-100 text-green-700 text-sm px-4 py-3 rounded-lg mb-4">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('login') }}" method="POST" class="space-y-5">
            @csrf

            {{-- Nombre de usuario --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre de usuario</label>
                <input
                    type="text"
                    name="name"
                    value="{{ old('name') }}"
                    placeholder="admin"
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
                    placeholder="••••••••"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
            </div>

            {{-- Recordar --}}
            <div class="flex items-center">
                <input type="checkbox" name="remember" id="remember" class="h-4 w-4 text-blue-600 border-gray-300 rounded">
                <label for="remember" class="ml-2 text-sm text-gray-600">Recordarme</label>
            </div>

            {{-- Botón --}}
            <button
                type="submit"
                class="w-full text-white font-bold py-3 rounded-lg transition"
                style="background-color: #2563eb;"
                onmouseover="this.style.backgroundColor='#1d4ed8'"
                onmouseout="this.style.backgroundColor='#2563eb'"
            >
                Iniciar Sesión
            </button>

        </form>

        <p class="text-center text-sm text-gray-500 mt-6">
            ¿No tienes cuenta? <a href="{{ route('register') }}" class="text-blue-600 hover:underline">Regístrate aquí</a>
        </p>
    </div>
</div>
@endsection
