<?php

namespace App\Http\Controllers;

use App\Models\Circuito;
use Illuminate\Http\Request;
use App\Traits\HasSearchAndPagination;

class CircuitoController extends Controller
{
    use HasSearchAndPagination;

    public function index(Request $request)
    {
        // Configurar paginación
        $this->setupPagination();

        // Crear consulta base
        $query = Circuito::query();

        // Aplicar búsqueda
        $searchFields = ['nombre', 'distancia']; // Campos en los que buscar
        $this->applySearch($query, $request, $searchFields);

        // Definir columnas de la tabla
        $columns = [
            ['field' => 'nombre', 'label' => 'Nombre', 'type' => 'text'],
            ['field' => 'distancia', 'label' => 'Distancia', 'type' => 'badge', 'color' => 'primary'],
        ];

        // Configuración específica
        $config = [
            'orderBy' => 'nombre',
            'orderDirection' => 'asc',
            'nameField' => 'nombre'
        ];

        // Manejar respuesta
        $result = $this->handleIndexResponse($request, $query, $columns, 'admin.circuitos', $config);

        // Si es AJAX, ya se devolvió la respuesta
        if ($request->ajax()) {
            return $result;
        }

        // Si no es AJAX, devolver la vista completa
        $circuitos = $result;
        return view('admin.circuitos.index', compact('circuitos'));
    }

    public function create()
    {
        return view('admin.circuitos.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'distancia' => 'nullable|numeric|min:0',
        ]);

        Circuito::create($validated);

        return redirect()->route('admin.circuitos.index')
            ->with('success', 'Circuito creado exitosamente.');
    }

    public function show(Circuito $circuito)
    {
        return view('admin.circuitos.show', compact('circuito'));
    }

    public function edit(Circuito $circuito)
    {
        return view('admin.circuitos.edit', compact('circuito'));
    }

    public function update(Request $request, Circuito $circuito)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'distancia' => 'nullable|numeric|min:0',
        ]);

        $circuito->update($validated);

        return redirect()->route('admin.circuitos.index')
            ->with('success', 'Circuito actualizado exitosamente.');
    }

    public function destroy(Circuito $circuito)
    {
        $circuito->delete();

        return redirect()->route('admin.circuitos.index')
            ->with('success', 'Circuito eliminado exitosamente.');
    }
}
