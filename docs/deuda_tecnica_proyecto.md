# Informe Global de Deuda Técnica (Proyecto AdmintinRacing)

Este informe consolida los hallazgos tras un análisis exhaustivo del código en las capas de Modelos, Controladores, Vistas, Servicios y Traits del proyecto Laravel. Detalla problemas estructurales, de rendimiento y duplicación de código.

---

## 1. Problemas de Rendimiento (Performance)

### 1.1 Full Table Scans en Búsquedas Globales (Traits)
En `HasSearchAndPagination` y `SearchableSelectTrait`, se utiliza repetidamente el siguiente Raw SQL para implementar búsquedas Case/Accent Insensitive:
```sql
unaccent(lower(CAST(campo AS TEXT))) LIKE unaccent(lower(?))
```
**Impacto:** Esto obliga a PostgreSQL a ejecutar un escaneo secuencial (Full Table Scan) en cada fila de la base de datos cada vez que se teclea en el frontend (para Select2 o tablas). 
**Solución:** Moverse a Búsqueda Full-Text (ej. Laravel Scout) o crear Índices GIN/Trigram específicos en PostgreSQL para esas expresiones.

### 1.2 Complejidad Ciclomática y Colecciones O(N³)
En `StandingsService::calcular()`, la lógica anida iteraciones: *Por cada fecha -> por cada piloto -> por cada sesión*. Dentro del loop más profundo, ejecuta `$sesion->resultados->where('piloto_id', $pilotoId)->first();`.
**Impacto:** Buscar iterativamente dentro de colecciones instanciadas en memoria a lo largo de 3 ciclos anidados destruirá la memoria RAM y el tiempo de respuesta a medida que el campeonato gane fechas y pilotos.
**Solución:** En lugar de iterar objetos de Eloquent, agrupar (Group By) los resultados previamente con una consulta plana de base de datos o mapear todo a una tabla Hash simple de PHP `[$pilotoId][$sesionId] = $resultado` antes del ciclo.

### 1.3 N+1 Queries Ocultos en Blade
En vistas como `standings.blade.php`, se llama a `$piloto?->campeonatos->firstWhere(...)`.
**Impacto:** Si la relación no fue pre-cargada masivamente, esto dispara una query a la BD por cada fila en el HTML.

---

## 2. Código Duplicado (Violación DRY)

### 2.1 Lógica de API Idéntica
Toda la lógica de transformación de Fechas y Sesiones, junto con el cálculo algorítmico para determinar si el evento es `live`, `upcoming` o `completed`, está **100% copiada y pegada** entre `CampeonatoApiController` y `FechaApiController`.

### 2.2 Parseo de Tiempos Manual
Múltiples controladores (`ResultadoSesionController` en `store`, `update`, `import`) definen cierres locales (`$parseTime = function(...)`) idénticos para convertir strings "1:16.389" a segundos decimales.

### 2.3 Constructores de Búsquedas (Raw SQL)
La lógica profunda de partir relaciones (`explode('.', $field)` y aplicar el Raw de `unaccent(...)`) está copiada idénticamente en los archivos `HasSearchAndPagination` y `SearchableSelectTrait`. Debería existir un solo Query Builder Macro en su lugar.

---

## 3. Responsabilidades Mezcladas (Arquitectura MVC)

### 3.1 Validaciones en Controladores (Fat Controllers)
No existe ni un solo `FormRequest` en todo el proyecto. Todas las validaciones, reglas anidadas y mensajes de error traducidos están bloqueando los métodos de los controladores.

### 3.2 Lógica de Negocio en Vistas
Archivos como `import-preview.blade.php` o `resultados.blade.php` utilizan `@php` para extraer metadata de arreglos o dividir/filtrar colecciones de Eloquent (clasificados vs excluidos). Todo dato que deba procesarse matemáticamente o algorítmicamente debe resolverse en el Controlador.

### 3.3 "Fuzzy Matching" en Controladores
`ResultadoSesionController::importPreview` tiene el algoritmo duro de separar nombres con "explode", buscar coincidencias con `str_contains` y mapear IDs para los OCR de planillas de carreras. Esto requiere ser extraído urgentemente a un `OcrMatcherService` o `ImportProcessorAction`.

### 3.4 StandingsService mezcla cálculo y persistencia
El servicio `StandingsService` calcula en memoria el estado del campeonato, pero también contiene métodos como `syncFechaPuntos` que iteran modelos de BD y ejecutan un `.update()` masivo sobre los registros uno por uno.

---

## 4. Métodos Excesivamente Largos

- **`StandingsService::calcular()`:** Supera las 110 líneas de lógica algorítmica ininterrumpida. Su lógica de asignar puntos (Series vs Final vs Presentación) debe romperse en métodos privados más pequeños o clases de estrategias (`ScoringStrategy`).
- **`FechaController::generarCronogramaEstandar()`:** Inicializa iteradores de fechas (`Carbon`), crea arreglos gigantescos instanciando modelos hijos y maneja todo el flujo de trabajo en una sola función procedimental dentro de una ruta web.

---

## 5. Resumen del Plan de Pago de Deuda Sugerido

1. **Corto Plazo:** 
   - Extraer todos los `$request->validate()` hacia clases `FormRequest`.
   - Crear `API Resources` para consolidar las respuestas JSON duplicadas entre `CampeonatoApiController` y `FechaApiController`.
2. **Mediano Plazo:**
   - Extraer los componentes repetitivos de Blade (Badges, Tablas, Modales) a `<x-components>`.
   - Refactorizar `HasSearchAndPagination` para usar un Scope o Macro en el Builder en lugar de código pegado.
3. **Largo Plazo:**
   - Reescribir `StandingsService::calcular()` para usar SQL Agregado (SUM, GROUP BY) u optimizar la estructura de datos en memoria para evitar complejidad O(N³).
   - Reemplazar el escaneo de tabla con `unaccent()` instalando un motor de búsqueda indexado o generando índices en Postgres.
