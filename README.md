# Nube — FTP Cloud Manager

Aplicación web Laravel para administrar fuentes FTP, FTPS y SFTP desde una interfaz Blade. Permite registrar conexiones remotas, navegar carpetas, crear directorios, subir archivos, descargar archivos o carpetas comprimidas en ZIP, renombrar y eliminar elementos según permisos del servidor remoto.

## Objetivo del proyecto

Este proyecto centraliza el acceso a servidores FTP/SFTP/FTPS desde una aplicación web con autenticación y roles. La app está pensada para operar como un administrador de archivos remoto con una UI simple, renderizada del lado servidor, sin convertir Laravel en una SPA.

## Stack técnico

- PHP `^8.2`
- Laravel `12.x`
- MySQL `8.0` en Docker
- Blade como motor de vistas
- JavaScript vanilla para interacciones progresivas
- Vite `7.x`
- Tailwind CSS `4.x`
- Axios
- phpseclib `3.x` para SFTP
- Nginx + PHP-FPM vía Docker
- PHPUnit `11.x`
- Laravel Pint para formato PHP

## Funcionalidades principales

- Autenticación de usuarios.
- Roles de usuario:
  - `admin`: puede gestionar usuarios y fuentes FTP.
  - `user`: acceso limitado según permisos definidos en la aplicación y el servidor remoto.
- CRUD de fuentes FTP/SFTP/FTPS llamadas `nubes`.
- Navegador de archivos remoto.
- Subida de archivos con barra de progreso visual.
- Descarga directa de archivos.
- Descarga de carpetas como ZIP.
- Barra de progreso para descargas.
- Creación de carpetas remotas.
- Renombrado de archivos/carpetas.
- Eliminación de archivos/carpetas.
- Verificación de conexión y manejo de errores FTP legibles.
- Límites de subida configurados para archivos grandes.

## Estructura relevante

```text
app/
  Http/Controllers/NubeController.php      # Controlador principal de fuentes y operaciones FTP
  Http/Controllers/UserController.php      # Gestión de usuarios
  Http/Middleware/EnsureDatabaseConnection.php
  Models/Nube.php                          # Modelo de fuente FTP/SFTP/FTPS
  Models/User.php                          # Usuario con rol admin/user
  Services/FtpService.php                  # Operaciones FTP, FTPS y SFTP
  Traits/CategorizesFtpErrors.php          # Clasificación de errores FTP

resources/views/
  nubes/                                   # Vistas de fuentes y navegador remoto
  users/                                   # Vistas de usuarios
  layouts/                                 # Layout Blade principal

routes/
  user.php                                 # Rutas reales de la aplicación
  web.php                                  # Redirección mínima

docker/
  nginx/default.conf                       # Configuración Nginx
  php/uploads.ini                          # Límites PHP para uploads

database/
  migrations/                              # Migraciones de Laravel
  seeders/                                 # Seeders, si aplica
```

## Requisitos

### Opción recomendada: Docker

Necesitas:

- Docker Desktop o Docker Engine
- Docker Compose
- Git

No necesitas instalar PHP, Composer, Node ni MySQL en tu máquina si usas Docker.

### Opción local sin Docker

Necesitas:

- PHP `8.2` o superior
- Composer `2.x`
- Node.js `20.x` recomendado
- npm
- MySQL `8.0` o compatible
- Extensiones PHP habituales de Laravel:
  - `openssl`
  - `pdo`
  - `pdo_mysql`
  - `mbstring`
  - `tokenizer`
  - `xml`
  - `ctype`
  - `json`
  - `fileinfo`
  - `curl`
  - `zip`

Para SFTP se usa `phpseclib`, por lo que no se depende de la extensión `ssh2`.

## Variables de entorno

Copia el archivo base:

```bash
cp .env.example .env
```

Valores típicos usando Docker:

```env
APP_NAME=Nube
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8080

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=miapp
DB_USERNAME=miapp
DB_PASSWORD=secret

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database
```

> Importante: `.env` no se versiona. Cada entorno debe tener su propio archivo.

## Inicialización con Docker

Desde la raíz del proyecto:

```bash
docker compose up -d --build
```

Instalar dependencias PHP:

```bash
docker compose run --rm composer install
```

Instalar dependencias frontend y levantar Vite:

```bash
docker compose up -d node
```

Generar clave de aplicación:

```bash
docker exec nube-app php artisan key:generate
```

Ejecutar migraciones:

```bash
docker exec nube-app php artisan migrate
```

Abrir la aplicación:

```text
http://localhost:8080
```

## Inicialización local sin Docker

Instalar dependencias PHP:

```bash
composer install
```

Instalar dependencias frontend:

```bash
npm install
```

Crear `.env` y generar clave:

```bash
cp .env.example .env
php artisan key:generate
```

Configurar la conexión a base de datos en `.env` y luego ejecutar:

```bash
php artisan migrate
```

Levantar servidor Laravel y Vite:

