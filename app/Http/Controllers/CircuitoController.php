<?php

namespace App\Http\Controllers;

use App\Models\Circuito;
use Illuminate\Http\Request;

class CircuitoController extends Controller
{
    public function index()
    {
        $circuitos = Circuito::orderBy('nombre')
            ->paginate(10);

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
        $circuito->load('fechas.campeonato');
        
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
