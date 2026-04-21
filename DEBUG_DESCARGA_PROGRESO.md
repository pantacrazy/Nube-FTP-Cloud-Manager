# Debug de barra de progreso de descarga

Fecha: 2026-04-12

## Problema reportado

- La descarga de archivos y carpetas funciona, pero la barra de progreso no refleja el progreso real.
- En algunos intentos hubo errores `500`.
- En carpetas, el flujo esperado es:
  1. Descargar cada archivo del FTP al servidor.
  2. Comprimir todo con el nombre de la carpeta.
  3. Descargar el ZIP al cliente.
- En archivos, el flujo esperado es:
  1. Descargar el archivo al servidor.
  2. Descargarlo al cliente.

## Hallazgos confirmados

### 1. El backend sí genera progreso

Se confirmó en logs previos que `FtpService` iba escribiendo estados como:

- `calculating`
- `downloading`
- `compressing`
- `complete`

Y también porcentajes como:

- `10.4`
- `50`
- `58.3`
- `66.7`
- `75`
- `91.7`
- `100`

Conclusión:

- El problema principal no era que el backend no calculara progreso.
- El problema estaba en que el frontend no lograba leerlo o reflejarlo de forma consistente.

### 2. Había un error con rutas FTP que contenían caracteres especiales

Error visto:

- `Error de conexión FTP: URL rejected: Malformed input to a URL function`

Causa:

- La URL FTP se estaba construyendo con el path remoto sin codificar.

Corrección aplicada:

- En `app/Services/FtpService.php` se agregó `encodeFtpPath()` usando `rawurlencode()` por segmento.

Resultado:

- La descarga volvió a funcionar para nombres con espacios, tildes y Unicode.

### 3. Hubo un error de sesión en el polling

Error visto en logs:

- `Session store not set on request.`

Causa detectada:

- El endpoint `/download-progress` estaba chocando con middleware de sesión/CSRF.

Cambios aplicados:

- Se movió la ruta de `/download-progress` fuera del grupo `web`.
- Se registró en `bootstrap/app.php`.
- Se dejó con middleware `api`.

Observación:

- Aun con ese cambio, hubo evidencia de que el servidor activo seguía usando definición vieja en algunos intentos.
- Se recomendó reiniciar el servidor Laravel para forzar recarga de rutas.

### 4. Hubo timeout durante compresión/flujo largo

Error visto:

- `Maximum execution time of 60 seconds exceeded`

Ubicación reportada:

- `app/Services/FtpService.php`

Corrección aplicada:

- `@set_time_limit(0);` en:
  - `app/Http/Controllers/NubeController.php` dentro de `downloadSync()`
  - `app/Services/FtpService.php` dentro de `downloadDirectory()`

Resultado:

- Se evitó el fallo por límite de 60 segundos en procesos largos.

## Cambios aplicados

### app/Services/FtpService.php

- Se agregó codificación segura del path FTP.
- Se eliminó el límite de tiempo en `downloadDirectory()`.

### app/Http/Controllers/NubeController.php

- Se usa `jobId` como clave de progreso.
- Se quitó dependencia del usuario para la clave de progreso.
- Se eliminó el límite de tiempo en `downloadSync()`.
- Se cambió el almacenamiento de progreso a `Cache::store('file')`.

### bootstrap/app.php

- Se registró `/download-progress` como ruta separada.
- Se dejó bajo middleware `api`.

### routes/user.php

- Se retiró la definición previa de `/download-progress` del grupo de rutas de usuario.

### resources/views/nubes/browse.blade.php

- Se agregó debug visible del polling.
- Se validó `content-type`.
- Se mostraron errores de polling en consola y en el estado del modal.
- Se ajustó la representación visual de la fase `compressing`.

## Estado actual

- La descarga funciona.
- La barra sigue sin reflejar el progreso real.
- No hay confirmación todavía de si el problema actual es:
  - el polling devuelve siempre `pending`,
  - el polling sí devuelve progreso pero la UI no lo pinta bien,
  - o el store de caché no está persistiendo como se espera entre requests en el entorno real.

## Hipótesis más probables para continuar mañana

### Hipótesis A: el polling sí llega pero devuelve siempre `pending`

Qué verificar:

- Instrumentar `downloadProgress()` con logs temporales para registrar:
  - `jobId`
  - `cacheKey`
  - valor leído desde `Cache::store('file')->get(...)`

### Hipótesis B: `downloadSync()` escribe en un store y `downloadProgress()` lee vacío

Qué verificar:

- Confirmar físicamente si se crean entradas/archivos del cache file durante una descarga.
- Confirmar que ambos requests usan el mismo entorno/configuración de cache.

### Hipótesis C: el frontend recibe updates pero otra lógica los pisa

Qué verificar:

- Registrar en consola cada `data` recibido desde `/download-progress`.
- Confirmar si `xhr.progress` o `load` están sobrescribiendo la UI antes de tiempo.

## Siguientes pasos recomendados

1. Reiniciar el servidor Laravel antes de seguir probando.
2. Agregar logs temporales dentro de `downloadProgress()` para ver si realmente encuentra el progreso.
3. Agregar logs temporales dentro de `downloadSync()` al escribir progreso para comparar escritura vs lectura.
4. Si el cache file no funciona de forma confiable, considerar persistir el progreso en:
   - una tabla DB simple,
   - o un archivo JSON temporal por `jobId`.

## Archivos tocados durante esta sesión

- `app/Services/FtpService.php`
- `app/Http/Controllers/NubeController.php`
- `bootstrap/app.php`
- `routes/user.php`
- `resources/views/nubes/browse.blade.php`

## Nota

El punto más importante para mañana es no seguir cambiando a ciegas. Ya se avanzó bastante en aislar errores de:

- URL FTP malformada
- sesión/CSRF
- timeout de 60 segundos

El siguiente paso correcto es instrumentar lectura y escritura del progreso para confirmar dónde se rompe exactamente.
