# FTP Cloud Manager (Nube)

Sistema web para gestionar fuentes de datos FTP/SFTP/FTPS con navegador de archivos integrado.

## Descripción

Aplicación Laravel 12 que permite administrar múltiples conexiones FTP, navegar archivos, subir/descargar contenido y gestionar usuarios.

## Funcionalidades

- **Gestión de Fuentes FTP**: Crear, editar, eliminar conexiones FTP/SFTP/FTPS
- **Navegador de Archivos**: Explorar directorios, crear carpetas, renombrar, eliminar
- **Transferencia de Archivos**: Subir y descargar archivos/carpetas
- **Monitoreo de Estado**: Verificación automática de conexión con mensajes de error descriptivos
- **Sistema de Usuarios**: Roles admin/user, autenticación, gestión de perfiles
- **Descarga Síncrona**: Descarga directa sin necesidad de queue worker

## Stack Tecnológico

- **Backend**: Laravel 12, PHP 8.2+
- **Frontend**: Blade, Tailwind CSS 4, Vanilla JS
- **Conexiones FTP**: phpseclib3 (SFTP), ftp_connect nativo (FTP/FTPS)
- **Base de Datos**: MySQL/SQLite
- **Cola**: Database (opcional, solo para descargas muy grandes)

## Problemas Arreglados

### 1. Barra de navegación al fondo
**Problema**: La barra de navegación aparecía al fondo de la página en `nubes/index.blade.php`.

**Causa**: La sección `@section('scripts')` estaba dentro de `@section('content')`, causando que Blade procesara mal el orden de renderizado.

**Solución**: Mover `@section('scripts')` fuera de `@section('content')`, colocándolo al mismo nivel jerárquico.

### 2. Verificación de estado incorrecta
**Problema**: Las fuentes de datos mostraban "Desconectado" aunque estaban en línea.

**Causa raíz**: Error de encriptación "The MAC is invalid" porque:
- La extensión `ssh2` de PHP no estaba instalada
- La contraseña encriptada en la base de datos usaba una clave de encriptación anterior

**Solución**: 
- Instalar `phpseclib/phpseclib` para conexiones SFTP sin necesidad de extensión SSH2
- Regenerar `APP_KEY` en `.env`
- Actualizar la contraseña de la fuente de datos con la nueva clave

### 3. Mensajes de error poco claros
**Problema**: Los errores de conexión mostraban mensajes técnicos como "The MAC is invalid".

**Solución**: Implementar categorización de errores con mensajes amigables:
- `auth`: Credenciales incorrectas
- `network`: No se puede conectar al servidor
- `ssl`: Error de conexión segura
- `config`: Contraseña corrupta
- `permission`: Permiso denegado

### 4. Navegación desconectada en página de browse
**Problema**: Si la conexión se perdía mientras se navegaba, no había indicación visual.

**Solución**: 
- Verificación periódica de conexión cada 30 segundos
- Overlay modal cuando se detecta pérdida de conexión
- Botones para volver a Fuentes o reintentar

### 5. Descarga atascada en 0%
**Problema**: Al descargar archivos/carpetas, el modal mostraba "0%" indefinidamente.

**Causa**: El sistema usaba Jobs en cola (`queue:work`) que no estaba corriendo.

**Solución**: Implementar descarga síncrona directa que no requiere queue worker.

### 6. Error JavaScript "fileName is not defined"
**Problema**: Error de scope en Promise chain al descargar.

**Solución**: Restructurar el código para pasar el nombre del archivo correctamente entre `.then()` callbacks usando un objeto `{blob, fileName}`.

## Estado Actual

### Funcionalidades Completadas
- ✅ Navegación de archivos
- ✅ Crear/eliminar carpetas
- ✅ Subir archivos
- ✅ Descargar archivos (síncrono)
- ✅ Descargar carpetas como ZIP (síncrono)
- ✅ Renombrar archivos/carpetas
- ✅ Verificación de estado con mensajes de error claros
- ✅ Detección de pérdida de conexión
- ✅ Conexiones FTP/SFTP/FTPS usando phpseclib

### Pendiente por Arreglar
- (Ninguno)

### Pendiente por Arreglar (Resuelto en última limpieza)
- ✅ Código de Jobs obsoletos eliminado (DownloadDirectoryJob, DownloadFileJob)
- ✅ Rutas y código de cola obsoleto removido (download-stream, download-progress, download-file)
- ✅ Error de sintaxis JavaScript corregido en browse.blade.php (faltaba `})` en fetch)

### Para Iniciar el Proyecto

```bash
# Instalar dependencias
composer install
npm install

# Copiar .env y configurar
cp .env.example .env
php artisan key:generate

# Migrar base de datos
php artisan migrate

# Construir assets
npm run build

# Iniciar servidor de desarrollo
composer run dev
# O manualmente:
# php artisan serve
# npm run dev
```

### Comandos Útiles

```bash
# Servidor + Queue + Vite
composer run dev

# Solo Vite
npm run dev

# Build producción
npm run build

# Limpiar cachés
php artisan optimize:clear

# Tests
composer run test

# Formatear código PHP
vendor/bin/pint
```

## Notas Importantes

- La contraseña de las fuentes FTP se encripta con `APP_KEY`. Si regeneras la key, las contraseñas existentes dejarán de funcionar.
- Para conexiones SFTP se usa `phpseclib3` (no requiere extensión ssh2 de PHP).
- Las descargas usan método síncrono para evitar dependencia de queue worker.
- El intervalo de verificación de estado es de 30 segundos.
