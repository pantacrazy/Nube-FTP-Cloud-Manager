@extends('layouts.app')

@section('title', 'Editar Rol')

@section('content')
<div class="bg-gray-100 flex items-center justify-center py-6 sm:py-12 px-3 sm:px-4 min-h-[calc(100vh-100px)]">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-lg p-5 sm:p-8">
        <div class="flex flex-col gap-2 sm:flex-row sm:justify-between sm:items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Editar Rol</h2>
            <a href="{{ route('users.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Volver</a>
        </div>

        <div class="bg-blue-50 text-blue-700 text-sm px-4 py-3 rounded-lg mb-4">
            Editando rol de <strong>{{ $user->name }}</strong>
        </div>

        <form action="{{ route('user.edit-role', $user->id) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Rol</label>
                <select name="role" class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="user" {{ $user->role === 'user' ? 'selected' : '' }}>Usuario</option>
                    <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Administrador</option>
                </select>
            </div>

            <button
                type="submit"
                class="w-full text-white font-bold py-3 rounded-lg transition"
                style="background-color: #8b5cf6;"
                onmouseover="this.style.backgroundColor='#7c3aed'"
                onmouseout="this.style.backgroundColor='#8b5cf6'"
            >
                Actualizar rol
            </button>

        </form>
    </div>
</div>
@endsection
