<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAppIsSetup
{
    public function handle(Request $request, Closure $next): Response
    {
        $routeName = $request->route()?->getName();

        if (in_array($routeName, ['setup.show', 'setup.store', 'up', 'login', 'logout'])) {
            return $next($request);
        }

        if (Auth::check()) {
            return $next($request);
        }

        if (User::count() === 0) {
            return redirect()->route('setup.show');
        }

        return $next($request);
    }
}
