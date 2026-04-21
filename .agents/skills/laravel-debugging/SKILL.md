---
name: laravel-debugging
description: Usa esta skill para diagnosticar y corregir errores en proyectos Laravel. Sigue un proceso de depuración ordenado, empezando por la causa más probable, evitando cambios innecesarios y proponiendo arreglos mínimos verificables.
---

# Laravel Debugging

## Objetivo
Diagnosticar problemas Laravel con un enfoque ordenado:
- identificar causa probable
- verificar antes de cambiar demasiado
- aplicar el arreglo mínimo efectivo
- dejar pasos claros para confirmar la solución

## Mentalidad
- No adivinar si se puede verificar.
- No proponer refactor grande cuando el problema parece local.
- Empezar por lo más probable y barato de comprobar.
- Separar síntomas de causa raíz.
- Preferir cambios pequeños y reversibles.

## Flujo de depuración
Cuando analices un problema, sigue este orden:

1. definir el síntoma exacto
2. identificar dónde ocurre
3. revisar logs y mensajes reales
4. listar causas probables en orden
5. proponer verificación concreta por cada hipótesis
6. aplicar el fix más pequeño posible
7. indicar cómo validar el arreglo

## Categorías comunes a revisar

### 1. Rutas
Verificar:
- la ruta existe
- el método HTTP coincide
- el nombre de ruta es correcto
- el prefijo y middleware del grupo no rompen acceso
- no hay conflicto entre rutas similares
- route model binding está resolviendo bien

Comandos útiles a sugerir:
- `php artisan route:list`

### 2. Controladores y requests
Verificar:
- namespace correcto
- método correcto
- importaciones correctas
- `FormRequest` aplicado
- validación no está bloqueando el flujo
- redirect y response esperados

### 3. CSRF, sesión y autenticación
Verificar:
- formulario incluye `@csrf`
- dominio/sesión/configuración de cookies
- middleware `web` está presente
- usuario autenticado realmente existe en la sesión
- guards correctos
- expiración de sesión

### 4. Blade y vistas
Verificar:
- nombre de vista correcto
- variables pasadas realmente existen
- nulls no controlados
- componentes/includes existen
- errores de sintaxis Blade
- datos de formulario usando `old()` y errores correctamente

### 5. Base de datos y Eloquent
Verificar:
- migraciones aplicadas
- columnas y tipos correctos
- fillable/guarded
- relaciones correctas
- eager loading necesario
- consultas retornan lo esperado
- scopes o accessors no alteran resultados inesperadamente

### 6. Config, cache y entorno
Verificar:
- `.env` correcto
- config cache desactualizado
- route cache desactualizado
- view cache corrupto o viejo
- credenciales correctas
- driver de sesión, cola o cache correcto

Comandos útiles a considerar:
- `php artisan optimize:clear`
- `php artisan config:clear`
- `php artisan route:clear`
- `php artisan view:clear`
- `php artisan cache:clear`

### 7. Assets y frontend simple
Verificar:
- archivos cargan realmente
- errores en consola del navegador
- rutas de assets correctas
- listeners se montan después de existir el DOM
- la interacción frontend no rompe el submit o navegación normal

### 8. Autorización
Verificar:
- middleware auth
- policies/gates
- permisos/roles
- condiciones de ownership
- diferencia entre 403, 404 y redirect por auth

## Cómo responder al depurar
Al ayudar con un bug, estructurar así:

### Síntoma
Describir qué está fallando exactamente.

### Causas probables
Listar de la más probable a la menos probable.

### Qué revisar primero
Dar 2 o 3 verificaciones concretas y rápidas.

### Fix mínimo propuesto
Mostrar el cambio más pequeño razonable.

### Cómo comprobarlo
Indicar cómo validar si quedó resuelto.

## Reglas de calidad
- Pedir o usar el mensaje de error exacto si existe.
- Si hay stack trace, basarse en él.
- Si falta contexto, inferir con cautela y decir qué parte es hipótesis.
- No mezclar demasiadas soluciones a la vez.
- No recomendar limpiar caché como respuesta automática a todo; usarlo cuando tenga sentido.
- Si el error parece del framework, primero descartar problema del código o configuración.

## Casos típicos
### 419 Page Expired
Revisar:
- `@csrf`
- sesión
- dominio/cookies
- middleware web
- configuración de APP_URL y sesión

### 404 en ruta
Revisar:
- route:list
- prefijo
- método HTTP
- nombre de ruta
- parámetros requeridos

### 403 Unauthorized
Revisar:
- auth
- policy
- gate
- permisos
- ownership del recurso

### Variable undefined en Blade
Revisar:
- controlador
- `compact()` o `with()`
- nombre exacto
- componente/include
- condición `@isset` o null-safe si aplica

### Datos no guardan
Revisar:
- validación
- fillable
- cast
- nombre de campos
- transacción fallida
- excepción en logs

### Relación devuelve vacío
Revisar:
- foreign key
- nombre de relación
- datos reales en DB
- claves locales y foráneas
- scopes globales

## Qué evitar
- refactorizar media aplicación para arreglar un bug local
- sugerir patrones nuevos sin relación con la falla
- culpar al framework sin evidencia
- dar diez hipótesis sin priorización
- cambiar backend y frontend a la vez sin motivo

## Resultado esperado
La ayuda debe producir:
- diagnóstico claro
- pasos concretos
- fix pequeño
- validación simple
- menor riesgo de romper otras partes
