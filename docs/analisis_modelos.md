# Análisis de Modelos de Laravel

Este informe detalla el análisis de los 13 modelos encontrados en el proyecto, evaluando sus responsabilidades, relaciones, posibles problemas de diseño, atributos que podrían pertenecer a otra entidad y modelos redundantes.

---

## 1. Campeonato
- **Responsabilidad:** Representa una temporada o campeonato específico (ej. "Turismo Pista 2024").
- **Relaciones:**
  - `hasMany` Fecha (Un campeonato tiene muchas fechas).
  - `belongsToMany` Piloto (Muchos pilotos participan en un campeonato).
  - `hasMany` PosicionCampeonato (Tabla de posiciones del campeonato).
  - `hasMany` SistemaPuntaje (Reglas de puntuación base del campeonato).
- **Problemas de diseño:**
  - `public $timestamps = false;`: No registrar cuándo se creó o modificó un campeonato puede dificultar auditorías futuras.
- **Atributos desubicados:** Ninguno evidente, aunque el atributo `anio` podría ser redundante si se puede derivar de las fechas, pero es práctico mantenerlo para búsquedas.

## 2. Circuito
- **Responsabilidad:** Representa un circuito o autódromo físico.
- **Relaciones:**
  - `hasMany` Fecha (Un circuito alberga múltiples eventos a lo largo del tiempo).
- **Problemas de diseño:**
  - **Inconsistencia de Claves Primarias:** Mientras que casi todos los demás modelos usan UUIDs (`HasUuids`), este modelo parece usar IDs auto-incrementales por defecto. Es importante mantener consistencia en la arquitectura.
- **Atributos desubicados:**
  - `distancia`: Un circuito físico suele tener múltiples "variantes" o trazados, cada uno con una distancia distinta. La distancia debería pertenecer a un modelo `VarianteCircuito` o `Trazado`, no al circuito en general.

## 3. Fecha
- **Responsabilidad:** Representa un evento de fin de semana de carreras dentro de un campeonato.
- **Relaciones:**
  - `belongsTo` Campeonato (Pertenece a un campeonato).
  - `belongsTo` Circuito (Se corre en un circuito).
  - `hasMany` SesionDefinicion (Tiene múltiples sesiones como Prácticas, Clasificación, Final).
  - `hasMany` Horario (Horarios del evento).
  - `hasMany` Imagen (Galería del evento).
  - `hasMany` SistemaPuntajeFecha (Puntuaciones específicas o excepcionales para esta fecha).
- **Problemas de diseño:**
  - Carece de timestamps (`public $timestamps = false;`).
- **Atributos desubicados:** Ninguno evidente.

## 4. Horario
- **Responsabilidad:** Define el horario de una sesión específica dentro de una fecha.
- **Relaciones:**
  - `belongsTo` Fecha
  - `belongsTo` SesionDefinicion
- **Problemas de diseño:**
  - **Relaciones redundantes:** `Horario` pertenece a `SesionDefinicion`, y a su vez `SesionDefinicion` ya pertenece a `Fecha`. Tener un `fecha_id` directamente en `Horario` es una desnormalización que puede llevar a inconsistencias si el `fecha_id` del Horario no coincide con el `fecha_id` de la Sesión.
- **Atributos desubicados:**
  - `fecha_id` no debería estar aquí, ya que se puede acceder a través de la sesión.
- **Modelos Redundantes:** Ver sección de "Modelos Redundantes".

## 5. Imagen
- **Responsabilidad:** Almacena imágenes (mediante URLs de Cloudinary) asociadas a una fecha.
- **Relaciones:**
  - `belongsTo` Fecha
- **Problemas de diseño:**
  - Acoplamiento fuerte con `Fecha`. Si en el futuro se desean agregar imágenes a Pilotos, Circuitos o Campeonatos, este diseño obligará a crear tablas separadas o modificar esta.
  - **Sugerencia:** Usar relaciones polimórficas (`imageable_id`, `imageable_type`) para que cualquier modelo pueda tener imágenes.

## 6. Piloto
- **Responsabilidad:** Representa a un corredor/piloto.
- **Relaciones:**
  - `belongsToMany` Campeonato
- **Problemas de diseño:**
  - **Accessor Peligroso:** El método `getNumeroAutoPivotAttribute()` obtiene el `numero_auto` de la primera relación que encuentra (`$this->campeonatos->first()->pivot->numero_auto`). Un piloto puede correr en múltiples campeonatos con números distintos. Esto devolverá un número aleatorio dependiendo de cómo se carguen las relaciones, provocando bugs en la UI.
- **Atributos desubicados:** Ninguno evidente.

## 7. PilotoCampeonato
- **Responsabilidad:** Modelo Pivot que relaciona a los pilotos con los campeonatos e incluye datos específicos de esa inscripción (ej. el número del auto).
- **Relaciones:**
  - `belongsTo` Piloto
  - `belongsTo` Campeonato
- **Problemas de diseño:**
  - Extiende de `Model` en lugar de `Illuminate\Database\Eloquent\Relations\Pivot`. Aunque funciona, usar `Pivot` otorga comportamientos más adecuados para tablas intermedias en Laravel.
- **Atributos desubicados:** Ninguno evidente.

## 8. PosicionCampeonato
- **Responsabilidad:** Almacena la tabla de posiciones (puntos totales) de los pilotos en un campeonato.
- **Relaciones:**
  - `belongsTo` Campeonato
  - `belongsTo` Piloto
