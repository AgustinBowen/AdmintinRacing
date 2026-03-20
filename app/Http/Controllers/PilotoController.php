<?php

namespace App\Http\Controllers;

use App\Models\Piloto;
use Illuminate\Http\Request;
use App\Traits\HasSearchAndPagination;

class PilotoController extends Controller
{
    use HasSearchAndPagination;

    public function index(Request $request)
    {
        // Configurar paginación
        $this->setupPagination();

        // Filtro por Campeonato
        $campeonatoId = $request->input('campeonato_id');
        $campeonatos = \App\Models\Campeonato::orderBy('anio', 'desc')->get();
        
        $campeonatosOptions = ['none' => 'Pilotos sin Campeonato'] + $campeonatos->pluck('nombre', 'id')->toArray();

        // Definir columnas de la tabla
        $columns = [
            ['field' => 'nombre', 'label' => 'Nombre', 'type' => 'text'],
            ['field' => 'pais', 'label' => 'País', 'type' => 'badge', 'color' => 'primary'],
        ];

        // Si NO hay campeonato seleccionado de ninguna forma, no mostrar pilotos
        if (!$campeonatoId) {
            // Para AJAX, devolver HTML con estado "seleccionar filtro"
            if ($request->ajax()) {
                return '<div id="require-filter-state" class="text-center py-5">
                    <div class="mb-3">
                        <i class="fas fa-filter" style="font-size: 3rem; color: hsl(var(--muted-foreground)); opacity: 0.5;"></i>
                    </div>
                    <h6 class="mb-2" style="color: hsl(var(--foreground));">Seleccioná un campeonato</h6>
                    <p class="mb-0" style="color: hsl(var(--muted-foreground)); font-size: 0.875rem;">
                        Elegí un campeonato (o "Pilotos sin Campeonato") para ver los resultados
                    </p>
                </div>';
            }

            // Para carga normal, pasar datos vacíos con flag
            $pilotos = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10);
            return view('admin.pilotos.index', compact('pilotos', 'campeonatosOptions', 'campeonatoId', 'columns'))
                ->with('requireFilter', true);
        }

        // Crear consulta base con filtro de campeonato
        $query = Piloto::query();

        // Aplicar búsqueda de texto
        $searchFields = ['nombre', 'pais'];
        $this->applySearch($query, $request, $searchFields);

        if ($campeonatoId === 'none') {
            $query->doesntHave('campeonatos');
        } else {
            $query->whereHas('campeonatos', function ($q) use ($campeonatoId) {
                $q->where('campeonatos.id', $campeonatoId);
            });
            $query->with(['campeonatos' => function($q) use ($campeonatoId) {
                $q->where('campeonatos.id', $campeonatoId);
            }]);

            // Inyectar columna N° Auto
            array_splice($columns, 1, 0, [['field' => 'numero_auto_pivot', 'label' => 'N° Auto', 'type' => 'text']]);
        }

        // Configuración específica
        $config = [
            'orderBy' => 'nombre',
            'orderDirection' => 'asc',
            'nameField' => 'nombre'
        ];

        // Manejar respuesta
        $result = $this->handleIndexResponse($request, $query, $columns, 'admin.pilotos', $config);

        // Si es AJAX devolvemos
        if ($request->ajax()) {
            return $result;
        }

