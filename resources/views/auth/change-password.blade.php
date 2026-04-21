@extends('layouts.app')

@section('title', 'Cambiar Contraseña')

@section('content')
<div class="bg-gray-100 flex items-center justify-center py-6 sm:py-12 px-3 sm:px-4 min-h-[calc(100vh-100px)]">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-lg p-5 sm:p-8">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Cambiar Contraseña</h2>
            <a href="{{ route('users.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Volver</a>
        </div>

        @if(session('success'))
            <div class="bg-green-100 text-green-700 text-sm px-4 py-3 rounded-lg mb-4">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('change-password') }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            {{-- Contraseña actual --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Contraseña actual</label>
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

            {{-- Nueva Contraseña --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nueva contraseña</label>
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

            {{-- Confirmar Nueva Contraseña --}}
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
                style="background-color: #2563eb;"
                onmouseover="this.style.backgroundColor='#1d4ed8'"
                onmouseout="this.style.backgroundColor='#2563eb'"
            >
                Actualizar contraseña
            </button>

        </form>
    </div>
</div>
@endsection
