<?php

namespace App\Services;

use App\Models\Nube;
use phpseclib3\Net\SFTP;
use phpseclib3\Net\SSH2;

class FtpService
{
    protected $connId;

    protected $sftp = null;

    protected Nube $nube;

    protected ?string $password = null;

    protected string $connectionType;

    protected array $directoryProgressMetrics = [];

    protected function encodeFtpPath(string $path): string
    {
        $segments = array_map(
            static fn (string $segment): string => rawurlencode($segment),
            array_values(array_filter(explode('/', ltrim($path, '/')), static fn (string $segment): bool => $segment !== ''))
        );

        return implode('/', $segments);
    }

    public function __construct(Nube $nube)
    {
        $this->nube = $nube;
    }

    protected function getPassword(): string
    {
        if ($this->password === null) {
            $this->password = decrypt($this->nube->password);
        }

        return $this->password;
    }

    public function connect()
    {
        if ($this->nube->tipo_conexion === 'sftp') {
            return $this->connectSftp();
        }

        return $this->connectFtp();
    }

    protected function connectSftp()
    {
        $this->connectionType = 'sftp';

        try {
            $ssh = new SSH2($this->nube->host, $this->nube->puerto, $this->nube->timeout);

            if (! $ssh->login($this->nube->usuario, $this->getPassword())) {
                throw new \Exception('Credenciales SFTP incorrectas.');
            }

            $this->connId = $ssh;
            $this->sftp = new SFTP($this->nube->host, $this->nube->puerto, $this->nube->timeout);

            if (! $this->sftp->login($this->nube->usuario, $this->getPassword())) {
                throw new \Exception('Credenciales SFTP incorrectas.');
            }

            return $this;
        } catch (\Exception $e) {
            throw new \Exception('Error de conexión SFTP: '.$e->getMessage());
        }
    }

    protected function connectFtp()
    {
        $this->connectionType = 'ftp';

        if ($this->nube->tipo_conexion === 'ftps') {
            $this->connId = @ftp_ssl_connect($this->nube->host, $this->nube->puerto, $this->nube->timeout);

            if (! $this->connId) {
                throw new \Exception('No se pudo establecer conexión SSL.');
            }
        } else {
            $this->connId = @ftp_connect($this->nube->host, $this->nube->puerto, $this->nube->timeout);

            if (! $this->connId) {
                throw new \Exception('No se pudo conectar al servidor FTP.');
            }
        }

        if (! @ftp_login($this->connId, $this->nube->usuario, $this->getPassword())) {
            throw new \Exception('Credenciales FTP incorrectas.');
        }

        if ($this->nube->ssl_pasv) {
            @ftp_set_option($this->connId, FTP_USEPASVADDRESS, false);
            @ftp_pasv($this->connId, true);
        }

        return $this;
    }

    public function listFiles(string $path = '/'): array
    {
        $path = $this->normalizePath($path);

        if ($this->connectionType === 'sftp' && $this->sftp) {
            return $this->listSftpFiles($path);
        }

        return $this->listFtpFiles($path);
    }

    public function exists(string $path, ?string $type = null): bool
    {
        $path = trim($path, '/');

        if ($path === '') {
            return $type === null || $type === 'directory';
        }

        $parent = dirname($path);
        if ($parent === '.' || $parent === DIRECTORY_SEPARATOR) {
            $parent = '';
        }

        $name = basename($path);

        foreach ($this->listFiles($parent) as $item) {
            if ($item['name'] !== $name) {
                continue;
            }

            return $type === null || $item['type'] === $type;
        }

        return false;
    }

