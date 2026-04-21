---
name: laravel-blade-conventions
description: Usa esta skill cuando trabajes en una aplicación Laravel renderizada en servidor con Blade. Aplica convenciones limpias para controladores, rutas, validación, vistas, componentes Blade y organización general del proyecto sin convertirlo en SPA.
---

# Laravel Blade Conventions

## Objetivo
Mantener una base Laravel tradicional, clara y mantenible usando:
- Blade
- controladores REST
- Form Requests
- componentes/partials Blade
- validación en servidor
- JavaScript mínimo y progresivo

## Principios
- Preferir renderizado del lado servidor con Blade.
- No introducir React, Vue, Inertia, Livewire, Alpine u otros frameworks frontend, salvo que se pida explícitamente.
- Mantener las vistas simples: la lógica de negocio no pertenece a Blade.
- Reutilizar UI con componentes Blade o partials.
- Seguir convenciones estándar de Laravel antes de inventar estructuras personalizadas.
- Hacer cambios pequeños y consistentes con el estilo existente del proyecto.

## Convenciones de arquitectura
- Usar rutas REST cuando aplique:
  - `index`
  - `create`
  - `store`
  - `show`
  - `edit`
  - `update`
  - `destroy`
- Mantener los controladores delgados.
- Mover validación a `FormRequest` cuando no sea algo trivial.
- Mover lógica repetida o compleja fuera del controlador cuando empiece a crecer.
- No meter consultas Eloquent complejas directamente en Blade.
- No duplicar validación entre controlador y vista.

## Convenciones para vistas Blade
- Cada vista debe tener una responsabilidad clara.
- Usar layouts para estructura global.
- Extraer bloques repetidos a:
  - componentes Blade
  - includes
  - partials
- Mantener nombres de vistas predecibles, por ejemplo:
  - `users/index.blade.php`
  - `users/create.blade.php`
  - `users/edit.blade.php`
  - `users/partials/form.blade.php`
- Formularios largos deben reutilizar un partial compartido.
- Mostrar errores de validación de forma consistente.
- Mostrar mensajes flash de éxito y error de forma consistente.
- Escapar salida por defecto con `{{ }}`.
- Usar `{!! !!}` solo si está justificado y es seguro.

## Formularios
- Preferir `FormRequest` para `store` y `update`.
- Conservar valores anteriores con `old()`.
- Mostrar errores por campo debajo del input.
- Incluir protección CSRF en todos los formularios POST, PUT, PATCH y DELETE.
- Usar métodos HTTP simulados con `@method()` cuando corresponda.
- Mantener nombres de campos alineados con las columnas o DTO esperados.
- No mezclar demasiada lógica condicional en el formulario.

## Componentes e includes
Usar:
- componentes Blade para UI reutilizable y encapsulada
- partials para fragmentos simples de vistas
- includes cuando el bloque sea solo presentación y muy acotado

Ejemplos de bloques que conviene extraer:
- alertas flash
- tabla reutilizable
- formulario compartido create/edit
- breadcrumbs
- modal simple
- paginación y filtros

## Rutas
- Agrupar rutas por recurso y prefijo lógico.
- Usar nombres de rutas consistentes:
  - `users.index`
  - `users.create`
  - `users.store`
  - `users.edit`
  - `users.update`
  - `users.destroy`
- Aplicar middleware en grupos cuando sea posible.
- No declarar rutas duplicadas o ambiguas.

## Controladores
- Cada método debe hacer una sola cosa principal.
- El controlador coordina; no concentra lógica pesada.
- Usar route model binding cuando ayude a claridad.
- Redirigir a rutas con nombre.
- Después de acciones mutables, usar redirect con mensaje flash.

## Base de datos y Eloquent
- Evitar N+1 usando eager loading cuando corresponda.
- Seleccionar solo relaciones necesarias.
- Mantener consultas legibles.
- No optimizar prematuramente, pero tampoco cargar relaciones innecesarias.
- Si una consulta es compleja y reutilizada, considerar moverla a un scope o clase dedicada.

## Estilo de implementación
Cuando generes código:
1. revisa la estructura existente
2. sigue nombres y patrones ya usados
3. haz el cambio mínimo necesario
4. deja el resultado listo para copiar/usar
5. no introduzcas frameworks frontend

## Qué evitar
- convertir una pantalla CRUD simple en arquitectura compleja
- meter lógica de negocio en Blade
- duplicar markup grande en varias vistas
- usar JavaScript para resolver algo que Blade y HTML resuelven mejor
- cambiar la organización del proyecto sin motivo

## Resultado esperado
El resultado debe sentirse como una app Laravel tradicional bien hecha:
- simple
- predecible
- mantenible
- fácil de depurar
- fácil de extender
