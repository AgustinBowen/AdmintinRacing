<?php

namespace App\Http\Controllers;

use App\Models\Fecha;
use App\Models\Campeonato;
use App\Models\Circuito;
use App\Models\SesionDefinicion;
use App\Models\Horario;
use App\Models\ResultadoSesion;
use App\Models\SistemaPuntajeFecha;
use App\Services\StandingsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Traits\HasSearchAndPagination;

class FechaController extends Controller
{
    use HasSearchAndPagination;

    public function index(Request $request)
    {
        // Configurar paginación
        $this->setupPagination();

        // Crear consulta base
        $query = Fecha::query();

        // Aplicar búsqueda
        $searchFields = ['nombre', 'circuito.nombre', 'campeonato.nombre']; // Campos en los que buscar
        $this->applySearch($query, $request, $searchFields);

        // Definir columnas de la tabla
        $columns = [
            ['field' => 'nombre', 'label' => 'Nombre', 'type' => 'text'],
            ['field' => 'fecha_desde', 'label' => 'Fecha Desde', 'type' => 'date'],
            ['field' => 'fecha_hasta', 'label' => 'Fecha Hasta', 'type' => 'date'],
            ['field' => 'circuito.nombre', 'label' => 'Circuito', 'type' => 'text'],
            ['field' => 'campeonato.nombre', 'label' => 'Campeonato', 'type' => 'text'],
        ];

        // Configuración específica
        $config = [
            'orderBy' => 'fecha_desde',
            'orderDirection' => 'asc',
            'nameField' => 'fecha_desde'
        ];

        // Manejar respuesta
        $result = $this->handleIndexResponse($request, $query, $columns, 'admin.fechas', $config);

        // Si es AJAX, ya se devolvió la respuesta
        if ($request->ajax()) {
            return $result;
        }

        // Si no es AJAX, devolver la vista completa
        $fechas = $result;
        return view('admin.fechas.index', compact('fechas'));
    }

    public function create()
    {
        $campeonatos = Campeonato::orderBy('anio', 'desc')->get();
        $circuitos = Circuito::orderBy('nombre')->get();

        return view('admin.fechas.create', compact('campeonatos', 'circuitos'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'fecha_desde' => 'required|date',
            'fecha_hasta' => 'required|date',
            'campeonato_id' => 'required|exists:campeonatos,id',
            'circuito_id' => 'required|exists:circuitos,id',
            'generar_cronograma' => 'nullable|boolean',
        ]);

        $generarCronograma = $request->has('generar_cronograma') && $request->generar_cronograma;
        unset($validated['generar_cronograma']);

        $fecha = Fecha::create($validated);

        if ($generarCronograma) {
            $this->generarCronogramaEstandar($fecha);
        }

        return redirect()->route('admin.fechas.index')
            ->with('success', 'Fecha creada exitosamente.');
    }

    public function show(Fecha $fecha)
    {
        $fecha->load(['campeonato', 'circuito', 'sesiones.horarios']);
        
        // Sort sessions by the official order
        $fecha->setRelation('sesiones', $fecha->sesiones->sortBy(function($sesion) {
            return \App\Models\SesionDefinicion::ORDEN[$sesion->tipo] ?? 99;
        })->values());

        // Calculate how many standard session types are not yet created
        $tiposExistentes = $fecha->sesiones->pluck('tipo')->toArray();
        $todosTipos = array_keys(SesionDefinicion::TIPOS);
        $pendientesKeys = array_diff($todosTipos, $tiposExistentes);
        $sesionesPendientes = count($pendientesKeys);
        
        $sesionesPendientesLista = [];
        foreach ($pendientesKeys as $key) {
            $sesionesPendientesLista[] = SesionDefinicion::TIPOS[$key] ?? $key;
        }

        return view('admin.fechas.show', compact('fecha', 'sesionesPendientes', 'sesionesPendientesLista'));
    }

    public function edit(Fecha $fecha)
    {
        $campeonatos = Campeonato::orderBy('anio', 'desc')->get();
        $circuitos = Circuito::orderBy('nombre')->get();

        return view('admin.fechas.edit', compact('fecha', 'campeonatos', 'circuitos'));
    }

