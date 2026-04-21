<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SetupController extends Controller
{
    public function show(): View|RedirectResponse
    {
        if (User::count() > 0) {
            return redirect()->route('login');
        }

        return view('setup');
    }

    public function store(Request $request): RedirectResponse
    {
        if (User::count() > 0) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:users,name',
            'password' => 'required|string|min:8|confirmed',
        ]);

        User::create([
            'name' => $validated['name'],
            'password' => bcrypt($validated['password']),
            'role' => 'admin',
        ]);

        return redirect()->route('login')->with('success', '¡Perfecto! Tu cuenta de administrador fue creada. Iniciá sesión para continuar.');
    }
}
