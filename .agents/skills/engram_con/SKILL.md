---
name: engram-memory
description: Usa esta skill cuando necesites guardar, recuperar o consolidar memoria del proyecto con Engram en un entorno OpenCode. Pensada para proyectos Laravel con Blade y JavaScript vanilla, usando Engram como memoria persistente local vía MCP, CLI o REST API.
---

# Engram Memory

## Objetivo
Usar Engram como capa de memoria persistente del proyecto para:
- recordar decisiones técnicas
- recuperar contexto entre sesiones
- guardar convenciones del proyecto
- registrar bugs, fixes y hallazgos
- evitar reexplicar el mismo contexto repetidamente

Esta skill está pensada para trabajar en proyectos Laravel renderizados con Blade y frontend sin framework.

## Cuándo usar esta skill
Usa esta skill cuando ocurra cualquiera de estas situaciones:
- se tomó una decisión arquitectónica que conviene recordar
- se descubrió una causa raíz importante de un bug
- el proyecto tiene convenciones propias que no deben perderse
- hay contexto útil de negocio o producto que impacta futuras respuestas
- el usuario menciona algo como “recuerda esto”, “ten esto en cuenta”, “esto ya lo resolvimos”, “usa la misma convención”, o “no repitas el error anterior”
- necesitas consultar memoria previa antes de proponer cambios

## Qué es Engram en este flujo
Engram es una capa de memoria para agentes con:
- servidor MCP
- REST API
- SDK de TypeScript
- almacenamiento local con SQLite

La documentación pública describe comandos como `engram init`, `engram mcp`, y el servidor local en `127.0.0.1:3800` aunque actualmente cuando se inicia se pone a funcionar en http://127.0.0.1:49857, además de operaciones de “remember”, “recall” y “consolidate”. Si la instalación del entorno ya está hecha, asumir que Engram puede estar disponible por MCP, CLI o REST. No asumir nube obligatoria. Priorizar uso local.  

## Estrategia de uso
Siempre seguir este orden mental:

1. **recuperar antes de escribir**
   - Antes de dar una recomendación importante, intentar recordar si ya existe contexto relacionado.

2. **guardar solo lo que aporta valor futuro**
   - No guardar ruido, texto duplicado ni detalles pasajeros.
   - Guardar decisiones, patrones, restricciones, fixes confirmados y preferencias duraderas del proyecto.

3. **preferir memorias concretas y reutilizables**
   - Una memoria útil debe poder ayudar en una sesión futura.
   - Redactar memorias breves, específicas y accionables.

4. **consolidar cuando haya aprendizaje acumulado**
   - Si hay varias memorias relacionadas sobre el mismo problema o módulo, considerar consolidación.

## Qué guardar en Engram
Guardar memorias como:
- decisiones de arquitectura
- convenciones del proyecto
- reglas de validación repetidas
- causas raíz de bugs
- soluciones verificadas
- restricciones técnicas
- preferencias del usuario para este proyecto
- estructura de carpetas o patrones internos no obvios
- acuerdos sobre estilo de Blade, rutas, controladores y frontend vanilla

### Ejemplos de memorias útiles
- “El proyecto usa Blade tradicional; no introducir React, Vue, Livewire ni Inertia salvo petición explícita.”
- “Las validaciones complejas van en Form Requests, no inline en el controlador.”
- “Los formularios create/edit reutilizan `resources/views/users/partials/form.blade.php`.”
- “Se detectó que el error 419 provenía de middleware y sesión, no del token Blade.”
- “El usuario quiere cambios mínimos, no refactors grandes.”
- “La UI debe mantenerse simple y compatible con Laravel + JS vanilla.”

## Qué NO guardar
No guardar:
- texto temporal sin valor futuro
- errores no confirmados como si fueran hechos
- hipótesis sin verificar
- logs completos innecesarios
- respuestas enteras del asistente
- datos sensibles
- secretos, tokens, API keys, contraseñas
- contenido privado que no sea necesario para el trabajo técnico

## Flujo recomendado

