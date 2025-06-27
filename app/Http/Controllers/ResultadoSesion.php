<?php

namespace App\Http\Controllers;

use App\Models\ResultadoSesion;
use App\Models\SesionDefinicion;
use App\Models\Piloto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;

class ResultadoSesionController extends Controller
{
    /**
     * Display a listing of the session results.
     */
    public function index()
    {
        $resultados = ResultadoSesion::with(['sesion', 'piloto'])
            ->ordenadoPorPosicion()
            ->paginate(10);

        return view('admin.resultados.index', compact('resultados'));
    }

    /**
     * Show the form for creating a new session result.
     */
    public function create()
    {
        $sesiones = SesionDefinicion::all();
        $pilotos = Piloto::all();

        return view('admin.resultados.create', compact('sesiones', 'pilotos'));
    }

    /**
     * Store a newly created session result in storage.
     */
    public function store(Request $request)
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

        ResultadoSesion::create($validated);

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
        $sesiones = SesionDefinicion::all();
        $pilotos = Piloto::all();

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