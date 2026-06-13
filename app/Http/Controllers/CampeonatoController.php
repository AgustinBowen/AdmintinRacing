<?php

namespace App\Http\Controllers;

use App\Models\Campeonato;
use App\Models\SistemaPuntaje;
use App\Services\StandingsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Traits\HasSearchAndPagination;
use Illuminate\Support\Facades\Redirect;
use App\Http\Requests\StoreCampeonatoRequest;
use App\Http\Requests\UpdateCampeonatoRequest;
use App\Http\Requests\UpdateCampeonatoScoringRequest;
use App\Http\Requests\AddCampeonatoScoringRequest;

class CampeonatoController extends Controller
{
    use HasSearchAndPagination;

    public function index(Request $request)
    {
        $this->setupPagination();
        $query = Campeonato::query()->where('categoria_id', session('categoria_id'));
        $searchFields = ['nombre', 'anio'];
        $this->applySearch($query, $request, $searchFields);
        $columns = [
            ['field' => 'nombre', 'label' => 'Nombre', 'type' => 'text'],
            ['field' => 'anio',   'label' => 'Año',    'type' => 'badge', 'color' => 'primary'],
        ];
        $config = ['orderBy' => 'nombre', 'orderDirection' => 'asc', 'nameField' => 'nombre'];
        $result = $this->handleIndexResponse($request, $query, $columns, 'admin.campeonatos', $config);
        if ($request->ajax()) return $result;
        $campeonatos = $result;
        return view('admin.campeonatos.index', compact('campeonatos'));
    }

    public function create()
    {
        return view('admin.campeonatos.create');
    }

    public function store(StoreCampeonatoRequest $request)
    {
        $validated = $request->validated();
        $validated['categoria_id'] = session('categoria_id');
        $campeonato = Campeonato::create($validated);

        // Seed default scoring
        StandingsService::seedDefaultScoring($campeonato->id);

        return redirect()->route('admin.campeonatos.index')
            ->with('success', 'Campeonato creado exitosamente.');
    }

    public function show(Campeonato $campeonato)
    {
        $campeonato->load([
            'fechas' => fn($q) => $q->orderBy('fecha_desde'),
            'fechas.circuito',
        ]);
        $campeonato->fechas->each(fn($f, $i) => $f->numero_fecha = $i + 1);
        return view('admin.campeonatos.show', compact('campeonato'));
    }

    public function edit(Campeonato $campeonato)
    {
        return view('admin.campeonatos.edit', compact('campeonato'));
    }

    public function update(UpdateCampeonatoRequest $request, Campeonato $campeonato)
    {
        $validated = $request->validated();
        $campeonato->update($validated);
        return redirect()->route('admin.campeonatos.index')
            ->with('success', 'Campeonato actualizado exitosamente.');
    }

    public function destroy(Campeonato $campeonato)
    {
        $campeonato->delete();
        return redirect()->route('admin.campeonatos.index')
            ->with('success', 'Campeonato eliminado exitosamente.');
    }

    /**
     * Show the championship standings table.
     */
    public function standings(Campeonato $campeonato, StandingsService $service)
    {
        // Seed default scoring if none configured yet
        StandingsService::seedDefaultScoring($campeonato->id);

        $campeonato->load(['fechas' => fn($q) => $q->orderBy('fecha_desde')]);

        $standings = $service->calcular($campeonato);
        $fechas    = $campeonato->fechas;

        return view('admin.campeonatos.standings', compact('campeonato', 'standings', 'fechas'));
    }

    /**
     * Show the scoring system management page.
     */
    public function scoring(Campeonato $campeonato)
    {
        StandingsService::seedDefaultScoring($campeonato->id);

        $scoring = SistemaPuntaje::where('campeonato_id', $campeonato->id)
            ->orderBy('tipo_sesion')
            ->orderBy('posicion')
            ->get()
            ->groupBy('tipo_sesion');

        return view('admin.campeonatos.scoring', compact('campeonato', 'scoring'));
    }

