---
name: vanilla-frontend-patterns
description: Usa esta skill cuando construyas interfaz web sin framework frontend. Favorece HTML semántico, CSS claro, JavaScript vanilla progresivo, accesibilidad y bajo acoplamiento con Laravel Blade.
---

# Vanilla Frontend Patterns

## Objetivo
Construir frontend sin framework usando:
- HTML semántico
- CSS mantenible
- JavaScript vanilla
- mejora progresiva
- accesibilidad
- integración natural con Blade

## Principios
- Resolver primero con HTML y CSS.
- Agregar JavaScript solo cuando aporte valor real.
- Priorizar simplicidad, legibilidad y robustez.
- Evitar dependencias innecesarias.
- Diseñar para formularios, tablas, filtros, modales simples y navegación clásica.
- Mantener compatibilidad razonable con navegadores modernos.
- El frontend debe funcionar bien incluso si el JavaScript falla en partes no críticas.

## Reglas de decisión
Antes de agregar JavaScript, evaluar:
1. ¿Se puede resolver solo con HTML/CSS?
2. ¿La interacción mejora realmente la experiencia?
3. ¿La solución puede ser pequeña y local?
4. ¿Se puede mantener sin introducir una mini-framework casera?

Si la respuesta no es clara, preferir una solución más simple.

## HTML
- Usar etiquetas semánticas:
  - `header`
  - `main`
  - `section`
  - `nav`
  - `form`
  - `table`
  - `button`
  - `label`
- Asociar siempre `label` con sus inputs.
- Usar botones reales para acciones, no `a` con hacks.
- Respetar jerarquía de encabezados.
- Mantener formularios claros y accesibles.
- Incluir texto visible suficiente en acciones importantes.

## CSS
- Escribir CSS claro y específico, evitando complejidad innecesaria.
- Favorecer clases utilitarias del proyecto si ya existen; si no, usar clases descriptivas.
- Evitar selectores demasiado profundos.
- Mantener espaciado, tipografía y estados consistentes.
- Diseñar pensando primero en layouts simples y adaptables.
- Evitar efectos visuales excesivos si no aportan.
- No introducir Tailwind, Bootstrap u otros frameworks salvo petición explícita.

## JavaScript vanilla
- Mantener scripts pequeños, modulares y con responsabilidad clara.
- Usar `addEventListener`, no handlers inline.
- Seleccionar elementos de forma robusta.
- Evitar manipulación excesiva del DOM.
- No duplicar lógica entre backend y frontend.
- Usar `fetch` solo cuando tenga sentido.
- Si una interacción puede funcionar con submit tradicional, preferir eso.
- Encapsular cada feature en una función pequeña o módulo simple.

## Mejora progresiva
- La funcionalidad principal debe existir sin JavaScript cuando sea razonable.
- JavaScript debe mejorar:
  - filtros rápidos
  - confirmaciones
  - previews
  - toggles
  - interacciones pequeñas
- No depender de JS para renderizar páginas completas en una app Blade tradicional.

## Accesibilidad
- Mantener contraste razonable.
- Hacer visibles los estados `focus`.
- Usar `aria-*` solo cuando haga falta y correctamente.
- Los modales y menús deben ser navegables con teclado.
- No depender solo del color para transmitir estado.
- Los mensajes de error y éxito deben ser claros.

## Patrones recomendados
### Formularios
- Validación principal en servidor.
- Validación cliente solo como apoyo.
- Deshabilitar submit solo cuando esté justificado.
- Mostrar estados de carga en acciones lentas.

### Tablas
- Tablas sencillas y legibles.
- Mantener encabezados claros.
- Para móvil, simplificar o permitir scroll horizontal antes que romper estructura.

### Modales
- Usarlos solo cuando eviten navegación innecesaria.
- Deben poder cerrarse con Escape y botón visible.
- No meter formularios gigantes dentro de modales si una página dedicada es mejor.

### Filtros y búsqueda
- Si es simple, usar GET con parámetros de query.
- Mantener URLs compartibles.
- Evitar filtros completamente dependientes de JS si el backend puede resolverlos.

### Confirmaciones
- Para acciones destructivas, usar confirmación clara.
- Preferir una solución simple antes que una librería pesada.

## Integración con Laravel
- El estado principal vive en backend.
- Blade renderiza la estructura y datos.
- JS añade interactividad localizada.
- Mantener los assets organizados y fáciles de ubicar.
- No crear una pseudo-SPA accidentalmente.

## Qué evitar
- introducir dependencias por una sola interacción pequeña
- usar demasiados listeners globales
- crear utilidades genéricas innecesarias
- sobrecargar una página con animaciones o lógica compleja
- esconder comportamiento importante dentro de scripts difíciles de rastrear

## Resultado esperado
El resultado debe verse como frontend clásico bien hecho:
- rápido
- claro
- accesible
- fácil de mantener
- alineado con Laravel Blade
