<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $usuarios = User::all();

        return view('usuarios.index', ['users' => $usuarios]);
    }

    public function show($id): View
    {
        $user = User::findOrFail($id);

        return view('usuarios.show', compact('user'));
    }

    public function create(): View
    {
        return view('usuarios.create');
    }

    public function store(StoreUserRequest $request)
    {
        $validated = $request->validated();

        User::create([
            'name' => $validated['name'],
            'password' => bcrypt($validated['password']),
        ]);

        return redirect()->route('users.index')->with('success', 'Usuario creado exitosamente');
    }

    public function edit($id): View
    {
        $user = User::findOrFail($id);

        return view('usuarios.edit', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:users,name,'.$id,
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $user->name = $validated['name'];

        if (! empty($validated['password'])) {
            $user->password = bcrypt($validated['password']);
        }

        $user->save();

        return redirect()->route('user.edit', $id)->with('success', 'Usuario actualizado exitosamente');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('users.index')->with('success', 'Usuario eliminado exitosamente');
    }

    public function showResetPassword($id): View
    {
        $user = User::findOrFail($id);

        return view('usuarios.reset-password', compact('user'));
    }

    public function resetPassword(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user->update([
            'password' => bcrypt($validated['password']),
        ]);

        return redirect()->route('users.index')->with('success', 'Contraseña de '.$user->name.' actualizada correctamente.');
    }

    public function editRole($id): View
    {
        $user = User::findOrFail($id);

        return view('usuarios.edit-role', compact('user'));
    }

    public function updateRole(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'role' => 'required|in:user,admin',
        ]);

        $user->update([
            'role' => $validated['role'],
        ]);

        return redirect()->route('users.index')->with('success', 'Rol de '.$user->name.' actualizado correctamente.');
    }
}
