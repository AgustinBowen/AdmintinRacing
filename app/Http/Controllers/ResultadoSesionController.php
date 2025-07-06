<?php

namespace App\Http\Controllers;

use App\Models\ResultadoSesion;
use App\Models\SesionDefinicion;
use App\Models\Piloto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use App\Traits\HasSearchAndPagination;
use App\Traits\SearchableSelectTrait;
use Illuminate\Validation\Rule;

class ResultadoSesionController extends Controller
{
    use HasSearchAndPagination;
    use SearchableSelectTrait;

    public function index(Request $request)
    {
        // Configurar paginación
        $this->setupPagination();

        // Crear consulta base
        $query = ResultadoSesion::query();

        // Aplicar búsqueda
        $searchFields = ['piloto.nombre', 'sesion.nombre', 'sesion.fecha.nombre']; // Campos en los que buscar
        $this->applySearch($query, $request, $searchFields);


        // Definir filtros dinámicos
        $filters = [
            [
                'key' => 'sesion_tipo',
                'type' => 'select',
                'field' => 'sesion.tipo',
                'placeholder' => 'Todos los tipos',
                'options' => SesionDefinicion::TIPOS
            ],
            [
                'key' => 'posicion_range',
                'type' => 'select',
                'field' => 'posicion',
                'placeholder' => 'Todas las posiciones',
                'options' => [
                    '1-3' => 'Podio (1-3)',
                    '4-10' => 'Top 10 (4-10)',
                    '11-20' => 'Puntos (11-20)',
                    '21+' => 'Fuera de puntos (21+)'
                ],
                'callback' => function ($query, $value) {
                    switch ($value) {
                        case '1-3':
                            $query->whereBetween('posicion', [1, 3]);
                            break;
                        case '4-10':
                            $query->whereBetween('posicion', [4, 10]);
                            break;
                        case '11-20':
                            $query->whereBetween('posicion', [11, 20]);
                            break;
                        case '21+':
                            $query->where('posicion', '>', 20);
                            break;
                    }
                }
            ],
        ];

        // Definir columnas de la tabla
        $columns = [
            ['label' => 'Sesión', 'field' => 'sesion.tipo',  'type' => 'badge'],
            ['label' => 'Piloto', 'field' => 'piloto.nombre',  'type' => 'text'],
            ['label' => 'Posición', 'field' => 'posicion', 'type' => 'text'],
            ['label' => 'Fecha', 'field' => 'sesion.fecha.nombre', 'type' => 'badge']
        ];

        // Configuración específica
        $config = [
            'orderBy' => 'posicion',
            'orderDirection' => 'asc',
            'nameField' => 'posicion',
            'filters' => $filters,
        ];

        // Manejar respuesta
        $result = $this->handleIndexResponse($request, $query, $columns, 'admin.resultados', $config);

        // Si es AJAX, ya se devolvió la respuesta
        if ($request->ajax()) {
            return $result;
        }

        // Si no es AJAX, devolver la vista completa
        $resultados = $result;
        $filterOptions = $this->getFilterOptions($filters);
        return view('admin.resultados.index', compact('resultados', 'filters', 'filterOptions'));
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

    // Endpoint para búsqueda de sesiones
    public function searchSesiones(Request $request)
    {
        return $this->searchSelect(
            $request,
            SesionDefinicion::class,
            ['tipo', 'fecha.nombre'], // Campos de búsqueda
            ['fecha'], // Relaciones a cargar
            function ($query) {
                // Query personalizada si necesitas filtros adicionales
                return $query->orderBy('created_at', 'desc');
            }
        );
    }

    // Endpoint para búsqueda de pilotos
    public function searchPilotos(Request $request)
    {
        return $this->searchSelect(
            $request,
            Piloto::class,
            ['nombre'], // Campos de búsqueda
            [], // Sin relaciones
            function ($query) {
                return $query->orderBy('nombre', 'asc');
            }
        );
    }

    // Sobrescribir el método para formatear texto personalizado
    protected function formatSelectText($item)
    {
        // Para SesionDefinicion
        if ($item instanceof SesionDefinicion) {
            return $item->tipo . ' - ' . ($item->fecha->nombre ?? 'Sin fecha');
        }

        // Para Piloto
        if ($item instanceof Piloto) {
            return $item->nombre;
        }

        return (string) $item;
    }

    /**
     * Store a newly created session result in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'sesion_id' => 'required|exists:sesiones_definicion,id',
            'piloto_id' => [
                'required',
                'exists:pilotos,id',
                Rule::unique('resultados_sesion')->where(function ($query) use ($request) {
                    return $query->where('sesion_id', $request->sesion_id);
                }),
            ],
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
        ], [
            'sesion_id.required' => 'Debe seleccionar una sesión.',
            'sesion_id.exists' => 'La sesión seleccionada no es válida.',

            'piloto_id.required' => 'Debe seleccionar un piloto.',
            'piloto_id.exists' => 'El piloto seleccionado no es válido.',
            'piloto_id.unique' => 'Ya existe un resultado para este piloto en esta sesión.',

            'posicion.required' => 'Debe ingresar la posición del piloto.',
            'posicion.integer' => 'La posición debe ser un número entero.',
            'posicion.min' => 'La posición mínima permitida es 1.',

            'puntos.integer' => 'Los puntos deben ser un número entero.',
            'puntos.min' => 'Los puntos no pueden ser negativos.',

            'vueltas.integer' => 'La cantidad de vueltas debe ser un número entero.',
            'vueltas.min' => 'Las vueltas no pueden ser negativas.',

            'tiempo_total.numeric' => 'El tiempo total debe ser un número.',
            'tiempo_total.min' => 'El tiempo total no puede ser negativo.',

            'mejor_tiempo.numeric' => 'El mejor tiempo debe ser un número.',
            'mejor_tiempo.min' => 'El mejor tiempo no puede ser negativo.',

            'diferencia_primero.numeric' => 'La diferencia con el primero debe ser un número.',
            'diferencia_primero.min' => 'La diferencia no puede ser negativa.',

            'sector_1.numeric' => 'El tiempo del sector 1 debe ser un número.',
            'sector_1.min' => 'El tiempo del sector 1 no puede ser negativo.',

            'sector_2.numeric' => 'El tiempo del sector 2 debe ser un número.',
            'sector_2.min' => 'El tiempo del sector 2 no puede ser negativo.',

            'sector_3.numeric' => 'El tiempo del sector 3 debe ser un número.',
            'sector_3.min' => 'El tiempo del sector 3 no puede ser negativo.',

            'excluido.boolean' => 'El campo "excluido" debe ser verdadero o falso.',
            'presente.boolean' => 'El campo "presente" debe ser verdadero o falso.',

            'observaciones.string' => 'Las observaciones deben ser un texto válido.',
            'observaciones.max' => 'Las observaciones no pueden superar los 1000 caracteres.',
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
