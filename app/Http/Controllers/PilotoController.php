<?php

namespace App\Http\Controllers;

use App\Models\Piloto;
use Illuminate\Http\Request;

class PilotoController extends Controller
{
    public function index()
    {
        $pilotos = Piloto::orderBy('nombre')
            ->paginate(10);

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