    protected function listSftpFiles(string $path): array
    {
        $items = [];

        $files = $this->sftp->nlist($path);

        if ($files === false) {
            return [];
        }

        foreach ($files as $filename) {
            if ($filename === '.' || $filename === '..') {
                continue;
            }

            $fullPath = rtrim($path, '/').'/'.$filename;
            $stat = $this->sftp->stat($fullPath);

            $isDir = $stat && ($stat['type'] === 2);
            $size = $isDir ? 0 : ($stat['size'] ?? 0);
            $modified = $stat ? date('Y-m-d H:i:s', $stat['mtime'] ?? time()) : null;
            $permissions = $stat ? $this->formatPermissions($stat['mode'] ?? 0) : '????';

            $items[] = [
                'name' => $filename,
                'type' => $isDir ? 'directory' : 'file',
                'size' => $size,
                'modified' => $modified,
                'permissions' => $permissions,
            ];
        }

        usort($items, fn ($a, $b) => ($a['type'] === 'directory' ? 0 : 1) <=> ($b['type'] === 'directory' ? 0 : 1) ?: strcasecmp($a['name'], $b['name']));

        return $items;
    }

    protected function formatPermissions(int $mode): string
    {
        $perms = '';
        $perms .= (($mode & 0x0100) ? 'r' : '-');
        $perms .= (($mode & 0x0080) ? 'w' : '-');
        $perms .= (($mode & 0x0040) ? (($mode & 0x0800) ? 's' : 'x') : (($mode & 0x0800) ? 'S' : '-'));
        $perms .= (($mode & 0x0020) ? 'r' : '-');
        $perms .= (($mode & 0x0010) ? 'w' : '-');
        $perms .= (($mode & 0x0008) ? (($mode & 0x0400) ? 's' : 'x') : (($mode & 0x0400) ? 'S' : '-'));
        $perms .= (($mode & 0x0004) ? 'r' : '-');
        $perms .= (($mode & 0x0002) ? 'w' : '-');
        $perms .= (($mode & 0x0001) ? (($mode & 0x0200) ? 't' : 'x') : (($mode & 0x0200) ? 'T' : '-'));

        return $perms;
    }

    protected function listFtpFiles(string $path): array
    {
        $rawList = @ftp_rawlist($this->connId, $path);

        if (! $rawList) {
            return [];
        }

        $items = [];

        foreach ($rawList as $line) {
            $parts = preg_split('/\s+/', $line, 9);

            if (count($parts) < 9) {
                continue;
            }

            $name = $parts[8];

            if ($name === '.' || $name === '..') {
                continue;
            }

            $isDir = str_starts_with($parts[0], 'd');
            $size = $isDir ? 0 : (int) $parts[4];
            $permissions = $parts[0];
            $modified = implode(' ', array_slice($parts, 5, 3));

            $items[] = [
                'name' => $name,
                'type' => $isDir ? 'directory' : 'file',
                'size' => $size,
                'modified' => $modified,
                'permissions' => $permissions,
            ];
        }

        usort($items, fn ($a, $b) => ($a['type'] === 'directory' ? 0 : 1) <=> ($b['type'] === 'directory' ? 0 : 1) ?: strcasecmp($a['name'], $b['name']));

        return $items;
    }

    public function createDirectory(string $path): bool
    {
        $path = $this->normalizePath($path);

        if ($this->connectionType === 'sftp' && $this->sftp) {
            return $this->sftp->mkdir($path, 0755, true);
        }

        return @ftp_mkdir($this->connId, $path) !== false;
    }

    public function deleteFile(string $path): bool
    {
        $path = $this->normalizePath($path);

        if ($this->connectionType === 'sftp' && $this->sftp) {
            return $this->sftp->delete($path);
        }

        return @ftp_delete($this->connId, $path);
    }

    public function deleteDirectory(string $path): bool
    {
        $path = $this->normalizePath($path);

        if ($this->connectionType === 'sftp' && $this->sftp) {
            return $this->deleteSftpDir($path);
        }

        return $this->deleteFtpDir($path);
    }

    protected function deleteSftpDir(string $path): bool
    {
        $files = $this->sftp->nlist($path);

        if ($files) {
            foreach ($files as $file) {
                if ($file === '.' || $file === '..') {
                    continue;
                }

                $fullPath = rtrim($path, '/').'/'.$file;
                $stat = $this->sftp->stat($fullPath);

                if ($stat && ($stat['type'] === 2)) {
                    $this->deleteSftpDir($fullPath);
                } else {
                    $this->sftp->delete($fullPath);
                }
            }
        }

        return $this->sftp->rmdir($path);
    }

