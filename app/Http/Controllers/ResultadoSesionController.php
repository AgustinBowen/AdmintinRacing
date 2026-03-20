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
                'key' => 'fecha_id',
                'type' => 'select',
                'field' => 'sesion.fecha_id',
                'placeholder' => 'Todas las Fechas',
                'options' => \App\Models\Fecha::orderBy('nombre')->pluck('nombre', 'id')->toArray()
            ],
            [
                'key' => 'piloto_id',
                'type' => 'select',
                'field' => 'piloto_id',
                'placeholder' => 'Todos los Pilotos',
                'options' => \App\Models\Piloto::orderBy('nombre')->pluck('nombre', 'id')->toArray()
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
        $sesiones = SesionDefinicion::with('fecha')
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('admin.resultados.import', compact('sesiones'));
    }

    public function importPreview(Request $request)
    {
        $sesion_id = $request->input('sesion_id');
        $resultados_json = json_decode($request->input('resultados_json'), true) ?? [];

        $sesion = SesionDefinicion::with('fecha.campeonato')->findOrFail($sesion_id);
        
        // Obtener todos los pilotos para el dropdown
        $pilotos = Piloto::orderBy('nombre')->get();
        $campeonatos = \App\Models\Campeonato::orderBy('anio', 'desc')->get();
        
        // Intentar autocompletar el piloto usando el nombre extraído (muy básico)
        $nombresPilotos = $pilotos->pluck('id', 'nombre')->mapWithKeys(function ($item, $key) {
            return [strtolower(trim($key)) => $item];
        })->toArray();

        foreach ($resultados_json as &$row) {
            $nombreScan = strtolower(trim($row['nombre'] ?? ''));
            if (empty($nombreScan)) {
                $row['piloto_id_match'] = null;
                continue;
            }

            // 1. Búsqueda exacta
            if (isset($nombresPilotos[$nombreScan])) {
                $row['piloto_id_match'] = $nombresPilotos[$nombreScan];
            } else {
                // 2. Búsqueda estricta por partes (Strict Intersection)
                // El nombre escaneado "Franco Smith" NO debe coincidir con "Franco Rossi"
                $row['piloto_id_match'] = null;
                $parts = array_filter(explode(' ', $nombreScan), fn($p) => strlen($p) > 2);
                
                if (count($parts) > 0) {
                    foreach ($nombresPilotos as $dbName => $id) {
                        $allPartsExist = true;
                        foreach ($parts as $p) {
                            if (!str_contains($dbName, $p)) {
                                $allPartsExist = false;
                                break;
                            }
                        }

                        if ($allPartsExist) {
                            // Si encontramos una coincidencia donde TODAS las partes existen en el nombre de la DB
                            $row['piloto_id_match'] = $id;
                            break; 
                        }
                    }
                }
            }
        }

        return view('admin.resultados.import-preview', compact('sesion', 'resultados_json', 'pilotos', 'campeonatos'));
    }

    public function storeImport(Request $request)
    {
        $sesion_id = $request->input('sesion_id');
        $items = $request->input('items', []);

        if (empty($items)) {
            return Redirect::route('admin.resultados.import.form')->with('error', 'No se enviaron datos para importar.');
        }

        $guardados = 0;
        foreach ($items as $item) {
            if (empty($item['piloto_id'])) {
                continue; // Ignorar si no seleccionaron piloto
            }

            // Convertir tiempos a decimal (segundos) o guardarlos como vienen si la DB los maneja como decimal.
            // La BD tiene 'tiempo_total', 'mejor_tiempo', 'sector_1' como decimal. 
            // Ojo: "1:16.389" no es decimal. Hay que parsearlo a segundos.
            $parseTime = function($timeStr) {
                if (empty($timeStr)) return null;
                if (str_contains($timeStr, ':')) {
                    $parts = explode(':', $timeStr);
                    return ($parts[0] * 60) + (float)$parts[1];
                }
                return (float)$timeStr;
            };

            ResultadoSesion::updateOrCreate([
                'sesion_id' => $sesion_id,
                'piloto_id' => $item['piloto_id']
            ], [
                'posicion' => (isset($item['posicion']) && in_array(strtoupper(trim($item['posicion'])), ['NT', 'EX'])) ? null : ($item['posicion'] ?? null),
                'vueltas' => $item['vueltas'] ?? null,
                'tiempo_total' => $parseTime($item['tiempo_total'] ?? null),
                'mejor_tiempo' => $parseTime($item['mejor_tiempo'] ?? null),
                'diferencia_primero' => $parseTime($item['diferencia'] ?? null),
                'sector_1' => $parseTime($item['sector_1'] ?? null),
                'sector_2' => $parseTime($item['sector_2'] ?? null),
                'sector_3' => $parseTime($item['sector_3'] ?? null),
                'excluido' => (isset($item['posicion']) && strtoupper(trim($item['posicion'])) === 'EX') ? true : false,
            ]);
            $guardados++;
        }

        $sesion = SesionDefinicion::find($sesion_id);
        
        // Sincronizar puntos de la fecha después de la importación
        if ($sesion) {
            (new \App\Services\StandingsService())->syncFechaPuntos($sesion->fecha);
        }

        Session::flash('success', "Se importaron/actualizaron $guardados resultados exitosamente.");
        return Redirect::route('admin.resultados.index', ['sesion_tipo' => $sesion->tipo]);
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

        $resultado = ResultadoSesion::create($validated);
        
        // Actualizar totales del campeonato (Ranking) sin sobreescribir puntos individuales
        (new \App\Services\StandingsService())->syncChampionshipTotals($resultado->sesion->fecha->campeonato);

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
        
        // Actualizar totales del campeonato (Ranking) sin sobreescribir puntos individuales
        (new \App\Services\StandingsService())->syncChampionshipTotals($resultado->sesion->fecha->campeonato);

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
