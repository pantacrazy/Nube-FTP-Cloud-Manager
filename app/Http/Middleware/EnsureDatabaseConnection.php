<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class EnsureDatabaseConnection
{
    protected const CACHE_KEY = 'db_connection_ok';

    protected const CACHE_TTL = 10;

    protected const TIMEOUT = 5;

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->isExcludedRoute($request)) {
            return $next($request);
        }

        if (! $this->canConnectToDatabase()) {
            return $this->databaseUnavailableResponse();
        }

        return $next($request);
    }

    protected function isExcludedRoute(Request $request): bool
    {
        $excludedRoutes = [
            'up',
            'setup',
            'setup.show',
            'setup.store',
            'login',
            'logout',
        ];

        $routeName = $request->route()?->getName();
        $path = $request->path();

        if (in_array($routeName, $excludedRoutes, true)) {
            return true;
        }

        if (in_array($path, ['up', 'setup', 'login'], true)) {
            return true;
        }

        return false;
    }

    protected function canConnectToDatabase(): bool
    {
        if (app()->environment('testing')) {
            return true;
        }

        if (Cache::has(self::CACHE_KEY)) {
            return Cache::get(self::CACHE_KEY) === true;
        }

        try {
            DB::connection()->getPdo();

            DB::select('SELECT 1');

            Cache::put(self::CACHE_KEY, true, self::CACHE_TTL);

            return true;
        } catch (\Exception $e) {
            Cache::put(self::CACHE_KEY, false, self::CACHE_TTL);

            return false;
        }
    }

    protected function databaseUnavailableResponse(): Response
    {
        if (request()->expectsJson()) {
            return response()->json([
                'error' => 'Service temporarily unavailable',
                'message' => 'Database connection is not available. Please try again later.',
            ], 503);
        }

        return response()->view('errors.database', [], 503);
    }
}