    /**
     * Update a single scoring row's points value.
     */
    public function updateScoring(UpdateCampeonatoScoringRequest $request, Campeonato $campeonato, SistemaPuntaje $sistemaPuntaje)
    {
        $sistemaPuntaje->update(['puntos' => $request->validated()['puntos']]);
        
        // Sincronizar todo al cambiar reglas
        (new \App\Services\StandingsService())->sincronizar($campeonato);

        return back()->with('success', 'Puntaje actualizado y posiciones recalculadas.');
    }

    /**
     * Add a new position row to the scoring table.
     */
    public function addScoring(AddCampeonatoScoringRequest $request, Campeonato $campeonato)
    {
        $validated = $request->validated();

        $exists = SistemaPuntaje::where('campeonato_id', $campeonato->id)
            ->where('tipo_sesion', $request->tipo_sesion)
            ->where('posicion', $request->posicion)
            ->exists();

        if ($exists) {
            return back()->with('error', 'Ya existe una fila para ese tipo de sesión y posición.');
        }

        SistemaPuntaje::create([
            'campeonato_id' => $campeonato->id,
            'tipo_sesion'   => $request->tipo_sesion,
            'posicion'      => $request->posicion,
            'puntos'        => $validated['puntos'],
        ]);

        // Sincronizar todo al cambiar reglas
        (new \App\Services\StandingsService())->sincronizar($campeonato);

        return back()->with('success', 'Fila de puntaje agregada y posiciones recalculadas.');
    }

    /**
     * Delete a single scoring row.
     */
    public function deleteScoring(Campeonato $campeonato, SistemaPuntaje $sistemaPuntaje)
    {
        $sistemaPuntaje->delete();
        
        // Sincronizar todo al cambiar reglas
        (new \App\Services\StandingsService())->sincronizar($campeonato);

        return back()->with('success', 'Fila eliminada y posiciones recalculadas.');
    }

    /**
     * Reset scoring to defaults.
     */
    public function resetScoring(Campeonato $campeonato)
    {
        SistemaPuntaje::where('campeonato_id', $campeonato->id)->delete();
        StandingsService::seedDefaultScoring($campeonato->id);
        
        // Sincronizar todo al cambiar reglas
        (new \App\Services\StandingsService())->sincronizar($campeonato);

        return back()->with('success', 'Puntaje restablecido a predeterminados y posiciones recalculadas.');
    }

    /**
     * Bulk update all scoring for a championship.
     */
    public function bulkScoring(Request $request, Campeonato $campeonato)
    {
        $request->validate([
            'scoring' => 'array',
            'scoring.*' => 'array',
        ]);

        \Illuminate\Support\Facades\DB::transaction(function () use ($request, $campeonato) {
            SistemaPuntaje::where('campeonato_id', $campeonato->id)->delete();

            if ($request->has('scoring')) {
                foreach ($request->scoring as $tipo => $rows) {
                    foreach ($rows as $row) {
                        if (isset($row['posicion']) && isset($row['puntos']) && is_numeric($row['posicion']) && is_numeric($row['puntos'])) {
                            SistemaPuntaje::create([
                                'campeonato_id' => $campeonato->id,
                                'tipo_sesion' => $tipo,
                                'posicion' => (int)$row['posicion'],
                                'puntos' => (int)$row['puntos'],
                            ]);
                        }
                    }
                }
            }
        });

        // Sincronizar todo al cambiar reglas
        (new \App\Services\StandingsService())->sincronizar($campeonato);

        return back()->with('success', 'Sistema de puntaje actualizado y posiciones recalculadas.');
    }

    /**
     * Sync dynamic points to the database (resultado_sesion and posiciones_campeonato)
     */
    public function syncStandings(Campeonato $campeonato, StandingsService $service)
    {
        try {
            $service->sincronizar($campeonato);
            Session::flash('success', 'Los puntos han sido sincronizados y guardados exitosamente.');
        } catch (\Exception $e) {
            Session::flash('error', 'Error al sincronizar: ' . $e->getMessage());
        }

        return Redirect::back();
    }
}
