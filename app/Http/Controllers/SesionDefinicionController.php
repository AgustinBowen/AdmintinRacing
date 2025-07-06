<?php

namespace App\Http\Controllers;

use App\Models\Circuito;
use App\Models\SesionDefinicion;
use App\Models\Fecha;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use App\Traits\HasSearchAndPagination;

class SesionDefinicionController extends Controller
{
    use HasSearchAndPagination;

    public function index(Request $request)
    {
        // Configurar paginación
        $this->setupPagination();

        // Crear consulta base
        $query = SesionDefinicion::query();

        // Aplicar búsqueda
        $searchFields = ['fecha.nombre']; // Campos en los que buscar
        $this->applySearch($query, $request, $searchFields);

        // Definir filtros dinámicos
        $filters = [
            [
                'key' => 'sesion_tipo',
                'type' => 'select',
                'field' => 'tipo',
                'placeholder' => 'Todos los tipos',
                'options' => SesionDefinicion::TIPOS
            ],
            [
                'key' => 'fecha_sesion',
                'type' => 'date',
                'field' => 'fecha_sesion',
                'placeholder' => 'Fecha de la sesión'
            ],
            [
                'key' => 'fecha',
                'type' => 'select',
                'field' => 'fecha.nombre', // para filtrar por relación
                'placeholder' => 'Todas las fechas',
                'options' => function () {
                    // Obtener las fechas que realmente tienen sesiones definidas
                    return Fecha::whereHas('sesiones') // Asumiendo que tienes esta relación
                        ->orderBy('nombre')
                        ->pluck('nombre', 'nombre') // Usar nombre como key y value
                        ->toArray();
                }
            ],
            [
                'key' => 'circuito',
                'type' => 'select',
                'field' => 'fecha.circuito_id', // para filtrar por relación
                'placeholder' => 'Todos los circuitos',
                'options' => Circuito::distinct('nombre')
                    ->orderBy('nombre', 'desc')
                    ->pluck('nombre', 'id')
                    ->toArray()
            ]
        ];

        // Definir columnas de la tabla
        $columns = [
            ['label' => 'Tipo de Sesión', 'field' => 'tipo', 'type' => 'badge'],
            ['label' => 'Fecha de la sesión', 'field' => 'fecha_sesion', 'type' => 'date'],
            ['label' => 'Fecha correspondiente', 'field' => 'fecha.nombre', 'type' => 'text'],
        ];

        // Configuración específica
        $config = [
            'orderBy' => 'fecha_sesion',
            'orderDirection' => 'asc',
            'nameField' => 'nombre',
            'filters' => $filters,
        ];

        // Manejar respuesta
        $result = $this->handleIndexResponse($request, $query, $columns, 'admin.sesiones', $config);

        // Si es AJAX, ya se devolvió la respuesta
        if ($request->ajax()) {
            return $result;
        }

        // Si no es AJAX, devolver la vista completa
        $sesiones = $result;
        $filterOptions = $this->getFilterOptions($filters);
        return view('admin.sesiones.index', compact('sesiones', 'filters', 'filterOptions'));
    }

    /**
     * Show the form for creating a new session.
     */
    public function create()
    {
        $fechas = Fecha::all();
        $fechas = Fecha::pluck('nombre', 'id');
        $tipos = SesionDefinicion::TIPOS;

        return view('admin.sesiones.create', compact('fechas', 'tipos'));
    }

    /**
     * Store a newly created session in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'fecha_id' => 'required|exists:fechas,id',
            'fecha_sesion' => 'required|date',
            'tipo' => 'required|in:' . implode(',', array_keys(SesionDefinicion::TIPOS)),
        ]);

        SesionDefinicion::create($validated);

        Session::flash('success', 'Sesión creada exitosamente.');
        return Redirect::route('admin.sesiones.index');
    }

    /**
     * Display the specified session.
     */
    public function show(SesionDefinicion $sesion)
    {
        $sesion->load('horarios');
        return view('admin.sesiones.show', compact('sesion'));
    }

    /**
     * Show the form for editing the specified session.
     */
    public function edit(SesionDefinicion $sesion)
    {
        $fechas = Fecha::all();
        $fechas = Fecha::pluck('nombre', 'id');
        $tipos = SesionDefinicion::TIPOS;

        return view('admin.sesiones.edit', compact('sesion', 'fechas', 'tipos'));
    }

    /**
     * Update the specified session in storage.
     */
    public function update(Request $request, SesionDefinicion $sesion)
    {
        $validated = $request->validate([
            'fecha_id' => 'required|exists:fechas,id',
            'fecha_sesion' => 'required|date',
            'tipo' => 'required|in:' . implode(',', array_keys(SesionDefinicion::TIPOS)),
        ]);

        $sesion->update($validated);

        Session::flash('success', 'Sesión actualizada exitosamente.');
        return Redirect::route('admin.sesiones.index');
    }

    /**
     * Remove the specified session from storage.
     */
    public function destroy(SesionDefinicion $sesion)
    {
        $sesion->delete();
        Session::flash('success', 'Sesión eliminada exitosamente.');
        return Redirect::route('admin.sesiones.index');
    }
}
