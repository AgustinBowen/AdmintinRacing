# Análisis de Vistas Blade (Laravel)

Este informe detalla el análisis realizado sobre los archivos `.blade.php` del proyecto, identificando áreas donde la lógica del servidor se ha filtrado a la capa de presentación y sugiriendo oportunidades de refactorización mediante componentes.

---

## 1. Consultas a Base de Datos y Relaciones

Lo ideal en MVC es que la vista reciba los datos ya listos (DTOs, arrays o colecciones preparadas). Sin embargo, se han detectado operaciones que acceden a la base de datos o relaciones dinámicas directamente desde Blade:

- **Acceso Profundo a Relaciones (`standings.blade.php`):**
  Se ejecuta `$piloto?->campeonatos->firstWhere('id', $campeonato->id)?->pivot->numero_auto`. Esto no solo es verboso, sino que si la relación `campeonatos` no fue cargada (*Eager Loading*), provocará el problema de **N+1 queries** directamente desde la vista.
- **Acceso a Constantes de Modelos:**
  Múltiples vistas acceden a constantes de los modelos, por ejemplo: `\App\Models\SesionDefinicion::TIPOS[$sesion->tipo]`, `\App\Models\SistemaPuntaje::TIPO_LABELS` o `\App\Models\SesionDefinicion::ORDEN`. Es mejor pasar estos mapas de etiquetas desde el Controlador o usar un *Presenter* u *Observer* para anexar un atributo `tipo_label` al modelo antes de enviarlo a la vista.

## 2. Lógica de Negocio en Vistas

Se encontró lógica de negocio crítica embebida usando bloques `@php ... @endphp`. Esto dificulta las pruebas automatizadas (Testing) y rompe la separación de responsabilidades.

- **Filtrado de Colecciones (`resultados.blade.php`):**
  La vista divide manualmente la colección de resultados para separar clasificados de excluidos:
  ```php
  @php
      $clasificados = $sesion->resultados->filter(fn($r) => !$r->excluido && rtrim($r->posicion) !== '');
      $noClasificados = $sesion->resultados->filter(fn($r) => $r->excluido || rtrim($r->posicion) === '');
  @endphp
  ```
  *Solución:* Esto debe resolverse en el Controlador y pasarse a la vista como `$clasificados` y `$noClasificados`, o implementarse como un método en el Modelo/Colección personalizada (ej. `$sesion->clasificados()`).

- **Parsing de Metadata (`import-preview.blade.php`):**
  Hay un bloque `@php` al inicio que extrae un elemento del JSON (el `_meta`), modifica el array original con un `array_pop`, y configura booleanos (`$hasSectors`, `$hasTiempoTotal`) que luego alteran la interfaz. Alterar estructuras de datos en Blade es una mala práctica; el Controlador debería parsear el JSON, remover la metadata, y pasar los flags a la vista.

- **Mapeo de Variables (`standings.blade.php`):**
  Se usa frecuentemente `@php $piloto = $row['piloto']; $fd = $row['fechas'][$fecha->id] ?? null; @endphp`. Aunque es sintaxis para facilitar la lectura, revela que los datos llegan crudos y la vista debe hacer trabajo de extracción. 

## 3. Cálculos y Transformaciones Visuales

- **Formatos Condicionales de Posiciones (`standings.blade.php`):**
  La lógica de asignar colores (oro, plata, bronce) a las posiciones 1, 2 y 3 mediante `@if / @elseif` está escrita duro en la vista.
- **Formatos de Badges (`resultados.blade.php`):**
  Lógica para imprimir `"EX"` o `"NT"` (`$resultado->excluido ? 'EX' : 'NT'`) en la tabla.

## 4. Código Duplicado

- **Estructura de Tablas de Resultados (`resultados.blade.php`):**
  Se repiten las etiquetas HTML para las tablas `<thead>` y `<tbody>` casi idénticas entre las sesiones tipo "acumulados" y las sesiones estándar, con leves diferencias en las columnas.
- **Scripts de JavaScript Embebidos:**
  Vistas como `import-preview.blade.php`, `standings.blade.php` y el componente `filters.blade.php` contienen enormes bloques de `<script>`. Esto causa duplicidad y dificulta la minificación. El código JS de drag-to-scroll, modales de quick-create o peticiones AJAX de filtros debería extraerse a archivos `.js` en `public/js/` o mediante Vite/Mix.

## 5. Sugerencias de Componentes Reutilizables (Extract to `x-components`)

Blade ofrece poderosos "Blade Components" (`<x-component>`). Se recomienda extraer las siguientes piezas para limpiar las vistas:

1. **`x-status-badge`**: Un componente que reciba un estado o condición (ej. excluido, no clasificado, en curso) y devuelva el HTML estandarizado del badge con el color correcto. Eliminaría la duplicidad de los tags `<span class="badge...">`.
2. **`x-medal-position`**: Un componente que reciba un número entero (ej. `1`) y devuelva el HTML estilizado (Oro para 1, Plata para 2, Bronce para 3, o el número en sí para el resto). Limpiaría profundamente la tabla de posiciones (`standings.blade.php`).
3. **`x-results-table`**: Las tablas mostradas en `resultados.blade.php` y `import-preview.blade.php` son repetitivas (S1, S2, S3, Mejor Vueltas, Total). Se podría crear un `<x-table>` configurable que acepte arrays de columnas y datos, logrando un código puramente declarativo en las vistas padre.
4. **`x-empty-state`**: Existen múltiples `divs` que renderizan "Todavía no hay resultados..." o "No hay sesiones...". Un componente genérico `<x-empty-state icon="fas fa-inbox" text="No hay registros" />` unificaría estos mensajes vacíos visualmente en toda la app.
5. **Separación JS/Blade en `filters.blade.php`**: El HTML del filtro es bueno como *partial*, pero el `<script>` de abajo (para AJAX dinámico) es demasiado grande para estar embebido. Moverlo a un `resources/js/filters.js` modular.
