<?php

namespace App\Http\Controllers;

use App\Models\Piloto;
use Illuminate\Http\Request;
use App\Traits\HasSearchAndPagination;
use App\Http\Requests\StorePilotoRequest;
use App\Http\Requests\UpdatePilotoRequest;
use App\Http\Requests\StorePilotoImportRequest;
use App\Http\Requests\QuickStorePilotoRequest;

class PilotoController extends Controller
{
    use HasSearchAndPagination;

    public function index(Request $request)
    {
        // Configurar paginación
        $this->setupPagination();

        $campeonatoId = session('campeonato_id');

        // Definir columnas de la tabla
        $columns = [
            ['field' => 'nombre', 'label' => 'Nombre', 'type' => 'text'],
            ['field' => 'numero_auto_pivot', 'label' => 'N° Auto', 'type' => 'text'],
            ['field' => 'pais', 'label' => 'País', 'type' => 'badge', 'color' => 'primary'],
        ];

        // Crear consulta base con filtro de campeonato fijo
        $query = Piloto::query()
            ->whereHas('campeonatos', function ($q) use ($campeonatoId) {
                $q->where('campeonatos.id', $campeonatoId);
            })
            ->with(['campeonatos' => function($q) use ($campeonatoId) {
                $q->where('campeonatos.id', $campeonatoId);
            }]);

        // Aplicar búsqueda de texto
        $searchFields = ['nombre', 'pais'];
        $this->applySearch($query, $request, $searchFields);

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
        return view('admin.pilotos.index', compact('pilotos', 'columns'));
    }

    public function create()
    {
        return view('admin.pilotos.create');
    }

    public function store(StorePilotoRequest $request)
    {
        $validated = $request->validated();
        $campeonatoId = session('campeonato_id');

        $nombre = ucwords(strtolower(trim($validated['nombre'])));

        if ($campeonatoId && $request->filled('numero_auto')) {
            $numeroTomado = \Illuminate\Support\Facades\DB::table('pilotos_campeonato')
                ->where('campeonato_id', $campeonatoId)
                ->where('numero_auto', $validated['numero_auto'])
                ->exists();

            if ($numeroTomado) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'numero_auto' => 'El número ' . $validated['numero_auto'] . ' ya está siendo usado por otro piloto en este campeonato.'
                ]);
            }
        }

        $piloto = Piloto::firstOrCreate([
            'nombre' => $nombre
        ], [
            'pais' => $validated['pais'] ?? 'Argentina'
        ]);

        if ($campeonatoId) {
            $piloto->campeonatos()->syncWithoutDetaching([
                $campeonatoId => [
                    'id' => \Illuminate\Support\Str::uuid()->toString(),
                    'numero_auto' => $validated['numero_auto'] ?? 0
                ]
            ]);
        }

        return redirect()->route('admin.pilotos.index')
            ->with('success', 'Piloto creado exitosamente.');
    }

    public function show(Piloto $piloto)
    {
        return view('admin.pilotos.show', compact('piloto'));
    }

    public function edit(Piloto $piloto)
    {
        $campeonatoId = session('campeonato_id');
        
        // Obtenemos el campeonato actual
        $campeonatoActual = $piloto->campeonatos()->where('campeonatos.id', $campeonatoId)->first();
        $numeroAutoActual = $campeonatoActual?->pivot->numero_auto;

        return view('admin.pilotos.edit', compact('piloto', 'numeroAutoActual'));
    }

    public function update(UpdatePilotoRequest $request, Piloto $piloto)
    {
        $validated = $request->validated();
        $campeonatoId = session('campeonato_id');

        // Validar que el número de auto no esté tomado por OTRO piloto en ese mismo campeonato
        if ($campeonatoId && $request->filled('numero_auto')) {
            $numeroTomado = \Illuminate\Support\Facades\DB::table('pilotos_campeonato')
                ->where('campeonato_id', $campeonatoId)
                ->where('numero_auto', $validated['numero_auto'])
                ->where('piloto_id', '!=', $piloto->id)
                ->exists();

            if ($numeroTomado) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'numero_auto' => 'El número ' . $validated['numero_auto'] . ' ya está siendo usado por otro piloto en este campeonato.'
                ]);
            }
        }

        $piloto->update([
            'nombre' => $validated['nombre'],
            'pais' => $validated['pais'],
        ]);

        if ($campeonatoId) {
            // Buscamos si ya existe la relación para no generar un nuevo UUID innecesariamente si usamos sync
            $pivot = $piloto->campeonatos()->where('campeonatos.id', $campeonatoId)->first();
            $pivotId = ($pivot && isset($pivot->pivot->id) && $pivot->pivot->id)
                ? $pivot->pivot->id 
                : \Illuminate\Support\Str::uuid()->toString();
            
            $piloto->campeonatos()->syncWithoutDetaching([
                $campeonatoId => [
                    'id' => $pivotId,
                    'numero_auto' => $validated['numero_auto'] ?? 0
                ]
            ]);
        }

        return redirect()->route('admin.pilotos.index')
            ->with('success', 'Piloto actualizado exitosamente.');
    }

    public function destroy(Piloto $piloto)
    {
        // Delete only from current campeonato
        $campeonatoId = session('campeonato_id');
        if ($campeonatoId) {
            $piloto->campeonatos()->detach($campeonatoId);
            
            // If they don't belong to ANY championships anymore, maybe delete? 
            // Or just leave them orphaned. For now leave them.
        }

        return redirect()->route('admin.pilotos.index')
            ->with('success', 'Piloto removido del campeonato exitosamente.');
    }

    public function importForm()
    {
        return view('admin.pilotos.import');
    }

    public function importPreview(Request $request)
    {
        $json = $request->input('pilotos_json');

        if (!$json) {
            return redirect()->route('admin.pilotos.import.form')
                ->withErrors(['pdf_file' => 'No se detectaron pilotos en el archivo PDF mediante JS.']);
        }

        $pilotos = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE || empty($pilotos)) {
             return redirect()->route('admin.pilotos.import.form')
                ->withErrors(['pdf_file' => 'Error analizando la estructura del PDF (JSON inválido).']);
        }

        return view('admin.pilotos.import-preview', compact('pilotos'));
    }

    public function storeImport(StorePilotoImportRequest $request)
    {
        $validated = $request->validated();

        $campeonatoId = session('campeonato_id');
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
    public function quickStore(QuickStorePilotoRequest $request)
    {
        $validated = $request->validated();

        $nombre = ucwords(strtolower(trim($validated['nombre'])));
        $campeonatoId = session('campeonato_id');

        $piloto = Piloto::firstOrCreate([
            'nombre' => $nombre
        ], [
            'pais' => $validated['pais'] ?? 'Argentina'
        ]);

        if ($campeonatoId) {
            $piloto->campeonatos()->syncWithoutDetaching([
                $campeonatoId => [
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
