<?php

namespace App\Http\Controllers;

use App\Models\Campeonato;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

class CampeonatoController extends Controller
{
    public function index()
    {
        $campeonatos = Campeonato::withCount('fechas')
            ->orderBy('anio', 'desc')
            ->paginate(10);
        Paginator::defaultView('components.admin.paginator');

        return view('admin.campeonatos.index', compact('campeonatos'));
    }

    public function create()
    {
        return view('admin.campeonatos.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'anio' => 'required|integer|min:1900|max:' . (date('Y') + 10),
        ]);

        Campeonato::create($validated);

        return redirect()->route('admin.campeonatos.index')
            ->with('success', 'Campeonato creado exitosamente.');
    }

    public function show(Campeonato $campeonato)
    {
        $campeonato->load(['fechas.circuito', 'fechas.resultados.piloto']);
        
        return view('admin.campeonatos.show', compact('campeonato'));
    }

    public function edit(Campeonato $campeonato)
    {
        return view('admin.campeonatos.edit', compact('campeonato'));
    }

    public function update(Request $request, Campeonato $campeonato)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'anio' => 'required|integer|min:1900|max:' . (date('Y') + 10),
        ]);

        $campeonato->update($validated);

        return redirect()->route('campeonatos.index')
            ->with('success', 'Campeonato actualizado exitosamente.');
    }

    public function destroy(Campeonato $campeonato)
    {
        $campeonato->delete();

        return redirect()->route('admin.campeonatos.index')
            ->with('success', 'Campeonato eliminado exitosamente.');
    }
}