- **Problemas de diseño / Modelos Redundantes:**
  - Este modelo representa **datos derivados**. Los puntos totales deberían ser la suma de los puntos obtenidos en los `ResultadoSesion` de todas las fechas del campeonato. 
  - Almacenar esto en una tabla separada crea problemas de sincronización (si se edita el resultado de una carrera pasada, hay que recordar actualizar esta tabla). Debería usarse solo como caché (vistas materializadas) o calcularse en tiempo real (con un método en el modelo Campeonato).

## 9. ResultadoSesion
- **Responsabilidad:** Registra el desempeño de un piloto en una sesión particular (tiempos, posición, puntos).
- **Relaciones:**
  - `belongsTo` SesionDefinicion
  - `belongsTo` Piloto
- **Problemas de diseño:**
  - **Estados Booleanos Conflictivos:** Tiene booleanos `excluido` y `presente`. Esto permite estados inválidos (ej. no presente pero excluido). Sería mejor un único campo `estado` (enum: 'clasificado', 'abandono', 'excluido', 'ausente').
  - Exceso de lógica de formateo de tiempo (`formatSecondsToTime`). Sería más limpio mover esto a un *Presenter* o usar *Casts* personalizados de Laravel, para no sobrecargar el modelo.

## 10. SesionDefinicion
- **Responsabilidad:** Define una sesión de pista (Ej. Entrenamiento 1, Clasificación, Final) dentro de una Fecha.
- **Relaciones:**
  - `belongsTo` Fecha
  - `hasMany` ResultadoSesion
  - `hasMany` Horario
- **Problemas de diseño:**
  - **Constantes rígidas:** Las constantes `TIPOS` y `ORDEN` están hardcodeadas en el código. Si un campeonato especial tiene un formato distinto, requerirá modificar el código fuente. El "orden" de las sesiones debería ser una columna en la base de datos.
  - **Cascada en Eloquent:** El método `boot()` maneja la eliminación en cascada. Es mejor delegar esto a la base de datos mediante llaves foráneas con `ON DELETE CASCADE` para garantizar la integridad referencial.
- **Atributos desubicados:**
  - `fecha_sesion`: Este atributo define el día de la sesión, pero existe el modelo `Horario` que guarda el datetime. La información temporal está dividida y duplicada.

## 11. SistemaPuntaje
- **Responsabilidad:** Define cuántos puntos se otorgan por posición en cada tipo de sesión para un campeonato completo.
- **Relaciones:**
  - `belongsTo` Campeonato
- **Problemas de diseño:**
  - Constantes `TIPO_LABELS` y `DEFAULT_SCORING` fuertemente acopladas.

## 12. SistemaPuntajeFecha
- **Responsabilidad:** Define reglas de puntuación específicas o excepcionales para una Fecha particular.
- **Relaciones:**
  - `belongsTo` Fecha
- **Modelos Redundantes:** Ver sección de "Modelos Redundantes".

## 13. User
- **Responsabilidad:** Gestión de usuarios del sistema (Autenticación).
- **Relaciones:** No tiene relaciones declaradas con el negocio (Pilotos, etc.).
- **Problemas de diseño:** Modelo estándar de Laravel, sin problemas aparentes para su función actual.

---

## 🛑 Resumen de Modelos Redundantes y Problemas Críticos

### 1. `Horario` vs `SesionDefinicion` (Redundancia y Atributos Desubicados)
El modelo `Horario` es un fuerte candidato a desaparecer. Una sesión de pista (`SesionDefinicion`) típicamente ocurre en un rango de tiempo específico. 
**Solución:** Mover los atributos `horario` (datetime), `duracion` y `observaciones` desde `Horario` hacia `SesionDefinicion`. Esto elimina una tabla completa, simplifica las consultas y elimina la redundancia del campo `fecha_id`.

### 2. `SistemaPuntaje` vs `SistemaPuntajeFecha` (Redundancia Estructural)
Ambos modelos tienen exactamente la misma estructura y propósito, solo cambia a qué entidad pertenecen (`campeonato_id` vs `fecha_id`).
**Solución:** Combinarlos en un único modelo `SistemaPuntaje`. Se puede lograr haciendo que `campeonato_id` y `fecha_id` sean anulables (`nullable`), de forma que si `fecha_id` tiene un valor, ese sistema aplica a la fecha y sobrescribe al del campeonato. Alternativamente, usar relaciones polimórficas (`puntuable_id`, `puntuable_type`).

### 3. `PosicionCampeonato` (Datos Derivados Peligrosos)
Esta tabla es redundante respecto a la suma natural de los datos. Los puntos de un campeonato son el resultado directo de la suma de los puntos en `ResultadoSesion` para ese campeonato. Mantener esta tabla requiere procesos de sincronización propensos a fallos.
**Solución:** Calcular las posiciones en tiempo real usando consultas agregadas SQL o, si el rendimiento es un problema, implementar un patrón de caché sólido o Vistas Materializadas, pero evitar manipular esto como modelo de datos "fuente de verdad".

### 4. Accesores Conflictivos en `Piloto`
El método `$this->campeonatos->first()->pivot->numero_auto` fallará lógicamente en cuanto un piloto participe en un segundo campeonato con distinto número de auto. El número de auto es contextual al campeonato y siempre debe consultarse desde el contexto de la relación Pivot activa, no genéricamente desde el piloto.
