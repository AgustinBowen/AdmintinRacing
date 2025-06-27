<?php

namespace App\Http\Controllers;

use App\Models\Fecha;
use App\Models\Campeonato;
use App\Models\Circuito;
use Illuminate\Http\Request;

class FechaController extends Controller
{
    public function index()
    {
        $fechas = Fecha::with(['campeonato', 'circuito'])
            ->orderBy('fecha', 'desc')
            ->paginate(10);

        return view('admin.fechas.index', compact('fechas'));
    }

    public function create()
    {
        $campeonatos = Campeonato::orderBy('anio', 'desc')->get();
        $campeonatos = Campeonato::pluck('nombre', 'id');
        $circuitos = Circuito::orderBy('nombre')->get();
        $circuitos = Circuito::pluck('nombre', 'id');
        
        return view('admin.fechas.create', compact('campeonatos', 'circuitos'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'fecha_desde' => 'required|date',
            'fecha_hasta' => 'required|date',
            'campeonato_id' => 'required|exists:campeonatos,id',
            'circuito' => 'required|exists:circuitos,id',
        ]);

        Fecha::create($validated);

        return redirect()->route('admin.fechas.index')
            ->with('success', 'Fecha creada exitosamente.');
    }

    public function show(Fecha $fecha)
    {
        $fecha->load(['campeonato', 'circuito', 'resultados.piloto']);
        
        return view('admin.fechas.show', compact('fecha'));
    }

    public function edit(Fecha $fecha)
    {
        $campeonatos = Campeonato::orderBy('anio', 'desc')->get();
        $circuitos = Circuito::orderBy('nombre')->get();
        
        return view('admin.fechas.edit', compact('fecha', 'campeonatos', 'circuitos'));
    }

    public function update(Request $request, Fecha $fecha)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'fecha' => 'required|date',
            'campeonato_id' => 'required|exists:campeonatos,id',
            'circuito_id' => 'required|exists:circuitos,id',
            'numero_fecha' => 'required|integer|min:1',
        ]);

        $fecha->update($validated);

        return redirect()->route('admin.fechas.index')
            ->with('success', 'Fecha actualizada exitosamente.');
    }

    public function destroy(Fecha $fecha)
    {
        $fecha->delete();

        return redirect()->route('admin.fechas.index')
            ->with('success', 'Fecha eliminada exitosamente.');
    }
}