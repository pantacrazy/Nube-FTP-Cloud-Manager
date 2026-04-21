@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="max-w-md w-full bg-white rounded-lg shadow-md p-5 sm:p-8">
        <div class="text-center mb-8">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-800 mb-2">Configuración Inicial</h1>
            <p class="text-gray-600">Creá tu cuenta de administrador para comenzar</p>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('setup.store') }}">
            @csrf

            <div class="mb-6">
                <label for="name" class="block text-gray-700 font-medium mb-2">
                    Nombre de usuario
                </label>
                <input 
                    type="text" 
                    name="name" 
                    id="name" 
                    value="{{ old('name') }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror"
                    placeholder="Tu nombre de usuario"
                    required 
                    autofocus
                >
                @error('name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label for="password" class="block text-gray-700 font-medium mb-2">
                    Contraseña
                </label>
                <input 
                    type="password" 
                    name="password" 
                    id="password" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('password') border-red-500 @enderror"
                    placeholder="Mínimo 8 caracteres"
                    required
                >
                @error('password')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label for="password_confirmation" class="block text-gray-700 font-medium mb-2">
                    Confirmar contraseña
                </label>
                <input 
                    type="password" 
                    name="password_confirmation" 
                    id="password_confirmation" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Repetí la contraseña"
                    required
                >
            </div>

            <button 
                type="submit" 
                class="w-full bg-blue-600 text-white font-bold py-3 px-4 rounded-lg hover:bg-blue-700 transition duration-200"
            >
                Crear cuenta de administrador
            </button>
        </form>

        <div class="mt-6 text-center text-sm text-gray-500">
            <p>Esta será tu cuenta principal de administrador.</p>
        </div>
    </div>
</div>
@endsection
