<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Servicio no disponible</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-lg p-5 sm:p-8 text-center">
        <div class="mb-6">
            <svg class="mx-auto h-16 w-16 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
        </div>

        <h1 class="text-2xl font-bold text-gray-800 mb-2">Servicio temporalmente no disponible</h1>
        <p class="text-gray-600 mb-6">Estamos experimentando problemas técnicos. Por favor, intenta de nuevo en unos minutos.</p>

        <div class="bg-gray-50 rounded-lg p-4 text-left mb-6">
            <h2 class="text-sm font-semibold text-gray-700 mb-2">Posibles causas:</h2>
            <ul class="text-sm text-gray-600 space-y-1">
                <li>• El servidor de base de datos no está respondiendo</li>
                <li>• Las credenciales de base de datos no son correctas</li>
                <li>• El servidor de base de datos está en mantenimiento</li>
                <li>• Problemas de conexión de red</li>
            </ul>
        </div>

        <div class="bg-blue-50 rounded-lg p-4 text-left mb-6">
            <h2 class="text-sm font-semibold text-blue-700 mb-2">Recomendaciones:</h2>
            <ul class="text-sm text-blue-600 space-y-1">
                <li>• Verifica que el servidor de base de datos esté activo</li>
                <li>• Revisa las variables DB_* en el archivo .env</li>
                <li>• Contacta al administrador del sistema si el problema persiste</li>
            </ul>
        </div>

        <button onclick="location.reload()" class="w-full bg-blue-600 text-white font-semibold py-3 rounded-lg hover:bg-blue-700 transition">
            Reintentar
        </button>

        <p class="text-xs text-gray-400 mt-6">Error 503 | Database Connection Unavailable</p>
    </div>
</body>
</html>
