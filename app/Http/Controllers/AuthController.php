<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'name' => 'required|string',
            'password' => 'required',
        ]);

        if (Auth::attempt(['name' => $credentials['name'], 'password' => $credentials['password']], $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended(route('nubes.index'));
        }

        return back()->withErrors([
            'name' => 'Las credenciales no coinciden con nuestros registros.',
        ])->onlyInput('name');
    }

    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:users,name',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'password' => bcrypt($validated['password']),
        ]);

        Auth::login($user);

        return redirect()->route('nubes.index');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function showChangePassword(): View
    {
        return view('auth.change-password');
    }

    public function changePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if (! Auth::check()) {
            return back()->withErrors(['current_password' => 'Debes iniciar sesión.']);
        }

        if (! password_verify($validated['current_password'], Auth::user()->password)) {
            return back()->withErrors(['current_password' => 'La contraseña actual es incorrecta.']);
        }

        Auth::user()->update([
            'password' => bcrypt($validated['password']),
        ]);

        return redirect()->route('change-password')->with('success', 'Contraseña actualizada correctamente.');
    }

    public function showProfile(): View
    {
        return view('auth.profile');
    }

    public function updateProfile(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:users,name,'.Auth::id(),
            'current_password' => 'required',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        if (! password_verify($validated['current_password'], Auth::user()->password)) {
            return back()->withErrors(['current_password' => 'La contraseña actual es incorrecta.']);
        }

        $user = Auth::user();
        $user->name = $validated['name'];

        if (! empty($validated['password'])) {
            $user->password = bcrypt($validated['password']);
        }

        $user->save();

        return redirect()->route('profile')->with('success', 'Perfil actualizado correctamente.');
    }
}