    public function update(Request $request, Fecha $fecha)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'fecha_desde' => 'required|date',
            'fecha_hasta' => 'required|date',
            'campeonato_id' => 'required|exists:campeonatos,id',
            'circuito_id' => 'required|exists:circuitos,id',
        ]);

        $fecha->update($validated);

        return redirect()->route('admin.fechas.index')
            ->with('success', 'Fecha actualizada exitosamente.');
    }

    public function destroy(Fecha $fecha)
    {
        $fecha->delete();

        return redirect()->route('admin.fechas.index')
            ->with('success', 'Fecha eliminada exitosamente.');
    }

    public function resultados(Fecha $fecha)
    {
        // Load sessions and their results
        $fecha->load(['campeonato', 'circuito', 'sesiones.resultados.piloto.campeonatos']);

        // Sort sessions by the official order
        $fecha->setRelation('sesiones', $fecha->sesiones->sortBy(function($sesion) {
            return \App\Models\SesionDefinicion::ORDEN[$sesion->tipo] ?? 99;
        })->values());

        // Sort results within each session
        // Collect all practice sessions for Acumulados calculation if needed
        $practicas = $fecha->sesiones->filter(fn($s) => str_starts_with($s->tipo, 'entrenamiento_'));

        foreach ($fecha->sesiones as $sesion) {
            // Sort results
            $sesion->setRelation('resultados', $sesion->resultados->sortBy(function ($res) {
                if ($res->excluido) return 9999;
                if (is_null($res->posicion)) return 9998;
                return $res->posicion;
            })->values());

            // Special logic for Acumulados (already sorted by result position)
            if ($sesion->tipo === 'acumulados') {
                // No extra data needed for now as we only show the first 6 columns
            }
        }

        return view('admin.fechas.resultados', compact('fecha'));
    }

    /**
     * Generar sesiones estándar automáticamente (desde el botón en el show)
     */
    public function generarSesiones(Fecha $fecha)
    {
        $this->generarCronogramaEstandar($fecha);

        return redirect()->route('admin.fechas.show', $fecha)
            ->with('success', 'Sesiones y horarios generados exitosamente.');
    }

    /**
     * Eliminar todas las sesiones y horarios de una fecha
     */
    public function eliminarSesiones(Fecha $fecha)
    {
        // Delete horarios first (they have FK to sesiones)
        Horario::where('fecha_id', $fecha->id)->delete();
        SesionDefinicion::where('fecha_id', $fecha->id)->delete();

        return redirect()->route('admin.fechas.show', $fecha)
            ->with('success', 'Sesiones y horarios eliminados exitosamente.');
    }

    /**
     * Generar un cronograma estándar de sesiones y horarios para una fecha
     */
    private function generarCronogramaEstandar(Fecha $fecha)
    {
        $dia1 = \Carbon\Carbon::parse($fecha->fecha_desde);
        $dia2 = $dia1->copy()->addDay();
        $diaFinal = \Carbon\Carbon::parse($fecha->fecha_hasta);

        $sesiones = [
            ['tipo' => 'entrenamiento_1',        'hora' => '09:00', 'duracion' => '15 min',    'dia' => $dia1],
            ['tipo' => 'entrenamiento_2',        'hora' => '09:30', 'duracion' => '15 min',    'dia' => $dia1],
            ['tipo' => 'entrenamiento_3',        'hora' => '10:00', 'duracion' => '15 min',    'dia' => $dia1],
            ['tipo' => 'entrenamiento_4',        'hora' => '10:30', 'duracion' => '15 min',    'dia' => $dia1],
            ['tipo' => 'acumulados',             'hora' => '11:00', 'duracion' => 'Calculado', 'dia' => $dia1],
            ['tipo' => 'clasificacion',          'hora' => '09:00', 'duracion' => '10 min',    'dia' => $dia2],
            ['tipo' => 'serie_clasificatoria_1', 'hora' => '12:00', 'duracion' => '6 vueltas', 'dia' => $dia2],
            ['tipo' => 'serie_clasificatoria_2', 'hora' => '13:00', 'duracion' => '6 vueltas', 'dia' => $dia2],
            ['tipo' => 'serie_clasificatoria_3', 'hora' => '14:00', 'duracion' => '6 vueltas', 'dia' => $dia2],
            ['tipo' => 'carrera_final',          'hora' => '16:00', 'duracion' => '12 vueltas','dia' => $diaFinal],
        ];

        foreach ($sesiones as $s) {
            // Skip if a session of this type already exists for this fecha
            $existe = SesionDefinicion::where('fecha_id', $fecha->id)
                ->where('tipo', $s['tipo'])
                ->exists();

            if ($existe) {
                continue;
            }

            $sesion = SesionDefinicion::create([
                'fecha_id'     => $fecha->id,
                'tipo'         => $s['tipo'],
                'fecha_sesion' => $s['dia']->format('Y-m-d'),
            ]);

            Horario::create([
                'fecha_id'      => $fecha->id,
                'sesion_id'     => $sesion->id,
                'horario'       => Carbon::parse($s['dia']->format('Y-m-d') . ' ' . $s['hora']),
                'duracion'      => $s['duracion'],
                'observaciones' => null,
            ]);
        }
    }

    /**
     * Generar la sesión de resultados ACUMULADOS (mejor tiempo de cada piloto en entrenamientos)
     */
    public function generarAcumulados(Fecha $fecha)
    {
        // 1. Obtener o crear la sesión de Acumulados
        $sesionAcumulados = SesionDefinicion::firstOrCreate([
            'fecha_id' => $fecha->id,
            'tipo'     => 'acumulados'
        ], [
            'fecha_sesion' => $fecha->fecha_desde // Por defecto el primer día
        ]);

        // 2. Limpiar resultados anteriores de acumulados
        $sesionAcumulados->resultados()->delete();

        // 3. Obtener todos los resultados de entrenamientos de esta fecha
        $resultadosEntrenamientos = \App\Models\ResultadoSesion::whereHas('sesion', function($q) use ($fecha) {
            $q->where('fecha_id', $fecha->id)
              ->where('tipo', 'like', 'entrenamiento_%');
        })->get();

        if ($resultadosEntrenamientos->isEmpty()) {
            return redirect()->back()->with('error', 'No hay resultados de entrenamiento para generar los acumulados.');
        }

        // 4. Agrupar por piloto y encontrar el mejor tiempo
        $mejorPorPiloto = [];
        foreach ($resultadosEntrenamientos as $res) {
            if (!$res->mejor_tiempo) continue;
            
            if (!isset($mejorPorPiloto[$res->piloto_id]) || $res->mejor_tiempo < $mejorPorPiloto[$res->piloto_id]['tiempo']) {
                $sessionName = SesionDefinicion::TIPOS[$res->sesion->tipo] ?? $res->sesion->tipo;
                $mejorPorPiloto[$res->piloto_id] = [
                    'tiempo' => $res->mejor_tiempo,
                    'vueltas' => max($res->vueltas ?? 0, isset($mejorPorPiloto[$res->piloto_id]) ? $mejorPorPiloto[$res->piloto_id]['vueltas'] : 0),
                    'en_sesion' => $sessionName
                ];
            }
        }

        // 5. Ordenar por tiempo ascendente
        uasort($mejorPorPiloto, function($a, $b) {
            return $a['tiempo'] <=> $b['tiempo'];
        });

        // 6. Crear los nuevos resultados de acumulados
        $posicion = 1;
        $primerTiempo = !empty($mejorPorPiloto) ? reset($mejorPorPiloto)['tiempo'] : 0;

        foreach ($mejorPorPiloto as $pilotoId => $data) {
            \App\Models\ResultadoSesion::create([
                'sesion_id'          => $sesionAcumulados->id,
                'piloto_id'          => $pilotoId,
                'posicion'           => $posicion++,
                'mejor_tiempo'       => $data['tiempo'],
                'vueltas'            => $data['vueltas'],
                'diferencia_primero' => $data['tiempo'] - $primerTiempo,
                'observaciones'      => $data['en_sesion'],
                'puntos'             => 0,
                'presente'           => true
            ]);
        }

        return redirect()->route('admin.fechas.resultados', $fecha)
            ->with('success', 'Se han generado los resultados acumulados de entrenamientos.');
    }

    // -------------------------------------------------------
    // Per-Fecha Scoring Override Management
    // -------------------------------------------------------

    /**
     * Show the scoring management page for a single fecha.
     */
    public function scoringFecha(Fecha $fecha)
    {
        $fecha->load('campeonato');

        $scoring = SistemaPuntajeFecha::where('fecha_id', $fecha->id)
            ->orderBy('tipo_sesion')
            ->orderBy('posicion')
            ->get()
            ->groupBy('tipo_sesion');

        return view('admin.fechas.scoring', compact('fecha', 'scoring'));
    }

    /**
     * Seed overrides from championship defaults. (Manual customization start)
     */
    public function customizeScoringFecha(Fecha $fecha)
    {
        StandingsService::seedFechaScoring($fecha->id, $fecha->campeonato_id);
        
        // Sincronizar puntos de la fecha
        (new \App\Services\StandingsService())->syncFechaPuntos($fecha);

        return back()->with('success', 'Reglas incorporadas del campeonato. Ahora podés personalizarlas.');
    }

    /**
     * Add a new scoring row for a fecha.
     */
    public function addScoringFecha(Request $request, Fecha $fecha)
    {
        $request->validate([
            'tipo_sesion' => 'required|in:presentacion,clasificacion,serie,final',
            'posicion'    => 'nullable|integer|min:1|max:999',
            'puntos'      => 'required|integer|min:0|max:9999',
        ]);

        $exists = SistemaPuntajeFecha::where('fecha_id', $fecha->id)
            ->where('tipo_sesion', $request->tipo_sesion)
            ->where('posicion', $request->posicion)
            ->exists();

        if ($exists) {
            return back()->with('error', 'Ya existe una fila para ese tipo de sesión y posición.');
        }

        SistemaPuntajeFecha::create([
            'fecha_id'    => $fecha->id,
            'tipo_sesion' => $request->tipo_sesion,
            'posicion'    => $request->posicion,
            'puntos'      => $request->puntos,
        ]);

        // Sincronizar puntos de la fecha
        (new \App\Services\StandingsService())->syncFechaPuntos($fecha);

        return back()->with('success', 'Fila de puntaje agregada.');
    }

    /**
     * Update a single scoring row for a fecha.
     */
    public function updateScoringFecha(Request $request, Fecha $fecha, SistemaPuntajeFecha $sistemaPuntajeFecha)
    {
        $request->validate(['puntos' => 'required|integer|min:0|max:9999']);
        $sistemaPuntajeFecha->update(['puntos' => $request->puntos]);

        // Sincronizar puntos de la fecha
        (new \App\Services\StandingsService())->syncFechaPuntos($fecha);

        return back()->with('success', 'Puntaje actualizado.');
    }

    /**
     * Delete a single scoring row for a fecha.
     */
    public function deleteScoringFecha(Fecha $fecha, SistemaPuntajeFecha $sistemaPuntajeFecha)
    {
        $sistemaPuntajeFecha->delete();

        // Sincronizar puntos de la fecha
        (new \App\Services\StandingsService())->syncFechaPuntos($fecha);

        return back()->with('success', 'Fila eliminada.');
    }

    /**
     * Remove ALL fecha scoring overrides (revert to campeonato defaults).
     */
    public function resetScoringFecha(Fecha $fecha)
    {
        SistemaPuntajeFecha::where('fecha_id', $fecha->id)->delete();

        // Sincronizar puntos de la fecha
        (new \App\Services\StandingsService())->syncFechaPuntos($fecha);

        return back()->with('success', 'Puntaje personalizado eliminado. Se usará el puntaje del campeonato.');
    }

    public function eliminarResultadosSesion(SesionDefinicion $sesion)
    {
        $sesion->resultados()->delete();

        return redirect()->route('admin.fechas.resultados', $sesion->fecha_id)
            ->with('success', 'Se han eliminado todos los resultados de la sesión ' . $sesion->tipoNombre . '.');
    }
}
