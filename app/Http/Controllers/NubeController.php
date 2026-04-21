<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNubeRequest;
use App\Http\Requests\UpdateNubeRequest;
use App\Models\Nube;
use App\Services\FtpService;
use App\Traits\CategorizesFtpErrors;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class NubeController extends Controller
{
    use CategorizesFtpErrors;

    public function index(): View
    {
        $nubesWithStatus = Nube::latest()->get()->map(function ($nube) {
            $nube->is_online = Cache::get($this->nubeStatusCacheKey($nube), null);

            return $nube;
        });

        return view('nubes.index', compact('nubesWithStatus'));
    }

    public function create(): View
    {
        $this->authorize('create', Nube::class);

        return view('nubes.create');
    }

    public function store(StoreNubeRequest $request)
    {
        $this->authorize('create', Nube::class);

        $validated = $request->validated();

        $validated['password'] = encrypt($validated['password']);
        $validated['ssl_pasv'] = $request->boolean('ssl_pasv');
        $validated['activo'] = $request->boolean('activo');
        $validated['user_id'] = auth()->id();

        Nube::create($validated);

        return redirect()->route('nubes.index')->with('success', 'Fuente de datos creada exitosamente.');
    }

    public function show(Nube $nube): View
    {
        $this->authorize('view', $nube);

        return view('nubes.show', compact('nube'));
    }

    public function edit(Nube $nube): View
    {
        $this->authorize('update', $nube);

        return view('nubes.edit', compact('nube'));
    }

    public function update(UpdateNubeRequest $request, Nube $nube)
    {
        $this->authorize('update', $nube);

        $validated = $request->validated();

        if (! empty($validated['password'])) {
            $validated['password'] = encrypt($validated['password']);
        } else {
            unset($validated['password']);
        }

        $validated['ssl_pasv'] = $request->boolean('ssl_pasv');
        $validated['activo'] = $request->boolean('activo');

        $nube->update($validated);

        return redirect()->route('nubes.edit', $nube)->with('success', 'Fuente de datos actualizada exitosamente.');
    }

    public function destroy(Nube $nube)
    {
        $this->authorize('delete', $nube);

        $nube->delete();

        return redirect()->route('nubes.index')->with('success', 'Fuente de datos eliminada exitosamente.');
    }

    public function testConnection(Nube $nube)
    {
        $this->authorize('update', $nube);

        try {
            $ftp = new FtpService($nube);
            $ftp->connect();
            $ftp->listFiles('/');

            $ftp->disconnect();

            $tipoLabel = match ($nube->tipo_conexion) {
                'sftp' => 'SFTP',
                'ftps' => 'FTPS',
                default => 'FTP',
            };

            return response()->json([
                'success' => true,
                'message' => "Conexión exitosa al servidor {$tipoLabel}.",
            ]);
        } catch (\Exception $e) {
            $errorMessage = $this->getHumanReadableError($e->getMessage(), $this->categorizeError($e->getMessage()));

            return response()->json([
                'success' => false,
                'message' => $errorMessage,
            ]);
        }
    }

    public function testConnectionPreview(Request $request)
    {
        $this->authorize('create', Nube::class);

        $validated = $request->validate([
            'host' => 'required|string|max:255',
            'puerto' => 'required|integer|min:1|max:65535',
            'usuario' => 'required|string|max:255',
            'password' => 'required|string',
            'ruta_raiz' => 'nullable|string|max:500',
            'tipo_conexion' => 'required|in:ftp,ftps,sftp',
            'timeout' => 'required|integer|min:5|max:120',
        ]);

        try {
            $tempNube = new Nube([
                'host' => $validated['host'],
                'puerto' => $validated['puerto'],
                'usuario' => $validated['usuario'],
                'password' => encrypt($validated['password']),
                'ruta_raiz' => $validated['ruta_raiz'] ?? '/',
                'tipo_conexion' => $validated['tipo_conexion'],
                'timeout' => $validated['timeout'],
            ]);

            $ftp = new FtpService($tempNube);
            $ftp->connect();
            $ftp->listFiles('/');

            $ftp->disconnect();

            $tipoLabel = match ($validated['tipo_conexion']) {
                'sftp' => 'SFTP',
                'ftps' => 'FTPS',
                default => 'FTP',
            };

            return response()->json([
                'success' => true,
                'message' => "Conexión exitosa al servidor {$tipoLabel}.",
            ]);
        } catch (\Exception $e) {
            $errorMessage = $this->getHumanReadableError($e->getMessage(), $this->categorizeError($e->getMessage()));

            return response()->json([
                'success' => false,
                'message' => $errorMessage,
            ]);
        }
    }

    public function browse(Nube $nube, Request $request): View
    {
        $this->authorize('view', $nube);

        $path = $this->validateRemotePath($request->get('path') ?: '/', true);

        try {
            $browseState = $this->loadBrowseState($nube, $path);

            return view('nubes.browse', [
                'nube' => $nube,
                'items' => $browseState['items'],
                'path' => $path,
                'permissions' => $browseState['permissions'],
                'breadcrumbs' => $this->buildBreadcrumbs($path),
                'connectionError' => null,
            ]);
        } catch (\Exception $e) {
            $errorType = $this->categorizeError($e->getMessage());
            $errorMessage = $this->getHumanReadableError($e->getMessage(), $errorType);

            return view('nubes.browse', [
                'nube' => $nube,
                'items' => [],
                'path' => $path,
                'permissions' => ['can_write' => false, 'can_delete' => false, 'can_upload' => false],
                'breadcrumbs' => $this->buildBreadcrumbs($path),
                'connectionError' => $errorMessage,
                'errorType' => $errorType,
            ]);
        }
    }

    public function browseItems(Nube $nube, Request $request)
    {
        $this->authorize('view', $nube);

        $path = $this->validateRemotePath($request->get('path') ?: '/', true);

        try {
            $includePermissions = $request->boolean('permissions', false);
            $browseState = $this->loadBrowseState($nube, $path, $includePermissions);

            return response()->json([
                'success' => true,
                'items' => $browseState['items'],
                'permissions' => $browseState['permissions'],
                'refreshed_at' => now()->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            $errorType = $this->categorizeError($e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $this->getHumanReadableError($e->getMessage(), $errorType),
                'error_type' => $errorType,
            ], $this->isMissingRemoteItemError($e) ? 404 : 500);
        }
    }

    public function createFolder(Nube $nube, Request $request)
    {
        $this->authorize('update', $nube);

        $validated = $request->validate([
            'path' => 'nullable|string',
            'name' => 'required|string|max:255',
        ]);

        $path = $this->validateRemotePath($validated['path'] ?? '', true);
        $name = $this->validateRemoteName($validated['name']);
        $newPath = $path ? "{$path}/{$name}" : $name;

        try {
            $ftp = new FtpService($nube);
            $ftp->connect();
            if (! $ftp->exists($path, 'directory')) {
                throw new \RuntimeException('La carpeta de destino ya no existe o no estÃ¡ disponible.');
            }
            if (! $ftp->getPermissions($path)['can_write']) {
                throw new \RuntimeException('El servidor FTP no permite crear carpetas en esta ubicaciÃ³n.');
            }
            $result = $ftp->createDirectory($newPath);
            $ftp->disconnect();
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $this->getHumanReadableError($e->getMessage(), $this->categorizeError($e->getMessage())));
        }

        if ($result) {
            return redirect()->route('nubes.browse', ['nube' => $nube, 'path' => $path])->with('success', 'Carpeta creada exitosamente.');
        }

        return redirect()->back()->with('error', 'No se pudo crear la carpeta.');
    }

    public function deleteItem(Nube $nube, Request $request)
    {
        $this->authorize('update', $nube);

        $validated = $request->validate([
            'path' => 'required|string',
            'type' => 'required|in:file,directory',
            'name' => 'required|string',
        ]);

        $path = $this->validateRemotePath($validated['path']);

        try {
            $ftp = new FtpService($nube);
            $ftp->connect();

            if (! $ftp->exists($path, $validated['type'])) {
                throw new \RuntimeException('El elemento remoto ya no existe o no estÃ¡ disponible.');
            }

            if ($validated['type'] === 'directory') {
                $result = $ftp->deleteDirectory($path);
            } else {
                $result = $ftp->deleteFile($path);
            }

            $ftp->disconnect();
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $this->getHumanReadableError($e->getMessage(), $this->categorizeError($e->getMessage())));
        }

        $parentPath = dirname($path);
        if ($parentPath === '.' || $parentPath === '') {
            $parentPath = '';
        }

        if ($result) {
            return redirect()->route('nubes.browse', ['nube' => $nube, 'path' => $parentPath])->with('success', 'Elemento eliminado exitosamente.');
        }

        return redirect()->back()->with('error', 'No se pudo eliminar el elemento.');
    }

    public function renameItem(Nube $nube, Request $request)
    {
        $this->authorize('update', $nube);

        $validated = $request->validate([
            'path' => 'required|string',
            'new_name' => 'required|string|max:255',
        ]);

        $oldPath = $this->validateRemotePath($validated['path']);
        $newName = $this->validateRemoteName($validated['new_name']);
        $parentDir = dirname($oldPath);
        $newPath = $parentDir !== '.' ? "{$parentDir}/{$newName}" : $newName;

        try {
            $ftp = new FtpService($nube);
            $ftp->connect();
            if (! $ftp->exists($oldPath)) {
                throw new \RuntimeException('El elemento remoto ya no existe o no estÃ¡ disponible.');
            }
            $result = $ftp->rename($oldPath, $newPath);
            $ftp->disconnect();
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $this->getHumanReadableError($e->getMessage(), $this->categorizeError($e->getMessage())));
        }

        if ($result) {
            return redirect()->route('nubes.browse', ['nube' => $nube, 'path' => $parentDir !== '.' ? $parentDir : ''])->with('success', 'Elemento renombrado exitosamente.');
        }

        return redirect()->back()->with('error', 'No se pudo renombrar el elemento.');
    }

    public function uploadFile(Nube $nube, Request $request)
    {
        $this->authorize('update', $nube);

        $request->validate([
            'file' => 'required|file',
            'path' => 'nullable|string',
        ]);

        $path = $this->validateRemotePath($request->get('path', ''), true);
        $uploadedFile = $request->file('file');
        $fileName = $this->validateRemoteName($uploadedFile->getClientOriginalName());
        $remotePath = $path ? "{$path}/{$fileName}" : $fileName;
        $ftp = null;

        try {
            $ftp = new FtpService($nube);
            $ftp->connect();
            if (! $ftp->exists($path, 'directory')) {
                throw new \RuntimeException('La carpeta de destino ya no existe o no estÃ¡ disponible.');
            }
            if (! $ftp->getPermissions($path)['can_upload']) {
                throw new \RuntimeException('El servidor FTP no permite subir archivos en esta ubicaciÃ³n.');
            }
            $result = $ftp->uploadFile($remotePath, $uploadedFile->getRealPath());
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $this->getHumanReadableError($e->getMessage(), $this->categorizeError($e->getMessage())),
                ], 422);
            }

            return redirect()->back()->with('error', $this->getHumanReadableError($e->getMessage(), $this->categorizeError($e->getMessage())));
        } finally {
            if ($ftp) {
                $ftp->disconnect();
            }
        }

        if ($result) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Archivo subido exitosamente.',
                    'redirect' => route('nubes.browse', ['nube' => $nube, 'path' => $path]),
                    'path' => $path,
                ]);
            }

            return redirect()->route('nubes.browse', ['nube' => $nube, 'path' => $path])->with('success', 'Archivo subido exitosamente.');
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo subir el archivo.',
            ], 422);
        }

        return redirect()->back()->with('error', 'No se pudo subir el archivo.');
    }

    public function downloadSync(Nube $nube, Request $request)
    {
        $this->authorize('view', $nube);
        @set_time_limit(0);

        $validated = $request->validate([
            'path' => 'required|string',
            'name' => 'required|string',
            'type' => 'required|in:file,directory',
            'jobId' => 'nullable|string',
        ]);

        $path = $this->validateRemotePath($validated['path'], $validated['type'] === 'directory');
        $name = $this->validateRemoteName($validated['name']);

        $ftp = new FtpService($nube);
        $jobId = $request->input('jobId', 'dl_'.uniqid(true));
        $cacheKey = "download_{$jobId}";
        $cancelKey = "download_cancel_{$jobId}";

        try {
            Cache::forget($cancelKey);
            $this->putDownloadProgress($cacheKey, [
                'status' => 'pending',
                'message' => 'Iniciando descarga...',
                'downloaded' => 0,
                'total' => 0,
                'percent' => 0,
                'phases' => [
                    'fetching' => ['label' => 'Lectura/transferencia al servidor', 'percent' => 0, 'weight' => 50],
                    'compressing' => ['label' => 'Compresion ZIP en servidor', 'percent' => 0, 'weight' => 30],
                    'downloading' => ['label' => 'Descarga del ZIP al cliente', 'percent' => 0, 'weight' => 20],
                ],
            ], 300);

            $ftp->connect();

            if (! $ftp->exists($path, $validated['type'])) {
                throw new \RuntimeException($validated['type'] === 'directory'
                    ? 'La carpeta remota ya no existe o no estÃ¡ disponible.'
                    : 'El archivo remoto ya no existe o no estÃ¡ disponible.');
            }

            $fileName = $validated['type'] === 'directory'
                ? $name.'.zip'
                : $name;

            if ($validated['type'] === 'file') {
                $fileSize = $ftp->getFileSize($path);
                $finalJobId = $jobId;

                return response()->stream(function () use ($ftp, $path, $cacheKey, $fileName, $fileSize, $finalJobId) {
                    $buffer = '';
                    $flushThreshold = 1024 * 1024;
                    $flushOutput = function () use (&$buffer): void {
                        if ($buffer === '') {
                            return;
                        }

                        echo $buffer;
                        $buffer = '';

                        if (ob_get_level() > 0) {
                            @ob_flush();
                        }

                        flush();
                    };

                    try {
                        $ftp->streamFileToOutput(
                            $path,
                            function (string $chunk) use (&$buffer, $flushThreshold, $flushOutput): void {
                                $buffer .= $chunk;

                                if (strlen($buffer) >= $flushThreshold) {
                                    $flushOutput();
                                }
                            },
                            null
                        );
                        $flushOutput();

                        $this->putDownloadProgress($cacheKey, [
                            'status' => 'complete',
                            'message' => 'Descarga completada',
                            'downloaded' => $fileSize,
                            'total' => $fileSize,
                            'percent' => 100,
                            'fileName' => $fileName,
                        ], 300);
                    } finally {
                        $ftp->disconnect();
                        $this->forgetDownloadProgress("download_{$finalJobId}");
                    }
                }, 200, array_filter([
                    'Content-Type' => 'application/octet-stream',
                    'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
                    'Content-Length' => $fileSize > 0 ? (string) $fileSize : null,
                    'X-Job-Id' => $jobId,
                    'X-Accel-Buffering' => 'no',
                    'Cache-Control' => 'no-cache, no-store, must-revalidate',
                ]));
            }

            $tempFile = $ftp->downloadDirectory($path, function ($data) use ($cacheKey, $cancelKey) {
                if (Cache::get($cancelKey)) {
                    throw new \RuntimeException('Descarga cancelada por el usuario.');
                }

                $this->putDownloadProgress($cacheKey, $data, 300);
            });

            $ftp->disconnect();

            if (! $tempFile || ! file_exists($tempFile)) {
                $this->forgetDownloadProgress($cacheKey);

                return response()->json([
                    'error' => 'No se pudo descargar el archivo. Verifica que el archivo exista.',
                ], 500);
            }

            $fileSize = filesize($tempFile);

            $this->putDownloadProgress($cacheKey, $this->folderProgressState(
                'ready_for_download',
                'ZIP listo para descargar',
                100,
                100,
                0,
                $fileSize,
                $fileSize,
                $fileName
            ), 300);

            return response()->stream(function () use ($tempFile, $cacheKey, $cancelKey, $fileSize, $fileName) {
                if (! file_exists($tempFile)) {
                    return;
                }

                $handle = fopen($tempFile, 'rb');
                if (! $handle) {
                    return;
                }

                $sent = 0;
                $downloadStartedAt = microtime(true);
                $lastProgressAt = null;
                $chunkSize = 1024 * 1024;
                $progressIntervalSeconds = 0.25;
                $downloadUpdates = 0;
                while (! feof($handle)) {
                    if (Cache::get($cancelKey)) {
                        break;
                    }

                    $chunk = fread($handle, $chunkSize);
                    if ($chunk !== false && strlen($chunk) > 0) {
                        echo $chunk;
                        $sent += strlen($chunk);
                        $downloadPercent = $fileSize > 0 ? min(100, round(($sent / $fileSize) * 100, 1)) : 0;
                        $now = microtime(true);
                        $shouldReportProgress = $lastProgressAt === null
                            || ($now - $lastProgressAt) >= $progressIntervalSeconds
                            || ($fileSize > 0 && $sent >= $fileSize);

                        if ($shouldReportProgress) {
                            $downloadUpdates++;
                            $this->putDownloadProgress($cacheKey, $this->folderProgressState(
                                'downloading',
                                'Descargando ZIP al cliente...',
                                100,
                                100,
                                $downloadPercent,
                                $sent,
                                $fileSize,
                                $fileName,
                                [
                                    'downloading_elapsed_ms' => round(($now - $downloadStartedAt) * 1000, 1),
                                    'downloading_updates' => $downloadUpdates,
                                    'last_update_delta_ms' => $lastProgressAt ? round(($now - $lastProgressAt) * 1000, 1) : null,
                                    'stream_chunk_size' => $chunkSize,
                                ]
                            ), 300);
                            $lastProgressAt = $now;
                        }

                        flush();
                    }
                }

                fclose($handle);

                if (Cache::get($cancelKey)) {
                    $this->putDownloadProgress($cacheKey, $this->folderProgressState(
                        'cancelled',
                        'Descarga cancelada',
                        100,
                        100,
                        $fileSize > 0 ? min(100, round(($sent / $fileSize) * 100, 1)) : 0,
                        $sent,
                        $fileSize,
                        $fileName
                    ), 300);

                    if (file_exists($tempFile)) {
                        @unlink($tempFile);
                    }

                    Cache::forget($cancelKey);

                    return;
                }

                $this->putDownloadProgress($cacheKey, $this->folderProgressState(
                    'completed',
                    'Descarga completada',
                    100,
                    100,
                    100,
                    $fileSize,
                    $fileSize,
                    $fileName,
                    [
                        'downloading_elapsed_ms' => round((microtime(true) - $downloadStartedAt) * 1000, 1),
                        'downloading_updates' => $downloadUpdates,
                    ]
                ), 300);

                if (file_exists($tempFile)) {
                    @unlink($tempFile);
                }

                // Keep the completed state observable until the cache TTL expires.
                Cache::forget($cancelKey);
            }, 200, [
                'Content-Type' => 'application/octet-stream',
                'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
                'Content-Length' => (string) $fileSize,
                'X-Job-Id' => $jobId,
                'X-Accel-Buffering' => 'no',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
            ]);

        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            $this->forgetDownloadProgress($cacheKey);

            return response()->json([
                'error' => 'Error de configuración: La contraseña está corrupta. Por favor, actualiza la contraseña.',
            ], 500);
        } catch (\Exception $e) {
            try {
                $ftp->disconnect();
            } catch (\Exception $ex) {
            }
            $wasCancelled = Cache::get($cancelKey);
            Cache::forget($cancelKey);
            $this->putDownloadProgress($cacheKey, $this->folderProgressState(
                $wasCancelled ? 'cancelled' : 'failed',
                $wasCancelled ? 'Descarga cancelada' : $e->getMessage(),
                0,
                0,
                0,
                0,
                0
            ), 300);

            return response()->json([
                'error' => $wasCancelled ? 'Descarga cancelada' : $e->getMessage(),
            ], $wasCancelled ? 499 : 500);
        }
    }

    public function cancelDownload(Nube $nube, Request $request)
    {
        $this->authorize('view', $nube);

        $validated = $request->validate([
            'jobId' => 'required|string',
        ]);

        Cache::put("download_cancel_{$validated['jobId']}", true, 300);
        $this->putDownloadProgress("download_{$validated['jobId']}", [
            'status' => 'cancelled',
            'message' => 'Cancelando descarga...',
            'downloaded' => 0,
            'total' => 0,
            'percent' => 0,
        ], 300);

        return response()->json(['status' => 'cancelled']);
    }

    public function getFolderSize(Nube $nube, Request $request)
    {
        $this->authorize('view', $nube);

        $validated = $request->validate([
            'path' => 'required|string',
        ]);

        $path = $this->validateRemotePath($validated['path'], true);

        try {
            $ftp = new FtpService($nube);
            $ftp->connect();
            if (! $ftp->exists($path, 'directory')) {
                throw new \RuntimeException('La carpeta remota ya no existe o no estÃ¡ disponible.');
            }
            $size = $ftp->getDirectorySize($path);
            $ftp->disconnect();
        } catch (\Exception $e) {
            return response()->json([
                'error' => $this->getHumanReadableError($e->getMessage(), $this->categorizeError($e->getMessage())),
            ], 500);
        }

        return response()->json(['size' => $size]);
    }

    public function downloadProgress(Request $request)
    {
        $validated = $request->validate([
            'jobId' => 'required|string',
        ]);

        $cacheKey = "download_{$validated['jobId']}";
        $progress = $this->getDownloadProgress($cacheKey);

        if (! $progress) {
            return response()->json(['status' => 'pending', 'message' => 'Descarga aún no inicia'], 200);
        }

        return response()->json($progress);
    }

    private function buildBreadcrumbs(string $path): array
    {
        $breadcrumbs = [];
        $parts = explode('/', trim($path, '/'));
        $current = '';

        foreach ($parts as $part) {
            if (empty($part)) {
                continue;
            }
            $current = $current ? "{$current}/{$part}" : $part;
            $breadcrumbs[] = ['name' => $part, 'path' => $current];
        }

        return $breadcrumbs;
    }

    private function loadBrowseState(Nube $nube, string $path, bool $includePermissions = true): array
    {
        $ftp = new FtpService($nube);
        $ftp->connect();

        try {
            if ($path !== '' && ! $ftp->exists($path, 'directory')) {
                throw new \RuntimeException('La carpeta remota ya no existe o no estÃ¡ disponible.');
            }

            return [
                'items' => $ftp->listFiles($path),
                'permissions' => $includePermissions ? $ftp->getPermissions($path) : null,
            ];
        } finally {
            $ftp->disconnect();
        }
    }

    private function isMissingRemoteItemError(\Throwable $e): bool
    {
        return str_contains($e->getMessage(), 'ya no existe')
            || str_contains($e->getMessage(), 'no existe')
            || str_contains($e->getMessage(), 'no estÃ¡ disponible');
    }

    private function putDownloadProgress(string $key, array $value, int $seconds): void
    {
        Cache::store('file')->put($key, $value, $seconds);
    }

    private function getDownloadProgress(string $key): mixed
    {
        return Cache::store('file')->get($key);
    }

    private function forgetDownloadProgress(string $key): void
    {
        Cache::store('file')->forget($key);
    }

    private function folderProgressState(
        string $status,
        string $message,
        float $fetchingPercent,
        float $compressingPercent,
        float $downloadingPercent,
        int|float $downloaded,
        int|float $total,
        ?string $currentFile = null,
        array $extraMetrics = []
    ): array {
        $fetchingPercent = max(0, min(100, round($fetchingPercent, 1)));
        $compressingPercent = max(0, min(100, round($compressingPercent, 1)));
        $downloadingPercent = max(0, min(100, round($downloadingPercent, 1)));

        return [
            'status' => $status,
            'message' => $message,
            'downloaded' => $downloaded,
            'total' => $total,
            'percent' => round(($fetchingPercent * 0.5) + ($compressingPercent * 0.3) + ($downloadingPercent * 0.2), 1),
            'currentFile' => $currentFile,
            'metrics' => $extraMetrics,
            'phases' => [
                'fetching' => [
                    'label' => 'Lectura/transferencia al servidor',
                    'percent' => $fetchingPercent,
                    'weight' => 50,
                ],
                'compressing' => [
                    'label' => 'Compresion ZIP en servidor',
                    'percent' => $compressingPercent,
                    'weight' => 30,
                ],
                'downloading' => [
                    'label' => 'Descarga del ZIP al cliente',
                    'percent' => $downloadingPercent,
                    'weight' => 20,
                ],
            ],
        ];
    }

    /**
     * Check if an FTP source is online
     */
    public function checkStatus(Nube $nube)
    {
        $this->authorize('view', $nube);

        $status = $nube->checkConnectionStatus();
        Cache::put($this->nubeStatusCacheKey($nube), $status['online'], 60);

        return response()->json([
            'online' => $status['online'],
            'error' => $status['error'],
            'error_type' => $status['error_type'],
        ]);
    }

    private function validateRemotePath(?string $path, bool $allowRoot = false): string
    {
        $path = trim((string) $path);

        if ($path === '' || $path === '/') {
            if ($allowRoot) {
                return '';
            }

            abort(422, 'Ruta remota inválida.');
        }

        if (str_contains($path, '\\') || str_contains($path, "\0")) {
            abort(422, 'Ruta remota inválida.');
        }

        $path = trim($path, '/');
        $segments = array_values(array_filter(explode('/', $path), fn (string $segment): bool => $segment !== ''));

        foreach ($segments as $segment) {
            $this->validateRemoteName($segment);
        }

        return implode('/', $segments);
    }

    private function validateRemoteName(string $name): string
    {
        $name = trim($name);

        if ($name === '' ||
            $name === '.' ||
            $name === '..' ||
            str_contains($name, '/') ||
            str_contains($name, '\\') ||
            str_contains($name, "\0")) {
            abort(422, 'Nombre remoto inválido.');
        }

        return $name;
    }

    private function nubeStatusCacheKey(Nube $nube): string
    {
        return "nube_status_{$nube->id}";
    }
}
