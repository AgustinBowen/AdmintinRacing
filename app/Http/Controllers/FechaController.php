<?php

namespace App\Http\Controllers;

use App\Models\Fecha;
use App\Models\Campeonato;
use App\Models\Circuito;
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
        $searchFields = ['nombre', 'circuito', 'campeonato']; // Campos en los que buscar
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
        $campeonatos = Campeonato::pluck('nombre', 'id');
        $circuitos = Circuito::orderBy('nombre')->get();
        $circuitos = Circuito::pluck('nombre', 'id');

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
        ]);

        Fecha::create($validated);

        return redirect()->route('admin.fechas.index')
            ->with('success', 'Fecha creada exitosamente.');
    }

    public function show(Fecha $fecha)
    {
        $fecha->load(['campeonato', 'circuito']);

        return view('admin.fechas.show', compact('fecha'));
    }

    public function edit(Fecha $fecha)
    {
        $campeonatos = Campeonato::orderBy('anio', 'desc')->get();
        $campeonatos = Campeonato::pluck('nombre', 'id');
        $circuitos = Circuito::orderBy('nombre')->get();
        $circuitos = Circuito::pluck('nombre', 'id');

        return view('admin.fechas.edit', compact('fecha', 'campeonatos', 'circuitos'));
    }

    public function update(Request $request, Fecha $fecha)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'fecha' => 'required|date',
            'campeonato_id' => 'required|exists:campeonatos,id',
            'circuito_id' => 'required|exists:circuitos,id',
            'numero_fecha' => 'required|integer|min:1',
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
}
