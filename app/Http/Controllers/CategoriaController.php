<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Campeonato;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;

class CategoriaController extends Controller
{
    public function index()
    {
        $categorias = Categoria::orderBy('nombre')->get();
        return view('admin.categorias.index', compact('categorias'));
    }

    public function create()
    {
        return view('admin.categorias.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'activa' => 'boolean',
        ]);

        $validated['activa'] = $request->has('activa');

        Categoria::create($validated);

        return Redirect::route('admin.categorias.index')->with('success', 'Categoría creada exitosamente.');
    }

    public function show(Categoria $categoria)
    {
        $categoria->load(['campeonatos' => function ($query) {
            $query->withCount('fechas');
        }]);
        return view('admin.categorias.show', compact('categoria'));
    }

    public function edit(Categoria $categoria)
    {
        return view('admin.categorias.edit', compact('categoria'));
    }

    public function update(Request $request, Categoria $categoria)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'activa' => 'boolean',
        ]);

        $validated['activa'] = $request->has('activa');

        $categoria->update($validated);

        return Redirect::route('admin.categorias.index')->with('success', 'Categoría actualizada exitosamente.');
    }

    public function destroy(Categoria $categoria)
    {
        if ($categoria->campeonatos()->count() > 0) {
            return Redirect::route('admin.categorias.index')->with('error', 'No se puede eliminar la categoría porque tiene campeonatos asociados.');
        }

        $categoria->delete();
        return Redirect::route('admin.categorias.index')->with('success', 'Categoría eliminada exitosamente.');
    }

    public function seleccionarCampeonato(Categoria $categoria, Campeonato $campeonato)
    {
        if ($campeonato->categoria_id !== $categoria->id) {
            return redirect()->route('admin.categorias.show', $categoria)->with('error', 'El campeonato no pertenece a la categoría seleccionada.');
        }

        session([
            'categoria_id'      => $categoria->id,
            'categoria_nombre'  => $categoria->nombre,
            'campeonato_id'     => $campeonato->id,
            'campeonato_nombre' => $campeonato->nombre,
        ]);

        session()->flash('animate_entry', true);

        return redirect()->route('admin.dashboard')->with('success', 'Contexto cambiado a: ' . $categoria->nombre . ' - ' . $campeonato->nombre);
    }

    public function createCampeonato(Categoria $categoria)
    {
        return view('admin.categorias.create-campeonato', compact('categoria'));
    }

    public function storeCampeonato(Request $request, Categoria $categoria)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'anio' => 'required|integer|min:2000|max:2100'
        ]);

        $validated['categoria_id'] = $categoria->id;
        $campeonato = Campeonato::create($validated);

        // Seed default scoring
        \App\Services\StandingsService::seedDefaultScoring($campeonato->id);

        return Redirect::route('admin.categorias.show', $categoria)->with('success', 'Campeonato creado exitosamente.');
    }
}