```bash
composer run dev
```

O por separado:

```bash
php artisan serve
npm run dev
```

## Comandos útiles

```bash
composer run dev      # Laravel server + queue listener + Vite
npm run dev           # Solo Vite
npm run build         # Compila assets para producción
composer run setup    # Instala dependencias, crea .env, genera key, migra y compila
composer run test     # Limpia config cache y ejecuta tests
vendor/bin/pint       # Formatea PHP
```

## Testing

Ejecutar toda la suite:

```bash
composer run test
```

Ejecutar un test específico:

```bash
php artisan test --filter=NombreDelTest
```

Ejecutar un archivo de test:

```bash
php artisan test tests/Feature/ExampleTest.php
```

El entorno de testing usa configuración Laravel orientada a pruebas: SQLite en memoria, queue síncrona y cache/mail/session de prueba cuando corresponde.

## Subidas de archivos

La aplicación tiene límites de subida configurados para archivos grandes:

- Nginx: `client_max_body_size 512m`
- PHP: `upload_max_filesize=512M`
- PHP: `post_max_size=512M`
- PHP: `memory_limit=512M`

Archivos relevantes:

```text
docker/nginx/default.conf
docker/php/uploads.ini
docker-compose.yml
```

Si cambias estos archivos en Docker, recrea los contenedores `app` y `web`:

```bash
docker compose up -d --force-recreate app web
```

## Descargas

Las descargas son síncronas y no requieren queue worker para completarse.

Rutas relevantes:

```text
POST /nubes/{nube}/download-sync
GET  /download-progress?jobId=...
POST /nubes/{nube}/download-cancel
```

La descarga de carpetas genera un ZIP temporal y reporta fases de progreso.

## FTP, FTPS y SFTP

La lógica FTP está centralizada en:

```text
app/Services/FtpService.php
```

Responsabilidades principales:

- Conectar según tipo de fuente.
- Listar archivos remotos.
- Validar existencia de rutas.
- Subir archivos.
- Descargar archivos.
- Crear carpetas.
- Renombrar elementos.
- Eliminar elementos.
- Calcular tamaño de carpetas bajo demanda.
- Determinar permisos reales del servidor remoto.

## Seguridad y consideraciones

- Las contraseñas de fuentes remotas se cifran usando `APP_KEY`.
- Si cambia `APP_KEY`, las contraseñas cifradas existentes pueden dejar de desencriptarse.
- No versionar `.env`, dumps de base de datos, logs, `vendor`, `node_modules`, builds ni memoria local de herramientas.
- Las acciones mutables deben validar permisos tanto en Laravel como contra el servidor FTP.
- El servidor FTP es estado externo: un archivo puede desaparecer entre renderizar la vista y ejecutar una acción. Por eso el backend revalida existencia antes de operar.

## Flujo recomendado para Git

Inicializar repositorio:

```bash
git init
git add .
git commit -m "chore: initial project setup"
```

Conectar con remoto:

```bash
git remote add origin <URL_DEL_REPOSITORIO>
git branch -M main
git push -u origin main
```

Antes de subir a un remoto, revisa SIEMPRE:

```bash
git status
git diff --cached --stat
```

## Archivos que no deben entrar al repositorio

El proyecto ignora, entre otros:

- `.env`
- `vendor/`
- `node_modules/`
- `public/build/`
- `public/hot`
- logs
- caches de PHPUnit
- carpetas temporales locales
- memoria local de Engram
- configuración local del editor

## Convenciones del proyecto

- Laravel tradicional con Blade.
- JavaScript vanilla como mejora progresiva.
- Evitar frameworks frontend innecesarios.
- Validación principal en backend.
- Controladores coordinan; lógica FTP vive en `FtpService`.
- No ejecutar build automáticamente después de cambios de código durante mantenimiento asistido.
- Commits con Conventional Commits.

Ejemplos:

```bash
git commit -m "feat: add upload progress indicator"
git commit -m "fix: handle ftp upload errors as json"
git commit -m "docs: document project setup"
```

## Troubleshooting

### Error 413 al subir archivos

Verifica:

- `docker/nginx/default.conf`
- `docker/php/uploads.ini`
- Que los contenedores `app` y `web` hayan sido recreados.

```bash
docker compose up -d --force-recreate app web
```

### Error de contraseña corrupta

Puede ocurrir si cambió `APP_KEY`. Actualiza la contraseña de la fuente remota desde la UI.

### El listado FTP tarda demasiado

Causas comunes:

- Servidor FTP lento.
- Conexión pasiva/activa mal configurada.
- Permisos remotos costosos de verificar.
- Carpetas con muchos elementos.

### Los estilos no se actualizan

En desarrollo asegúrate de tener Vite activo:

```bash
npm run dev
```

En producción compila assets manualmente:

```bash
npm run build
```

## Licencia

Este proyecto conserva licencia MIT por herencia del esqueleto Laravel, salvo que el propietario del repositorio defina otra licencia explícita.
