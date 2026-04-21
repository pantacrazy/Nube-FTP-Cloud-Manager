<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>@yield('title','Mi App Laravel')</title>
        <meta name="csrf-token" content="{{ csrf_token() }}">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('styles')
    </head>
    <body class="bg-gray-100 min-h-screen overflow-x-hidden antialiased">
        @auth
        <nav class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-3 sm:px-4 py-3 flex flex-col gap-3 sm:flex-row sm:justify-between sm:items-center">
                <a href="{{ route('nubes.index') }}" class="text-lg font-bold text-gray-800 hover:text-blue-600 transition text-center sm:text-left">
                    ☁️ Mis Fuentes
                </a>
                <div class="w-full sm:w-auto flex flex-col sm:flex-row flex-wrap items-stretch sm:items-center justify-center gap-2">
                    <a href="{{ route('nubes.index') }}" class="text-center text-sm text-white font-bold py-2 px-4 rounded shadow transition bg-violet-600 hover:bg-violet-700">
                        Fuentes de Datos
                    </a>
                    <a href="{{ route('profile') }}" class="text-center text-sm text-white font-bold py-2 px-4 rounded shadow transition bg-emerald-600 hover:bg-emerald-700">
                        Mi Perfil
                    </a>
                    <span class="text-center text-sm text-gray-600 sm:max-w-40 truncate">{{ Auth::user()->name }}</span>
                    <span class="self-center text-xs font-bold px-2 py-1 rounded text-white {{ Auth::user()->isAdmin() ? 'bg-red-600' : 'bg-gray-500' }}">
                        {{ Auth::user()->isAdmin() ? 'Admin' : 'Usuario' }}
                    </span>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full sm:w-auto text-white text-sm font-bold py-2 px-4 rounded shadow transition bg-red-600 hover:bg-red-700">
                            Cerrar Sesión
                        </button>
                    </form>
                </div>
            </div>
        </nav>
        @endauth

        <main class="w-full max-w-7xl mx-auto mt-4 sm:mt-8 px-3 sm:px-4">
            @yield('content')
        </main>
        
        @yield('scripts')
        @stack('scripts')
    </body>
</html>