    public function rename(string $oldPath, string $newPath): bool
    {
        $oldPath = $this->normalizePath($oldPath);
        $newPath = $this->normalizePath($newPath);

        if ($this->connectionType === 'sftp' && $this->sftp) {
            return $this->sftp->rename($oldPath, $newPath);
        }

        return @ftp_rename($this->connId, $oldPath, $newPath);
    }

    public function downloadFile(string $remotePath): ?string
    {
        $remotePath = $this->normalizePath($remotePath);
        $tempFile = tempnam(sys_get_temp_dir(), 'ftp_');

        if ($this->connectionType === 'sftp' && $this->sftp) {
            if ($this->sftp->get($remotePath, $tempFile)) {
                return $tempFile;
            }
        } else {
            if (@ftp_get($this->connId, $tempFile, $remotePath, FTP_BINARY)) {
                return $tempFile;
            }
        }

        @unlink($tempFile);

        return null;
    }

    public function downloadFileWithProgress(string $remotePath, ?callable $progress = null): ?string
    {
        $remotePath = $this->normalizePath($remotePath);
        $tempFile = tempnam(sys_get_temp_dir(), 'ftp_');
        $fileName = $this->getBaseName($remotePath);

        $size = $this->getFileSize($remotePath);

        if ($this->connectionType !== 'sftp' || ! $this->sftp) {
            $remotePath = $this->encodeFtpPath($remotePath);
        }

        if ($progress) {
            $progress([
                'status' => 'downloading',
                'message' => "Descargando: {$fileName}",
                'downloaded' => 0,
                'total' => $size,
                'percent' => 0,
            ]);
        }

        if ($this->connectionType === 'sftp' && $this->sftp) {
            $downloaded = 0;
            $handle = fopen($tempFile, 'wb');

            if ($handle) {
                $result = $this->sftp->get($remotePath, $handle);
                fclose($handle);

                if ($result) {
                    if ($progress) {
                        $finalSize = filesize($tempFile);
                        $progress([
                            'status' => 'downloading',
                            'message' => "Descargando: {$fileName}",
                            'downloaded' => $finalSize,
                            'total' => $finalSize,
                            'percent' => 100,
                        ]);
                    }

                    return $tempFile;
                }
            }
        } else {
            $protocol = $this->nube->tipo_conexion === 'ftps' ? 'ftps' : 'ftp';
            $url = sprintf('%s://%s:%d/%s',
                $protocol,
                $this->nube->host,
                $this->nube->puerto,
                $remotePath
            );

            $fp = fopen($tempFile, 'wb');
            if (! $fp) {
                @unlink($tempFile);

                return null;
            }

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_USERPWD, "{$this->nube->usuario}:{$this->getPassword()}");
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
            curl_setopt($ch, CURLOPT_NOPROGRESS, false);
            curl_setopt($ch, CURLOPT_BUFFERSIZE, 128 * 1024);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->nube->timeout);
            curl_setopt($ch, CURLOPT_TIMEOUT, 0);
            curl_setopt($ch, CURLOPT_TCP_KEEPALIVE, 1);

