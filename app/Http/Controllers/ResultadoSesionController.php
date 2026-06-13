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
use App\Http\Requests\StoreResultadoSesionRequest;
use App\Http\Requests\UpdateResultadoSesionRequest;

class ResultadoSesionController extends Controller
{
    use HasSearchAndPagination;
    use SearchableSelectTrait;

    public function index(Request $request)
    {
        // Configurar paginación
        $this->setupPagination();

        // Crear consulta base
        $query = ResultadoSesion::query()->whereHas('sesion.fecha', function($q) {
            $q->where('campeonato_id', session('campeonato_id'));
        });

        // Aplicar búsqueda
        $searchFields = ['piloto.nombre', 'sesion.nombre', 'sesion.fecha.nombre']; // Campos en los que buscar
        $this->applySearch($query, $request, $searchFields);


        // Definir filtros dinámicos
        $filters = [
            [
                'key' => 'fecha_id',
                'type' => 'select',
                'field' => 'sesion.fecha_id',
                'placeholder' => 'Todas las Fechas',
                'options' => \App\Models\Fecha::where('campeonato_id', session('campeonato_id'))->orderBy('nombre')->pluck('nombre', 'id')->toArray()
            ],
            [
                'key' => 'piloto_id',
                'type' => 'select',
                'field' => 'piloto_id',
                'placeholder' => 'Todos los Pilotos',
                'options' => \App\Models\Piloto::whereHas('campeonatos', function($q) {
                    $q->where('campeonatos.id', session('campeonato_id'));
                })->orderBy('nombre')->pluck('nombre', 'id')->toArray()
            ],
            [
                'key' => 'sesion_tipo',
                'type' => 'select',
                'field' => 'sesion.tipo',
                'placeholder' => 'Todos los tipos de Sesión',
                'options' => \App\Models\SesionDefinicion::TIPOS
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

    // ==========================================
    // IMPORTACIÓN POR OCR/PDF
    // ==========================================

    public function importForm()
    {
        $fechas = \App\Models\Fecha::with('campeonato')
            ->where('campeonato_id', session('campeonato_id'))
            ->orderBy('created_at', 'desc')
            ->get();
            
        $fechaIds = $fechas->pluck('id');

        $sesiones = SesionDefinicion::with('fecha')
            ->whereIn('fecha_id', $fechaIds)
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('admin.resultados.import', compact('sesiones', 'fechas'));
    }

    public function importPreview(Request $request, \App\Services\PilotMatchingService $matchingService)
    {
        $sesion_id = $request->input('sesion_id');
        $resultados_json = json_decode($request->input('resultados_json'), true) ?? [];

        $sesion = SesionDefinicion::with('fecha.campeonato')->findOrFail($sesion_id);
        
        // Obtener todos los pilotos para el dropdown
        $pilotos = Piloto::orderBy('nombre')->get();
        $campeonatos = \App\Models\Campeonato::where('categoria_id', session('categoria_id'))->orderBy('anio', 'desc')->get();
        
        $resultados_json = $matchingService->matchFromOcrData($resultados_json);

        return view('admin.resultados.import-preview', compact('sesion', 'resultados_json', 'pilotos', 'campeonatos'));
    }

    public function storeImport(Request $request, \App\Services\ResultImportService $importService)
    {
        $sesion_id = (string) $request->input('sesion_id');
        $items = $request->input('items', []);

        if (empty($items)) {
            return Redirect::route('admin.resultados.import.form')->with('error', 'No se enviaron datos para importar.');
        }

        $guardados = $importService->importResults($sesion_id, $items);

        $sesion = SesionDefinicion::find($sesion_id);
        
        Session::flash('success', "Se importaron/actualizaron $guardados resultados exitosamente.");
        return Redirect::route('admin.fechas.resultados', $sesion->fecha_id);
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
    public function store(StoreResultadoSesionRequest $request)
    {
        $validated = $request->validated();

        $resultado = ResultadoSesion::create($validated);
        
        // Actualizar totales del campeonato (Ranking) sin sobreescribir puntos individuales
        (new \App\Services\StandingsService())->syncChampionshipTotals($resultado->sesion->fecha->campeonato);

        Session::flash('success', 'Resultado creado exitosamente.');
        return Redirect::route('admin.fechas.resultados', $resultado->sesion->fecha_id);
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

        $cancelRoute = route('admin.fechas.resultados', $resultado->sesion->fecha_id);
        return view('admin.resultados.edit', compact('resultado', 'sesiones', 'pilotos', 'cancelRoute'));
    }

    /**
     * Update the specified session result in storage.
     */
    public function update(UpdateResultadoSesionRequest $request, ResultadoSesion $resultado)
    {
        $validated = $request->validated();

        $resultado->update($validated);
        
        // Actualizar totales del campeonato (Ranking) sin sobreescribir puntos individuales
        (new \App\Services\StandingsService())->syncChampionshipTotals($resultado->sesion->fecha->campeonato);

        Session::flash('success', 'Resultado actualizado exitosamente.');
        return Redirect::route('admin.fechas.resultados', $resultado->sesion->fecha_id);
    }

    /**
     * Remove the specified session result from storage.
     */
    public function destroy(ResultadoSesion $resultado)
    {
        $fechaId = $resultado->sesion->fecha_id;
        $resultado->delete();
        Session::flash('success', 'Resultado eliminado exitosamente.');
        return Redirect::route('admin.fechas.resultados', $fechaId);
    }
}