        // Si no es AJAX, devolver la vista completa
        $pilotos = $result;
        return view('admin.pilotos.index', compact('pilotos', 'campeonatosOptions', 'campeonatoId', 'columns'))
            ->with('requireFilter', false);
    }

    public function create(Request $request)
    {
        $campeonatos = \App\Models\Campeonato::orderBy('anio', 'desc')->get();
        $campeonatoId = $request->input('campeonato_id');
        return view('admin.pilotos.create', compact('campeonatos', 'campeonatoId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'pais' => 'nullable|string|max:100',
            'campeonato_id' => 'nullable|exists:campeonatos,id',
            'numero_auto' => 'nullable|numeric',
        ]);

        $nombre = ucwords(strtolower(trim($validated['nombre'])));

        $piloto = Piloto::create([
            'nombre' => $nombre,
            'pais' => $validated['pais'] ?? 'Argentina'
        ]);

        if (!empty($validated['campeonato_id'])) {
            $piloto->campeonatos()->syncWithoutDetaching([
                $validated['campeonato_id'] => [
                    'id' => \Illuminate\Support\Str::uuid()->toString(),
                    'numero_auto' => $validated['numero_auto'] ?? 0
                ]
            ]);
        }

        return redirect()->route('admin.pilotos.index', ['campeonato_id' => $validated['campeonato_id'] ?? null])
            ->with('success', 'Piloto creado exitosamente.');
    }

    public function show(Piloto $piloto)
    {
        $piloto;

        return view('admin.pilotos.show', compact('piloto'));
    }

    public function edit(Piloto $piloto)
    {
        $campeonatos = \App\Models\Campeonato::orderBy('anio', 'desc')->get();
        
        // Obtenemos el campeonato actual (el primero que encontremos para este piloto)
        $campeonatoActual = $piloto->campeonatos->first();
        $numeroAutoActual = $campeonatoActual?->pivot->numero_auto;

        return view('admin.pilotos.edit', compact('piloto', 'campeonatos', 'campeonatoActual', 'numeroAutoActual'));
    }

    public function update(Request $request, Piloto $piloto)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'pais' => 'nullable|string|max:100',
            'campeonato_id' => 'nullable|exists:campeonatos,id',
            'numero_auto' => 'nullable|integer',
        ]);

        // Validar que el número de auto no esté tomado por OTRO piloto en ese mismo campeonato
        if ($request->filled('campeonato_id') && $request->filled('numero_auto')) {
            $numeroTomado = \Illuminate\Support\Facades\DB::table('pilotos_campeonato')
                ->where('campeonato_id', $validated['campeonato_id'])
                ->where('numero_auto', $validated['numero_auto'])
                ->where('piloto_id', '!=', $piloto->id)
                ->exists();

            if ($numeroTomado) {
                return back()->withErrors(['numero_auto' => 'El número ' . $validated['numero_auto'] . ' ya está siendo usado por otro piloto en este campeonato.'])
                             ->withInput();
            }
        }

        $piloto->update([
            'nombre' => $validated['nombre'],
            'pais' => $validated['pais'],
        ]);

        if ($request->filled('campeonato_id')) {
            // Buscamos si ya existe la relación para no generar un nuevo UUID innecesariamente si usamos sync
            $pivot = $piloto->campeonatos()->where('campeonatos.id', $validated['campeonato_id'])->first();
            
            $piloto->campeonatos()->syncWithoutDetaching([
                $validated['campeonato_id'] => [
                    'id' => $pivot ? $pivot->pivot->id : \Illuminate\Support\Str::uuid()->toString(),
                    'numero_auto' => $validated['numero_auto'] ?? 0
                ]
            ]);
        }

        return redirect()->route('admin.pilotos.index', ['campeonato_id' => $request->campeonato_id])
            ->with('success', 'Piloto actualizado exitosamente.');
    }

    public function destroy(Piloto $piloto)
    {
        $piloto->delete();

        return redirect()->route('admin.pilotos.index')
            ->with('success', 'Piloto eliminado exitosamente.');
    }

    public function importForm()
    {
        $campeonatos = \App\Models\Campeonato::orderBy('anio', 'desc')->get();
        return view('admin.pilotos.import', compact('campeonatos'));
    }

    public function importPreview(Request $request)
    {
        $json = $request->input('pilotos_json');
        $campeonato_id = $request->input('campeonato_id');

        if (!$json) {
            return redirect()->route('admin.pilotos.import.form')
                ->withErrors(['pdf_file' => 'No se detectaron pilotos en el archivo PDF mediante JS.']);
        }

        $pilotos = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE || empty($pilotos)) {
             return redirect()->route('admin.pilotos.import.form')
                ->withErrors(['pdf_file' => 'Error analizando la estructura del PDF (JSON inválido).']);
        }

        return view('admin.pilotos.import-preview', compact('pilotos', 'campeonato_id'));
    }

    public function storeImport(Request $request)
    {
        $validated = $request->validate([
            'pilotos' => 'required|array',
            'pilotos.*.nombre' => 'required|string|max:255',
            'pilotos.*.pais' => 'required|string|max:100',
            'pilotos.*.auto' => 'required|numeric',
            'campeonato_id' => 'nullable|exists:campeonatos,id',
        ]);

        $campeonatoId = $validated['campeonato_id'];
        $processedCount = 0;

        foreach ($validated['pilotos'] as $p) {
            if (empty(trim($p['nombre']))) continue;

            $nombre = ucwords(strtolower(trim($p['nombre'])));

            $piloto = Piloto::firstOrCreate([
                'nombre' => $nombre
            ], [
                'pais' => trim($p['pais'] ?? 'Argentina')
            ]);

            if ($campeonatoId) {
                // Agregar al pivote (y enviamos el ID uuid si la tabla lo requiere estricto)
                $piloto->campeonatos()->syncWithoutDetaching([
                    $campeonatoId => [
                        'id' => \Illuminate\Support\Str::uuid()->toString(),
                        'numero_auto' => $p['auto']
                    ]
                ]);
            }
            $processedCount++;
        }

        return redirect()->route('admin.pilotos.index')
            ->with('success', "Se importaron exitosamente {$processedCount} pilotos documentados.");
    }

    /**
     * AJAX quick store for creating a pilot during results import.
     */
    public function quickStore(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255|unique:pilotos,nombre',
            'pais'   => 'nullable|string|max:100',
            'campeonato_id' => 'nullable|exists:campeonatos,id',
            'numero_auto'   => 'nullable|integer'
        ]);

        $nombre = ucwords(strtolower(trim($validated['nombre'])));

        $piloto = Piloto::create([
            'nombre' => $nombre,
            'pais'   => $validated['pais'] ?? 'Argentina'
        ]);

        if (!empty($validated['campeonato_id'])) {
            $piloto->campeonatos()->syncWithoutDetaching([
                $validated['campeonato_id'] => [
                    'id' => \Illuminate\Support\Str::uuid()->toString(),
                    'numero_auto' => $validated['numero_auto'] ?? 0
                ]
            ]);
        }

        return response()->json([
            'id' => $piloto->id,
            'nombre' => $piloto->nombre
        ]);
    }
}