### A. Antes de responder
Cuando el usuario pida ayuda técnica sobre algo no trivial:
- intentar recordar contexto relacionado con:
  - el módulo
  - el bug
  - la convención
  - el archivo
  - el patrón arquitectónico

### B. Durante el análisis
Si aparece un hallazgo importante:
- marcarlo mentalmente como candidato a memoria

### C. Al final
Si se confirmó algo relevante:
- guardar la memoria en forma resumida
- si hubo varios hallazgos conectados, considerar consolidar

## Formato recomendado de memoria
Al guardar, redactar con esta estructura:

- **tema**: módulo o área
- **hecho**: qué se confirmó
- **impacto**: por qué importa
- **acción o regla**: qué debe hacerse en el futuro

### Plantilla sugerida
`[área] hecho confirmado. Impacto: ... Regla futura: ...`

### Ejemplos
- `[routing] Las rutas admin deben usar nombres con prefijo admin.*. Impacto: evita inconsistencias al redirigir. Regla futura: usar siempre rutas con nombre.`
- `[frontend] El proyecto no usa framework frontend. Impacto: evitar dependencias innecesarias. Regla futura: resolver UI con Blade + JS vanilla.`
- `[debugging] El fallo al subir archivos se debía al límite de PHP y no a Laravel. Impacto: no tocar el controlador para este problema. Regla futura: revisar upload_max_filesize y post_max_size primero.`

## Comportamiento esperado del agente
Cuando uses esta skill:
- consulta memoria relacionada antes de proponer una solución grande
- cita la memoria recuperada en lenguaje natural, sin inventar detalles
- distingue entre memoria confirmada e hipótesis actual
- no sobreescribas una convención previa sin una razón clara
- si la memoria entra en conflicto con el estado actual del código, prioriza el código real y registra la discrepancia
- usa Engram para reducir repetición y preservar contexto útil entre sesiones

## Prioridades para este stack
En proyectos Laravel + Blade + JS vanilla:
- priorizar memorias sobre:
  - estructura de rutas
  - convenciones de controladores
  - partials y componentes Blade
  - validación con Form Requests
  - patrones de debugging frecuentes
  - restricciones sobre no usar frameworks frontend
  - decisiones sobre assets, scripts y formularios

## Patrones de consulta recomendados
Antes de trabajar en una tarea, buscar memoria relacionada con:
- nombre del módulo
- nombre del recurso
- tipo de problema
- convención técnica
- bug similar anterior
- decisión de arquitectura ya tomada

Ejemplos de intención de búsqueda:
- “convenciones Blade del proyecto”
- “reglas para formularios y validación”
- “bug previo con CSRF o sesión”
- “decisión sobre frontend sin framework”
- “estructura del módulo users”
- “criterios para CRUD con Laravel”

## Integración esperada
Si Engram está disponible:
- por MCP, usar las herramientas de memoria disponibles
- por CLI, usar operaciones equivalentes de recordar, consultar y consolidar
- por REST API, usar el servidor local si ya está corriendo
- si hay varias vías, preferir la integración más directa y local

## REST local de referencia
Si el entorno usa REST:
- el servidor local suele vivir en `http://127.0.0.1:3800`
- existen operaciones de guardar memoria y recall
- si el servidor no responde, tratar Engram como no disponible y continuar sin bloquear la tarea

## Reglas de seguridad
- nunca guardar secretos
- nunca guardar credenciales
- no persistir datos sensibles sin necesidad clara
- resumir antes de almacenar
- preferir memoria técnica del proyecto antes que datos personales

## Resolución de conflictos
Si memoria previa contradice el código actual:
1. inspeccionar el código actual
2. asumir que el código vigente tiene prioridad operativa
3. explicar la discrepancia
4. guardar una nueva memoria aclarando el cambio, si corresponde

## Tono de trabajo
- práctico
- preciso
- orientado a continuidad entre sesiones
- sin sobreingeniería
- enfocado en valor futuro

## Resultado esperado
El agente debe:
- recordar contexto del proyecto sin que el usuario repita todo
- proponer soluciones más consistentes con decisiones previas
- registrar hallazgos duraderos
- usar Engram como memoria útil, no como basurero de texto
