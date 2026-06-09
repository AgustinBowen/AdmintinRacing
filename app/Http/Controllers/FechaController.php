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
use App\Services\CronogramaService;
use App\Services\AcumuladosService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Traits\HasSearchAndPagination;
use App\Http\Requests\StoreFechaRequest;
use App\Http\Requests\UpdateFechaRequest;
use App\Http\Requests\AddFechaScoringRequest;
use App\Http\Requests\UpdateFechaScoringRequest;

class FechaController extends Controller
{
    use HasSearchAndPagination;

    public function index(Request $request)
    {
        // Configurar paginación
        $this->setupPagination();

        // Crear consulta base
        $query = Fecha::query()->where('campeonato_id', session('campeonato_id'));

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
        $campeonatos = Campeonato::where('categoria_id', session('categoria_id'))->orderBy('anio', 'desc')->get();
        $circuitos = Circuito::orderBy('nombre')->get();

        return view('admin.fechas.create', compact('campeonatos', 'circuitos'));
    }

    public function store(StoreFechaRequest $request, CronogramaService $cronogramaService)
    {
        $validated = $request->validated();

        $generarCronograma = $request->has('generar_cronograma') && $request->generar_cronograma;
        unset($validated['generar_cronograma']);

        $fecha = Fecha::create($validated);

        if ($generarCronograma) {
            $cronogramaService->generarCronogramaEstandar($fecha);
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
        $campeonatos = Campeonato::where('categoria_id', session('categoria_id'))->orderBy('anio', 'desc')->get();
        $circuitos = Circuito::orderBy('nombre')->get();

        return view('admin.fechas.edit', compact('fecha', 'campeonatos', 'circuitos'));
    }

    public function update(UpdateFechaRequest $request, Fecha $fecha)
    {
        $validated = $request->validated();

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
    public function generarSesiones(Fecha $fecha, CronogramaService $cronogramaService)
    {
        $cronogramaService->generarCronogramaEstandar($fecha);

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
     * Generar la sesión de resultados ACUMULADOS (mejor tiempo de cada piloto en entrenamientos)
     */
    public function generarAcumulados(Fecha $fecha, AcumuladosService $acumuladosService)
    {
        $success = $acumuladosService->generarAcumulados($fecha);

        if (!$success) {
            return redirect()->back()->with('error', 'No hay resultados de entrenamiento para generar los acumulados.');
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
    public function addScoringFecha(AddFechaScoringRequest $request, Fecha $fecha)
    {
        $validated = $request->validated();

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
            'puntos'      => $validated['puntos'],
        ]);

        // Sincronizar puntos de la fecha
        (new \App\Services\StandingsService())->syncFechaPuntos($fecha);

        return back()->with('success', 'Fila de puntaje agregada.');
    }

    /**
     * Update a single scoring row for a fecha.
     */
    public function updateScoringFecha(UpdateFechaScoringRequest $request, Fecha $fecha, SistemaPuntajeFecha $sistemaPuntajeFecha)
    {
        $sistemaPuntajeFecha->update(['puntos' => $request->validated()['puntos']]);

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
