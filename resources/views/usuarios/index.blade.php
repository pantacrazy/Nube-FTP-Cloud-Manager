@extends('layouts.app')

@section('title', 'Usuarios')

@section('content')
<div class="bg-gray-100 py-6 sm:py-12">
    <div class="max-w-6xl mx-auto">
        <div class="flex flex-col gap-4 sm:flex-row sm:justify-between sm:items-center mb-6">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">Usuarios</h1>
            <a href="{{ route('user.create') }}" class="inline-block text-center text-white font-bold py-2 px-4 rounded-lg shadow transition"
                style="background-color: #2563eb;"
                onmouseover="this.style.backgroundColor='#1d4ed8'"
                onmouseout="this.style.backgroundColor='#2563eb'">
                + Nuevo Usuario
            </a>
        </div>

        @if(session('success'))
            <div class="bg-green-100 text-green-700 text-sm px-4 py-3 rounded-lg mb-4">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white rounded-lg shadow p-4 sm:p-6">
            @if($users->isNotEmpty())
                <div class="mb-4" x-data="{busqueda: ''}">
                    <input type="text" x-model="busqueda" placeholder="Buscar usuario"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[720px]">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Nombre</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Rol</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Fecha de registro</th>
                                <th class="px-4 py-3 text-center text-sm font-semibold text-gray-600">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $usuario)
                                <tr class="border-b hover:bg-gray-50" x-show="!busqueda || '{{ strtolower($usuario->name) }}'.includes(busqueda.toLowerCase())">
                                    <td class="px-4 py-3 text-sm">{{ $usuario->name }}</td>
                                    <td class="px-4 py-3 text-sm">
                                        @if($usuario->isAdmin())
                                            <span class="inline-block px-2 py-1 text-xs font-bold text-white rounded" style="background-color: #dc2626;">Admin</span>
                                        @else
                                            <span class="inline-block px-2 py-1 text-xs font-bold text-white rounded" style="background-color: #6b7280;">Usuario</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-500">{{ $usuario->created_at?->format('d/m/Y') ?? 'N/A' }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex justify-center gap-2 flex-wrap">
                                            <a href="{{ route('user.show', $usuario->id) }}" class="inline-block text-white text-xs font-bold py-1 px-3 rounded shadow transition"
                                                style="background-color: #2563eb;"
                                                onmouseover="this.style.backgroundColor='#1d4ed8'"
                                                onmouseout="this.style.backgroundColor='#2563eb'" title="Ver">
                                                Ver
                                            </a>
                                            <a href="{{ route('user.edit', $usuario->id) }}" class="inline-block text-white text-xs font-bold py-1 px-3 rounded shadow transition"
                                                style="background-color: #16a34a;"
                                                onmouseover="this.style.backgroundColor='#15803d'"
                                                onmouseout="this.style.backgroundColor='#16a34a'" title="Editar">
                                                Editar
                                            </a>
                                            @auth
                                                @if(Auth::user()->isAdmin())
                                                    <a href="{{ route('user.reset-password', $usuario->id) }}" class="inline-block text-white text-xs font-bold py-1 px-3 rounded shadow transition"
                                                        style="background-color: #f59e0b;"
                                                        onmouseover="this.style.backgroundColor='#d97706'"
                                                        onmouseout="this.style.backgroundColor='#f59e0b'" title="Resetear contraseña">
                                                        Resetear
                                                    </a>
                                                    <a href="{{ route('user.edit-role', $usuario->id) }}" class="inline-block text-white text-xs font-bold py-1 px-3 rounded shadow transition"
                                                        style="background-color: #8b5cf6;"
                                                        onmouseover="this.style.backgroundColor='#7c3aed'"
                                                        onmouseout="this.style.backgroundColor='#8b5cf6'" title="Cambiar rol">
                                                        Rol
                                                    </a>
                                                @endif
                                            @endauth
                                            @auth
                                                @if(Auth::user()->isAdmin() && $usuario->id !== Auth::id())
                                                    <form action="{{ route('user.destroy', $usuario->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar este usuario?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-white text-xs font-bold py-1 px-3 rounded shadow transition"
                                                            style="background-color: #dc2626;"
                                                            onmouseover="this.style.backgroundColor='#b91c1c'"
                                                            onmouseout="this.style.backgroundColor='#dc2626'" title="Eliminar">
                                                            Eliminar
                                                        </button>
                                                    </form>
                                                @endif
                                            @endauth
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-gray-500 text-center py-8">No hay usuarios registrados.</p>
            @endif
        </div>
    </div>
</div>
@endsection