            if ($this->nube->tipo_conexion === 'ftps') {
                $verifySsl = ! app()->environment('local');
                curl_setopt($ch, CURLOPT_USE_SSL, CURLFTPSSL_ALL);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $verifySsl);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $verifySsl ? 2 : 0);
            }

            if ($this->nube->ssl_pasv) {
                if (defined('CURLOPT_FTP_SKIP_PASV_IP')) {
                    curl_setopt($ch, CURLOPT_FTP_SKIP_PASV_IP, true);
                }
                curl_setopt($ch, CURLOPT_FTP_USE_EPSV, true);
                curl_setopt($ch, CURLOPT_FTP_USE_EPRT, false);
            }

            if ($progress) {
                curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, function ($resource, $downloadSize, $downloaded) use ($progress, $fileName, $size) {
                    $total = $downloadSize > 0 ? $downloadSize : $size;
                    if ($total > 0) {
                        $progress([
                            'status' => 'downloading',
                            'message' => "Descargando: {$fileName}",
                            'downloaded' => $downloaded,
                            'total' => $total,
                            'percent' => round(($downloaded / $total) * 100, 1),
                        ]);
                    }
                });
            }

            $result = curl_exec($ch);
            $curlError = curl_error($ch);
            $curlInfo = curl_getinfo($ch);
            curl_close($ch);
            fclose($fp);

            if ($result && filesize($tempFile) > 0) {
                return $tempFile;
            }

            if (! $result) {
                throw new \Exception("Error de conexión FTP: {$curlError}");
            }

            if (filesize($tempFile) === 0) {
                @unlink($tempFile);
                throw new \Exception('El archivo descargado está vacío. Verifica la ruta y permisos en el servidor FTP.');
            }

            @unlink($tempFile);

            return null;
        }

        @unlink($tempFile);

        return null;
    }

    public function streamFileToOutput(string $remotePath, callable $write, ?callable $progress = null): void
    {
        $remotePath = $this->normalizePath($remotePath);
        $fileName = $this->getBaseName($remotePath);
        $size = $this->getFileSizeFromNormalizedPath($remotePath);
        $downloaded = 0;

        if ($progress) {
            $progress([
                'status' => 'downloading',
                'message' => "Descargando: {$fileName}",
                'downloaded' => 0,
                'total' => $size,
                'percent' => 0,
            ]);
        }

        if ($this->connectionType === 'sftp' && $this->sftp) {
            $result = $this->sftp->get($remotePath, function (string $chunk) use ($write, $progress, $fileName, $size, &$downloaded): void {
                $downloaded += strlen($chunk);
                $write($chunk);
                $this->reportStreamProgress($progress, $fileName, $downloaded, $size);
            });

            if (! $result) {
                throw new \Exception('No se pudo descargar el archivo SFTP.');
            }

            return;
        }

        $protocol = $this->nube->tipo_conexion === 'ftps' ? 'ftps' : 'ftp';
        $url = sprintf(
            '%s://%s:%d/%s',
            $protocol,
            $this->nube->host,
            $this->nube->puerto,
            $this->encodeFtpPath($remotePath)
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERPWD, "{$this->nube->usuario}:{$this->getPassword()}");
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($ch, CURLOPT_BUFFERSIZE, 1024 * 1024);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->nube->timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, 0);
        curl_setopt($ch, CURLOPT_TCP_KEEPALIVE, 1);
        curl_setopt($ch, CURLOPT_WRITEFUNCTION, function ($resource, string $chunk) use ($write, $progress, $fileName, $size, &$downloaded): int {
            $downloaded += strlen($chunk);
            $write($chunk);
            $this->reportStreamProgress($progress, $fileName, $downloaded, $size);

            return strlen($chunk);
        });

        if ($this->nube->tipo_conexion === 'ftps') {
            $verifySsl = ! app()->environment('local');
            curl_setopt($ch, CURLOPT_USE_SSL, CURLFTPSSL_ALL);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $verifySsl);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $verifySsl ? 2 : 0);
        }

        if ($this->nube->ssl_pasv) {
            if (defined('CURLOPT_FTP_SKIP_PASV_IP')) {
                curl_setopt($ch, CURLOPT_FTP_SKIP_PASV_IP, true);
            }
            curl_setopt($ch, CURLOPT_FTP_USE_EPSV, true);
            curl_setopt($ch, CURLOPT_FTP_USE_EPRT, false);
        }

        $result = curl_exec($ch);
        $curlError = curl_error($ch);
        curl_close($ch);

        if (! $result) {
            throw new \Exception("Error de conexión FTP: {$curlError}");
        }
    }

    public function getFileSize(string $remotePath): int
    {
        $remotePath = $this->normalizePath($remotePath);

        return $this->getFileSizeFromNormalizedPath($remotePath);
    }

    protected function getFileSizeFromNormalizedPath(string $remotePath): int
    {
        if ($this->connectionType === 'sftp' && $this->sftp) {
            $stat = $this->sftp->stat($remotePath);

            return $stat ? (int) ($stat['size'] ?? 0) : 0;
        }

        $size = @ftp_size($this->connId, $remotePath);

        return $size > 0 ? $size : 0;
    }

    protected function reportStreamProgress(?callable $progress, string $fileName, int $downloaded, int $total): void
    {
        if (! $progress) {
            return;
        }

        $progress([
            'status' => 'downloading',
            'message' => "Descargando: {$fileName}",
            'downloaded' => $downloaded,
            'total' => $total,
            'percent' => $total > 0 ? round(($downloaded / $total) * 100, 1) : 0,
        ]);
    }

    protected function getBaseName(string $path): string
    {
        $parts = explode('/', trim($path, '/'));

        return end($parts) ?: $path;
    }

    public function uploadFile(string $remotePath, string $localPath): bool
    {
        $remotePath = $this->normalizePath($remotePath);

        if ($this->connectionType === 'sftp' && $this->sftp) {
            return $this->sftp->put($remotePath, $localPath, SFTP::SOURCE_LOCAL_FILE);
        }

        return @ftp_put($this->connId, $remotePath, $localPath, FTP_BINARY) !== false;
    }

    public function downloadDirectory(string $remotePath, ?callable $progress = null): ?string
    {
        @set_time_limit(0);
        $this->resetDirectoryProgressMetrics();

        if (! class_exists(\ZipArchive::class)) {
            throw new \Exception('La extensión ZIP de PHP no está habilitada. Habilita extension=zip en php.ini');
        }

        $remotePath = trim($remotePath, '/');

        if ($progress) {
            $progress($this->directoryProgressState('preparing', 'Calculando tamano total...', 0, 0, 0, 0, 0));
        }

        $this->markDirectoryPhaseStart('preparing');
        $totalSize = $this->getDirectorySize($remotePath);
        $totalFiles = $this->countFiles($remotePath);
        $this->markDirectoryPhaseEnd('preparing');
        $this->directoryProgressMetrics['estimated_bytes_total'] = $totalSize;
        $this->directoryProgressMetrics['files_total'] = $totalFiles;

        if ($totalSize === 0) {
            if ($totalFiles === 0) {
                throw new \Exception('La carpeta está vacía, no hay archivos para comprimir.');
            }
            throw new \Exception('La carpeta contiene archivos de tamaño 0 bytes.');
        }

        $tempDir = sys_get_temp_dir().'/ftp_download_'.uniqid();
        mkdir($tempDir, 0755, true);

        $downloaded = 0;
        $fileCount = 0;
        $this->markDirectoryPhaseStart('fetching');
        $this->collectDirectoryFilesToTemp($remotePath, '', $tempDir, $totalSize, $downloaded, $fileCount, $progress);
        $this->markDirectoryPhaseEnd('fetching');

        if ($progress) {
            $progress($this->directoryProgressState('compressing', 'Comprimiendo archivos...', 100, 0, 0, $downloaded, $totalSize));
        }

        $this->markDirectoryPhaseStart('compressing');
        $zipPath = tempnam(sys_get_temp_dir(), 'ftp_dir_').'.zip';
        $zip = new \ZipArchive;
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            $this->deleteTempDir($tempDir);

            return null;
        }

        $this->createZipFromTempDir($tempDir, '', $zip, $fileCount, $progress);
        $this->markDirectoryPhaseStart('zip_finalize');
        if ($progress) {
            $progress($this->directoryProgressState(
                'zip_finalize',
                'Finalizando ZIP...',
                100,
                100,
                0,
                $fileCount,
                $fileCount
            ));
        }
        $zip->close();
        $this->markDirectoryPhaseEnd('zip_finalize');
        $this->markDirectoryPhaseEnd('compressing');

        $this->deleteTempDir($tempDir);

        if (file_exists($zipPath)) {
            if ($progress) {
                $zipSize = filesize($zipPath);
                $progress($this->directoryProgressState('ready_for_download', 'ZIP listo para descargar', 100, 100, 0, $zipSize, $zipSize));
            }

            return $zipPath;
        }

        return null;
    }

    protected function collectDirectoryFilesToTemp(
        string $relativeDirPath,
        string $dirPrefix,
        string $tempDir,
        int $totalSize,
        int &$downloaded,
        int &$fileCount,
        ?callable $progress
    ): void {
        $items = $this->listFiles($relativeDirPath);

        foreach ($items as $item) {
            $itemRelPath = $relativeDirPath !== '' ? "{$relativeDirPath}/{$item['name']}" : $item['name'];
            $itemTempPath = $dirPrefix.$item['name'];

            if ($item['type'] === 'directory') {
                $subDir = $tempDir.'/'.$itemTempPath;
                mkdir($subDir, 0755, true);
                $this->collectDirectoryFilesToTemp($itemRelPath, $itemTempPath.'/', $subDir, $totalSize, $downloaded, $fileCount, $progress);
            } else {
                if ($progress) {
                    $progress($this->directoryProgressState(
                        'fetching',
                        "Descargando: {$item['name']}",
                        $totalSize > 0 ? ($downloaded / $totalSize) * 100 : 0,
                        0,
                        0,
                        $downloaded,
                        $totalSize,
                        $item['name']
                    ));
                }

                $tempFile = $this->downloadFileWithProgress($itemRelPath, function (array $data) use ($progress, $item, $totalSize, &$downloaded): void {
                    if (! $progress) {
                        return;
                    }

                    $currentBytes = (int) ($data['downloaded'] ?? 0);
                    $progress($this->directoryProgressState(
                        'fetching',
                        "Descargando: {$item['name']}",
                        $totalSize > 0 ? (($downloaded + $currentBytes) / $totalSize) * 100 : 0,
                        0,
                        0,
                        $downloaded + $currentBytes,
                        $totalSize,
                        $item['name']
                    ));
                });
                if ($tempFile) {
                    $fileSize = filesize($tempFile);
                    $targetPath = $tempDir.'/'.$itemTempPath;
                    $targetDir = dirname($targetPath);
                    if (! is_dir($targetDir)) {
                        mkdir($targetDir, 0755, true);
                    }
                    rename($tempFile, $targetPath);
                    $downloaded += $fileSize;
                    $fileCount++;
                    $this->directoryProgressMetrics['bytes_read'] = $downloaded;
                    $this->directoryProgressMetrics['files_processed'] = $fileCount;

                    if ($progress) {
                        $progress($this->directoryProgressState(
                            'fetching',
                            "Descargado: {$item['name']}",
                            $totalSize > 0 ? ($downloaded / $totalSize) * 100 : 0,
                            0,
                            0,
                            $downloaded,
                            $totalSize,
                            $item['name']
                        ));
                    }
                }
            }
        }
    }

    protected function createZipFromTempDir(
        string $tempDir,
        string $zipPrefix,
        \ZipArchive $zip,
        int $totalFiles,
        ?callable $progress
    ): void {
        if ($totalFiles === 0) {
            return;
        }

        $compressed = 0;
        $this->addFilesToZip($tempDir, $zipPrefix, $zip, $totalFiles, $compressed, $progress);
    }

    protected function addFilesToZip(
        string $dir,
        string $zipPrefix,
        \ZipArchive $zip,
        int $totalFiles,
        int &$compressed,
        ?callable $progress
    ): void {
        $handle = opendir($dir);
        if (! $handle) {
            return;
        }

        while (($file = readdir($handle)) !== false) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $fullPath = $dir.'/'.$file;
            $zipPath = $zipPrefix.$file;

            if (is_dir($fullPath)) {
                $zip->addEmptyDir($zipPath.'/');
                $this->addFilesToZip($fullPath, $zipPath.'/', $zip, $totalFiles, $compressed, $progress);
            } else {
                $zip->addFile($fullPath, $zipPath);
                $compressed++;
                $this->directoryProgressMetrics['compressed_files'] = $compressed;

                if ($progress) {
                    $progress($this->directoryProgressState(
                        'compressing',
                        "Comprimiendo: {$file}",
                        100,
                        $totalFiles > 0 ? ($compressed / $totalFiles) * 100 : 0,
                        0,
                        $compressed,
                        $totalFiles,
                        $file
                    ));
                }
            }
        }

        closedir($handle);
    }

    protected function directoryProgressState(
        string $status,
        string $message,
        float $fetchingPercent,
        float $compressingPercent,
        float $downloadingPercent,
        int|float $downloaded,
        int|float $total,
        ?string $currentFile = null
    ): array {
        $fetchingPercent = max(0, min(100, round($fetchingPercent, 1)));
        $compressingPercent = max(0, min(100, round($compressingPercent, 1)));
        $downloadingPercent = max(0, min(100, round($downloadingPercent, 1)));
        $now = microtime(true);
        $lastUpdateAt = $this->directoryProgressMetrics['last_update_at'] ?? null;
        $this->directoryProgressMetrics['updates_total'] = ($this->directoryProgressMetrics['updates_total'] ?? 0) + 1;
        $this->directoryProgressMetrics['updates_by_status'][$status] = ($this->directoryProgressMetrics['updates_by_status'][$status] ?? 0) + 1;
        $this->directoryProgressMetrics['last_update_delta_ms'] = $lastUpdateAt ? round(($now - $lastUpdateAt) * 1000, 1) : null;
        $this->directoryProgressMetrics['last_update_at'] = $now;
        $this->directoryProgressMetrics['elapsed_ms'] = round(($now - ($this->directoryProgressMetrics['started_at'] ?? $now)) * 1000, 1);

        return [
            'status' => $status,
            'message' => $message,
            'downloaded' => $downloaded,
            'total' => $total,
            'percent' => round(($fetchingPercent * 0.5) + ($compressingPercent * 0.3) + ($downloadingPercent * 0.2), 1),
            'currentFile' => $currentFile,
            'metrics' => $this->directoryProgressMetricsSnapshot(),
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

    protected function resetDirectoryProgressMetrics(): void
    {
        $now = microtime(true);
        $this->directoryProgressMetrics = [
            'started_at' => $now,
            'elapsed_ms' => 0,
            'updates_total' => 0,
            'updates_by_status' => [],
            'last_update_delta_ms' => null,
            'files_total' => 0,
            'files_processed' => 0,
            'compressed_files' => 0,
            'estimated_bytes_total' => 0,
            'bytes_read' => 0,
            'phase_started_at' => [],
            'phase_finished_at' => [],
            'phase_durations_ms' => [],
        ];
    }

    protected function markDirectoryPhaseStart(string $phase): void
    {
        $this->directoryProgressMetrics['phase_started_at'][$phase] = microtime(true);
    }

    protected function markDirectoryPhaseEnd(string $phase): void
    {
        $now = microtime(true);
        $this->directoryProgressMetrics['phase_finished_at'][$phase] = $now;
        $startedAt = $this->directoryProgressMetrics['phase_started_at'][$phase] ?? null;
        if ($startedAt) {
            $this->directoryProgressMetrics['phase_durations_ms'][$phase] = round(($now - $startedAt) * 1000, 1);
        }
    }

    protected function directoryProgressMetricsSnapshot(): array
    {
        return [
            'elapsed_ms' => $this->directoryProgressMetrics['elapsed_ms'] ?? 0,
            'updates_total' => $this->directoryProgressMetrics['updates_total'] ?? 0,
            'updates_by_status' => $this->directoryProgressMetrics['updates_by_status'] ?? [],
            'last_update_delta_ms' => $this->directoryProgressMetrics['last_update_delta_ms'] ?? null,
            'files_total' => $this->directoryProgressMetrics['files_total'] ?? 0,
            'files_processed' => $this->directoryProgressMetrics['files_processed'] ?? 0,
            'compressed_files' => $this->directoryProgressMetrics['compressed_files'] ?? 0,
            'estimated_bytes_total' => $this->directoryProgressMetrics['estimated_bytes_total'] ?? 0,
            'bytes_read' => $this->directoryProgressMetrics['bytes_read'] ?? 0,
            'phase_durations_ms' => $this->directoryProgressMetrics['phase_durations_ms'] ?? [],
        ];
    }

    protected function deleteTempDir(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }

        $handle = opendir($dir);
        while (($file = readdir($handle)) !== false) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            $fullPath = $dir.'/'.$file;
            if (is_dir($fullPath)) {
                $this->deleteTempDir($fullPath);
            } else {
                @unlink($fullPath);
            }
        }
        closedir($handle);
        @rmdir($dir);
    }

    public function countFiles(string $relativeDirPath): int
    {
        $items = $this->listFiles($relativeDirPath);
        $count = 0;

        foreach ($items as $item) {
            $itemRelPath = $relativeDirPath !== '' ? "{$relativeDirPath}/{$item['name']}" : $item['name'];

            if ($item['type'] === 'directory') {
                $count += $this->countFiles($itemRelPath);
            } else {
                $count++;
            }
        }

        return $count;
    }

    public function getDirectorySize(string $relativeDirPath): int
    {
        $items = $this->listFiles($relativeDirPath);
        $totalSize = 0;

        foreach ($items as $item) {
            $itemRelPath = $relativeDirPath !== '' ? "{$relativeDirPath}/{$item['name']}" : $item['name'];

            if ($item['type'] === 'directory') {
                $totalSize += $this->getDirectorySize($itemRelPath);
            } else {
                $totalSize += $item['size'];
            }
        }

        return $totalSize;
    }

    public function getPermissions(string $path): array
    {
        $path = $this->normalizePath($path);
        $canWrite = false;
        $canDelete = false;
        $canUpload = false;

        if ($this->connectionType === 'sftp' && $this->sftp) {
            $testFile = rtrim($path, '/').'/.perm_test_'.uniqid();
            $testDir = rtrim($path, '/').'/.perm_test_dir_'.uniqid();

            if ($this->sftp->mkdir($testDir, 0755, true)) {
                $canWrite = true;
                $canDelete = $this->sftp->rmdir($testDir) || $canDelete;
            }

            $content = 'test';
            if ($this->sftp->put($testFile, $content)) {
                $canUpload = true;
                $canDelete = $this->sftp->delete($testFile) || $canDelete;
            }
        } else {
            $testDir = "{$path}/.perm_test_dir_".uniqid();
            if (@ftp_mkdir($this->connId, $testDir)) {
                $canWrite = true;
                $canDelete = @ftp_rmdir($this->connId, $testDir) || $canDelete;
            }

            $tempFile = tempnam(sys_get_temp_dir(), 'ftp_test');
            $testFile = "{$path}/.perm_test_".uniqid();
            file_put_contents($tempFile, 'test');
            if (@ftp_put($this->connId, $testFile, $tempFile, FTP_BINARY)) {
                $canUpload = true;
                $canDelete = @ftp_delete($this->connId, $testFile) || $canDelete;
            }
            @unlink($tempFile);
        }

        return [
            'can_write' => $canWrite,
            'can_delete' => $canDelete,
            'can_upload' => $canUpload,
        ];
    }

    public function disconnect(): void
    {
        if ($this->sftp) {
            $this->sftp = null;
        }
        if ($this->connId) {
            if ($this->connectionType === 'sftp') {
                // SSH connections close automatically
            } else {
                @ftp_close($this->connId);
            }
            $this->connId = null;
        }
    }

    protected function normalizePath(string $path): string
    {
        $root = rtrim($this->nube->ruta_raiz, '/');
        $path = trim($path, '/');

        if ($path === '') {
            return $root ?: '/';
        }

        return $root ? "{$root}/{$path}" : "/{$path}";
    }

    protected function deleteFtpDir(string $path): bool
    {
        $items = @ftp_rawlist($this->connId, $path);

        if ($items) {
            foreach ($items as $item) {
                $parts = preg_split('/\s+/', $item, 9);
                if (count($parts) < 9) {
                    continue;
                }

                $name = $parts[8];
                if ($name === '.' || $name === '..') {
                    continue;
                }

                $fullPath = "{$path}/{$name}";
                $isDir = str_starts_with($parts[0], 'd');

                if ($isDir) {
                    $this->deleteFtpDir($fullPath);
                } else {
                    @ftp_delete($this->connId, $fullPath);
                }
            }
        }

        return @ftp_rmdir($this->connId, $path) !== false;
    }
}
