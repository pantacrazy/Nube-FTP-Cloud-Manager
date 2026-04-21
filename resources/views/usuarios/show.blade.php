@extends('layouts.app')

@section('title', 'Detalle del Usuario')

@section('content')
<div class="bg-gray-100 flex items-center justify-center py-6 sm:py-12 px-3 sm:px-4 min-h-[calc(100vh-100px)]">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-lg p-5 sm:p-8">
        <div class="flex flex-col gap-2 sm:flex-row sm:justify-between sm:items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Detalle del Usuario</h2>
            <a href="{{ route('users.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Volver</a>
        </div>

        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">Nombre</label>
                <p class="text-gray-800 text-lg">{{ $user->name }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">Fecha de registro</label>
                <p class="text-gray-800">{{ $user->created_at?->format('d/m/Y H:i') ?? 'N/A' }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">Última actualización</label>
                <p class="text-gray-800">{{ $user->updated_at?->format('d/m/Y H:i') ?? 'N/A' }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-8">
            <a href="{{ route('user.edit', $user->id) }}" class="flex-1 bg-yellow-500 hover:bg-yellow-600 text-white text-center font-semibold py-2 rounded-lg transition">
                Editar
            </a>
            <form action="{{ route('user.destroy', $user->id) }}" method="POST" class="sm:flex-1" onsubmit="return confirm('¿Estás seguro de eliminar este usuario?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-2 rounded-lg transition">
                    Eliminar
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
