<?php

namespace App\Http\Controllers;

use App\Models\Campeonato;
use Illuminate\Http\Request;
use App\Traits\HasSearchAndPagination;

class CampeonatoController extends Controller
{
    use HasSearchAndPagination;

    public function index(Request $request)
    {
        // Configurar paginación
        $this->setupPagination();

        // Crear consulta base
        $query = Campeonato::query();

        // Aplicar búsqueda
        $searchFields = ['nombre', 'anio','fechas']; // Campos en los que buscar
        $this->applySearch($query, $request, $searchFields);

        $fechasCount = $query->withCount('fechas')->get()->pluck('fechas_count', 'id');

        // Definir columnas de la tabla
        $columns = [
            ['field' => 'nombre', 'label' => 'Nombre', 'type' => 'text'],
            ['field' => 'anio', 'label' => 'Año', 'type' => 'badge', 'color' => 'primary'],
            ['field' => 'fechas_count', 'label' => 'Fechas', 'type' => 'badge', 'color' => 'primary'],
        ];

        // Configuración específica
        $config = [
            'orderBy' => 'nombre',
            'orderDirection' => 'asc',
            'nameField' => 'nombre'
        ];

        // Manejar respuesta
        $result = $this->handleIndexResponse($request, $query, $columns, 'admin.campeonatos', $config);

        // Si es AJAX, ya se devolvió la respuesta
        if ($request->ajax()) {
            return $result;
        }

        // Si no es AJAX, devolver la vista completa
        $campeonatos = $result;
        return view('admin.campeonatos.index', compact('campeonatos'));
    }

    public function create()
    {
        return view('admin.campeonatos.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'anio' => 'required|integer|min:1900|max:' . (date('Y') + 10),
        ]);

        Campeonato::create($validated);

        return redirect()->route('admin.campeonatos.index')
            ->with('success', 'Campeonato creado exitosamente.');
    }

    public function show(Campeonato $campeonato)
    {
        $campeonato->load([
            'fechas' => function ($query) {
                $query->orderBy('fecha_desde', 'asc'); 
            },
            'fechas.circuito', 
        ]);

        $campeonato->fechas->each(function ($fecha, $index) {
            $fecha->numero_fecha = $index + 1;
        });

        return view('admin.campeonatos.show', compact('campeonato'));
    }

    public function edit(Campeonato $campeonato)
    {
        return view('admin.campeonatos.edit', compact('campeonato'));
    }

    public function update(Request $request, Campeonato $campeonato)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'anio' => 'required|integer|min:1900|max:' . (date('Y') + 10),
        ]);

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
}
