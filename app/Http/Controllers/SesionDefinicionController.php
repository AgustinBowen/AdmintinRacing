<?php

namespace App\Http\Controllers;

use App\Models\SesionDefinicion;
use App\Models\Fecha;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;

class SesionDefinicionController extends Controller
{
    /**
     * Display a listing of the sessions.
     */
    public function index()
    {
        $sesiones = SesionDefinicion::with('fecha')
            ->orderBy('horario')
            ->paginate(10);

        return view('admin.sesiones.index', compact('sesiones'));
    }

    /**
     * Show the form for creating a new session.
     */
    public function create()
    {
        $fechas = Fecha::all();
        $fechas = Fecha::pluck('nombre', 'id');
        $tipos = SesionDefinicion::TIPOS;

        return view('admin.sesiones.create', compact('fechas', 'tipos'));
    }

    /**
     * Store a newly created session in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'fecha_id' => 'required|exists:fechas,id',
            'fecha_sesion' => 'required|date',
            'tipo' => 'required|in:' . implode(',', array_keys(SesionDefinicion::TIPOS)),
        ]);

        SesionDefinicion::create($validated);

        Session::flash('success', 'Sesión creada exitosamente.');
        return Redirect::route('admin.sesiones.index');
    }

    /**
     * Display the specified session.
     */
    public function show(SesionDefinicion $sesion)
    {
        $sesion->load('horarios');
        return view('admin.sesiones.show', compact('sesion'));
    }

    /**
     * Show the form for editing the specified session.
     */
    public function edit(SesionDefinicion $sesion)
    {
        $fechas = Fecha::all();
        $fechas = Fecha::pluck('nombre', 'id');
        $tipos = SesionDefinicion::TIPOS;

        return view('admin.sesiones.edit', compact('sesion', 'fechas', 'tipos'));
    }

    /**
     * Update the specified session in storage.
     */
    public function update(Request $request, SesionDefinicion $sesion)
    {
        $validated = $request->validate([
            'fecha_id' => 'required|exists:fechas,id',
            'fecha_sesion' => 'required|date',
            'tipo' => 'required|in:' . implode(',', array_keys(SesionDefinicion::TIPOS)),
        ]);

        $sesion->update($validated);

        Session::flash('success', 'Sesión actualizada exitosamente.');
        return Redirect::route('admin.sesiones.index');
    }

    /**
     * Remove the specified session from storage.
     */
    public function destroy(SesionDefinicion $sesion)
    {
        $sesion->delete();
        Session::flash('success', 'Sesión eliminada exitosamente.');
        return Redirect::route('admin.sesiones.index');
    }
}