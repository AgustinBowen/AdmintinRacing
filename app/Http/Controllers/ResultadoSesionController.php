<?php

namespace App\Http\Controllers;

use App\Models\ResultadoSesion;
use App\Models\SesionDefinicion;
use App\Models\Piloto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use App\Traits\HasSearchAndPagination;

class ResultadoSesionController extends Controller
{
    use HasSearchAndPagination;

    public function index(Request $request)
    {
        // Configurar paginación
        $this->setupPagination();

        // Crear consulta base
        $query = ResultadoSesion::query();

        // Aplicar búsqueda
        $searchFields = ['piloto.nombre', 'sesion.nombre', 'sesion.fecha.nombre']; // Campos en los que buscar
        $this->applySearch($query, $request, $searchFields);

        // Definir columnas de la tabla
        $columns = [
            ['label' => 'Sesión', 'field' => 'sesion.tipo',  'type' => 'badge'],
            ['label' => 'Piloto', 'field' => 'piloto.nombre',  'type' => 'text'],
            ['label' => 'Posición', 'field' => 'posicion', 'type' => 'text'],
            ['label' => 'Fecha', 'field' => 'sesion.fecha.nombre', 'type' => 'badge']
        ];

        // Configuración específica
        $config = [
            'orderBy' => 'nombre',
            'orderDirection' => 'asc',
            'nameField' => 'nombre'
        ];

        // Manejar respuesta
        $result = $this->handleIndexResponse($request, $query, $columns, 'admin.resultados', $config);

        // Si es AJAX, ya se devolvió la respuesta
        if ($request->ajax()) {
            return $result;
        }

        // Si no es AJAX, devolver la vista completa
        $resultados = $result;
        return view('admin.resultados.index', compact('resultados'));
    }

    /**
     * Show the form for creating a new session result.
     */
    public function create()
    {
        $sesiones = SesionDefinicion::with('fecha')
            ->get()
            ->mapWithKeys(function ($sesion) {
                return [
                    $sesion->id => $sesion->tipo . ' - ' . ($sesion->fecha->nombre ?? 'Sin fecha')
                ];
            });
        $pilotos = Piloto::pluck('nombre', 'id');

        return view('admin.resultados.create', compact('sesiones', 'pilotos'));
    }

    /**
     * Store a newly created session result in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'sesion_id' => 'required|exists:sesiones_definicion,id',
            'piloto_id' => 'required|exists:pilotos,id',
            'posicion' => 'required|integer|min:1',
            'puntos' => 'nullable|integer|min:0',
            'vueltas' => 'nullable|integer|min:0',
            'tiempo_total' => 'nullable|numeric|min:0',
            'mejor_tiempo' => 'nullable|numeric|min:0',
            'diferencia_primero' => 'nullable|numeric|min:0',
            'sector_1' => 'nullable|numeric|min:0',
            'sector_2' => 'nullable|numeric|min:0',
            'sector_3' => 'nullable|numeric|min:0',
            'excluido' => 'boolean',
            'presente' => 'boolean',
            'observaciones' => 'nullable|string|max:1000',
        ]);

        ResultadoSesion::create($validated);

        Session::flash('success', 'Resultado creado exitosamente.');
        return Redirect::route('admin.resultados.index');
    }

    /**
     * Display the specified session result.
     */
    public function show(ResultadoSesion $resultado)
    {
        $resultado->load('sesion', 'piloto');
        return view('admin.resultados.show', compact('resultado'));
    }

    /**
     * Show the form for editing the specified session result.
     */
    public function edit(ResultadoSesion $resultado)
    {
        $sesiones = SesionDefinicion::pluck('tipo', 'id');
        $pilotos = Piloto::pluck('nombre', 'id');

        return view('admin.resultados.edit', compact('resultado', 'sesiones', 'pilotos'));
    }

    /**
     * Update the specified session result in storage.
     */
    public function update(Request $request, ResultadoSesion $resultado)
    {
        $validated = $request->validate([
            'sesion_id' => 'required|exists:sesiones_definicion,id',
            'piloto_id' => 'required|exists:pilotos,id',
            'posicion' => 'required|integer|min:1',
            'puntos' => 'nullable|integer|min:0',
            'vueltas' => 'nullable|integer|min:0',
            'tiempo_total' => 'nullable|numeric|min:0',
            'mejor_tiempo' => 'nullable|numeric|min:0',
            'diferencia_primero' => 'nullable|numeric|min:0',
            'sector_1' => 'nullable|numeric|min:0',
            'sector_2' => 'nullable|numeric|min:0',
            'sector_3' => 'nullable|numeric|min:0',
            'excluido' => 'boolean',
            'presente' => 'boolean',
            'observaciones' => 'nullable|string|max:1000',
        ]);

        $resultado->update($validated);

        Session::flash('success', 'Resultado actualizado exitosamente.');
        return Redirect::route('admin.resultados.index');
    }

    /**
     * Remove the specified session result from storage.
     */
    public function destroy(ResultadoSesion $resultado)
    {
        $resultado->delete();
        Session::flash('success', 'Resultado eliminado exitosamente.');
        return Redirect::route('admin.resultados.index');
    }
}
