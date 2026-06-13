<?php

namespace App\Http\Controllers;

use App\Models\Circuito;
use App\Models\SesionDefinicion;
use App\Models\Fecha;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use App\Traits\HasSearchAndPagination;
use App\Http\Requests\StoreSesionDefinicionRequest;
use App\Http\Requests\UpdateSesionDefinicionRequest;

class SesionDefinicionController extends Controller
{
    use HasSearchAndPagination;

    public function index(Request $request)
    {
        // Configurar paginación
        $this->setupPagination();

        // Crear consulta base
        $query = SesionDefinicion::query()->whereHas('fecha', function($q) {
            $q->where('campeonato_id', session('campeonato_id'));
        });

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
                    return Fecha::where('campeonato_id', session('campeonato_id'))
                        ->whereHas('sesiones') // Asumiendo que tienes esta relación
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
    public function create(Request $request)
    {
        $fechaId = $request->query('fecha_id');
        $fechas = Fecha::where('campeonato_id', session('campeonato_id'))->orderBy('nombre')->get();
        $tipos = [];
        foreach (SesionDefinicion::TIPOS as $value => $label) {
            $tipos[] = ['value' => $value, 'label' => $label];
        }

        $cancelRoute = $fechaId ? route('admin.fechas.show', $fechaId) : route('admin.dashboard');
        return view('admin.sesiones.create', compact('fechas', 'tipos', 'fechaId', 'cancelRoute'));
    }

    /**
     * Store a newly created session in storage.
     */
    public function store(StoreSesionDefinicionRequest $request)
    {
        $validated = $request->validated();

        SesionDefinicion::create($validated);

        Session::flash('success', 'Sesión creada exitosamente.');
        return Redirect::route('admin.fechas.show', $validated['fecha_id']);
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
        $fechas = Fecha::where('campeonato_id', session('campeonato_id'))->orderBy('nombre')->get();
        $tipos = [];
        foreach (SesionDefinicion::TIPOS as $value => $label) {
            $tipos[] = ['value' => $value, 'label' => $label];
        }

        $cancelRoute = route('admin.fechas.show', $sesion->fecha_id);
        return view('admin.sesiones.edit', compact('sesion', 'fechas', 'tipos', 'cancelRoute'));
    }

    /**
     * Update the specified session in storage.
     */
    public function update(UpdateSesionDefinicionRequest $request, SesionDefinicion $sesion)
    {
        $validated = $request->validated();

        $sesion->update($validated);

        Session::flash('success', 'Sesión actualizada exitosamente.');
        return Redirect::route('admin.fechas.show', $sesion->fecha_id);
    }

    /**
     * Remove the specified session from storage.
     */
    public function destroy(SesionDefinicion $sesion)
    {
        $fechaId = $sesion->fecha_id;
        $sesion->delete();
        Session::flash('success', 'Sesión eliminada exitosamente.');
        return Redirect::route('admin.fechas.show', $fechaId);
    }
}
