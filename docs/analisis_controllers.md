# Análisis de Controllers de Laravel

Este informe detalla los hallazgos en los controladores del proyecto, evaluando sus responsabilidades, la presencia de lógica de negocio (que idealmente debería estar desacoplada), consultas complejas, validaciones embebidas (en lugar de `FormRequests`) y código duplicado.

---

## 1. Patrones Generales Encontrados

Antes de detallar cada controlador, existen problemas arquitectónicos recurrentes en toda la capa de controladores:
- **Validaciones Embebidas:** **Ningún** controlador utiliza `FormRequests` de Laravel. Todas las validaciones (`$request->validate(...)`) están embebidas en los métodos `store` y `update`. Esto engorda los controladores y dificulta la reutilización.
- **Lógica de Dominio en Controladores:** Operaciones complejas como generar cronogramas, emparejar resultados de escaneos OCR o parsear tiempos están escritas proceduralmente dentro de los controladores en lugar de usar Servicios, *Actions* o *Observers*.

---

## 2. Análisis por Controlador

### `FechaController`
- **Responsabilidades:** CRUD de Fechas, gestión de cronogramas, generación de acumulados, y overrides de reglas de puntaje (`SistemaPuntajeFecha`).
- **Lógica de negocio embebida:** EXCESIVA.
  - El método `generarCronogramaEstandar` crea masivamente sesiones y horarios base instanciando modelos y sumando días. Esto pertenece a un `CronogramaBuilderService`.
  - El método `generarAcumulados` tiene lógica algorítmica: busca entrenamientos, los agrupa por piloto en un array PHP, determina el mejor tiempo con `uasort` manual, y luego inserta datos. Esta es lógica pura del dominio.
- **Consultas complejas:** En `resultados()`, hace *Eager Loading* profundo y luego ordena colecciones enteras en memoria (`$fecha->sesiones->sortBy(...)`).
- **Código duplicado:** Cada método de actualización de scoring (`addScoringFecha`, `updateScoringFecha`, `deleteScoringFecha`, etc.) repite exactamente la misma llamada de sincronización: `(new \App\Services\StandingsService())->syncFechaPuntos($fecha);`.

### `ResultadoSesionController`
- **Responsabilidades:** CRUD de resultados, importación desde JSON/OCR, y formateo para búsquedas Select2.
- **Lógica de negocio embebida:**
  - En `importPreview`, implementa un algoritmo manual de "Fuzzy Matching" (*Strict Intersection*) dividiendo el nombre del piloto por espacios e iterando para asociar el escaneo OCR con la base de datos.
  - Uso de *closures* internos como `$parseTime` para convertir `1:16.389` a segundos decimales.
- **Validaciones embebidas:** Contiene uno de los bloques de validación más grandes del sistema en el método `store`, con casi 30 líneas de mensajes de error traducidos manualmente.
- **Código duplicado:** El closure `$parseTime` y el bloque `$request->merge(...)` que pre-procesa las variables temporales está copiado y pegado idénticamente en los métodos `store`, `update`, y de forma similar en `storeImport`.

### `PilotoController`
- **Responsabilidades:** CRUD de pilotos e importación desde JSON.
- **Lógica de negocio embebida:** Sincronización manual de la tabla pivot manejando la generación explícita de UUIDs para `PilotoCampeonato`.
- **Consultas complejas:** En `update`, realiza una consulta suelta usando el Facade `DB::table(...)` para chequear manualmente si el `numero_auto` está ocupado por otro piloto (validación que debería ir en un Rule personalizado o un Request).
- **Código duplicado:** El bloque de código que asocia un piloto a un campeonato (`$piloto->campeonatos()->syncWithoutDetaching(...)`) está triplicado en `store`, `update`, `storeImport` y `quickStore`.

### `CampeonatoController`
- **Responsabilidades:** CRUD de campeonatos y gestión del esquema base de puntuación (`SistemaPuntaje`).
- **Lógica de negocio embebida:** Intermediario. Llama al `StandingsService` repetidas veces.
- **Validaciones embebidas:** Reglas repetidas en `store` y `update`.
- **Código duplicado:** Al igual que en `FechaController`, repite la llamada masiva a la sincronización de posiciones tras cada micro-modificación en la tabla de puntos.

### `HorarioController`
- **Responsabilidades:** CRUD de horarios.
- **Lógica de negocio embebida:** Concatena strings de fechas con horas y parsea con `Carbon` para formar el timestamp real.
- **Validaciones embebidas:** Contiene *closures* de validación anidados para chequear superposiciones de horarios (`function ($attribute, $value, $fail)`).
- **Código duplicado:** La lógica de generación del `$timestamp` está duplicada en `store`, `update` y `updateFromFecha`.

### `API / CampeonatoApiController` y `API / FechaApiController`
- **Responsabilidades:** Endpoints para proveer JSONs formateados al frontend/SPA.
- **Lógica de negocio embebida:** Procesamiento y formateo de datos masivo. Contiene lógica para determinar si el estado del evento es `live`, `upcoming` o `completed`.
- **Código duplicado (Crítico):** Los métodos `formatFechaWithResults`, `formatSesiones`, `formatResultado`, así como la lógica para saber si un evento está en curso o finalizado, están copiados **exactamente igual** en ambos controladores. Esto es una violación severa de DRY (*Don't Repeat Yourself*). Deberían extraerse a Recursos de API (`JsonResource` de Laravel) o un Servicio Transformador.

### `Auth / LoginController`
- **Responsabilidades:** Autenticación.
- **Código duplicado:** Contiene dos métodos (`submit` y `login`) que realizan **exactamente** la misma función (validar credenciales e iniciar sesión con `Auth::attempt`). Uno de los dos debe eliminarse.

---

## 🛑 Resumen de Mejoras Urgentes

1. **Crear FormRequests:** Mover todos los bloques `$request->validate(...)` hacia clases dedicadas (`StoreFechaRequest`, `UpdateResultadoRequest`, etc.). Esto limpiará cientos de líneas en los controladores.
2. **Extraer a Actions o Services:** Lógicas como "Generar Cronograma", "Parsear Tiempo de Carrera" o "Fuzzy Match de Nombres OCR" no tienen lugar en un controlador. 
3. **API Resources:** Eliminar la duplicación de código en la API creando `FechaResource`, `SesionResource` y `ResultadoResource`.
4. **Refactor de Parsing de Tiempos:** El closure `$parseTime` que se repite, debería ser un Mutator en Eloquent (`setTiempoTotalAttribute`) o un Cast personalizado.
