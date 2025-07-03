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

        // Crear consulta base
        $query = Piloto::query();

        // Aplicar búsqueda
        $searchFields = ['nombre', 'pais']; // Campos en los que buscar
        $this->applySearch($query, $request, $searchFields);

        // Definir columnas de la tabla
        $columns = [
            ['field' => 'nombre', 'label' => 'Nombre', 'type' => 'text'],
            ['field' => 'pais', 'label' => 'País', 'type' => 'badge', 'color' => 'primary'],
        ];

        // Configuración específica
        $config = [
            'orderBy' => 'nombre',
            'orderDirection' => 'asc',
            'nameField' => 'nombre'
        ];

        // Manejar respuesta
        $result = $this->handleIndexResponse($request, $query, $columns, 'admin.pilotos', $config);

        
        // Si es AJAX, ya se devolvió la respuesta
        if ($request->ajax()) {
            return $result;
        }

        // Si no es AJAX, devolver la vista completa
        $pilotos = $result;
        return view('admin.pilotos.index', compact('pilotos'));
    }

    public function create()
    {
        return view('admin.pilotos.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'pais' => 'nullable|string|max:100',
        ]);

        Piloto::create($validated);

        return redirect()->route('admin.pilotos.index')
            ->with('success', 'Piloto creado exitosamente.');
    }

    public function show(Piloto $piloto)
    {
        $piloto;

        return view('admin.pilotos.show', compact('piloto'));
    }

    public function edit(Piloto $piloto)
    {
        return view('admin.pilotos.edit', compact('piloto'));
    }

    public function update(Request $request, Piloto $piloto)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'pais' => 'nullable|string|max:100',
        ]);

        $piloto->update($validated);

        return redirect()->route('admin.pilotos.index')
            ->with('success', 'Piloto actualizado exitosamente.');
    }

    public function destroy(Piloto $piloto)
    {
        $piloto->delete();

        return redirect()->route('admin.pilotos.index')
            ->with('success', 'Piloto eliminado exitosamente.');
    }
}
