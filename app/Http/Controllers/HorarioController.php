<?php

namespace App\Http\Controllers;

use App\Models\Horario;
use App\Models\Fecha;
use App\Models\SesionDefinicion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;

class HorarioController extends Controller
{
    /**
     * Display a listing of the schedules.
     */
    public function index()
    {
        $horarios = Horario::with(['fecha', 'sesion'])
            ->ordenadoPorHorario()
            ->paginate(10);

        return view('admin.horarios.index', compact('horarios'));
    }

    /**
     * Show the form for creating a new schedule.
     */
    public function create()
    {
        $fechas = Fecha::all();
        $fechas = Fecha::pluck('nombre', 'id');
        $sesiones = SesionDefinicion::all();
        $sesiones = SesionDefinicion::pluck('tipo', 'id');

        return view('admin.horarios.create', compact('fechas', 'sesiones'));
    }

    /**
     * Store a newly created schedule in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'fecha_id' => 'required|exists:fechas,id',
            'sesion_id' => 'required|exists:sesiones_definicion,id',
            'horario' => 'required|date',
            'duracion' => 'required|string', // Assuming duration is a PostgreSQL interval
            'observaciones' => 'nullable|string|max:1000',
        ]);

        Horario::create($validated);

        Session::flash('success', 'Horario creado exitosamente.');
        return Redirect::route('admin.horarios.index');
    }

    /**
     * Display the specified schedule.
     */
    public function show(Horario $horario)
    {
        $horario->load('fecha', 'sesion');
        return view('admin.horarios.show', compact('horario'));
    }

    /**
     * Show the form for editing the specified schedule.
     */
    public function edit(Horario $horario)
    {
        $fechas = Fecha::all();
        $sesiones = SesionDefinicion::all();

        return view('admin.horarios.edit', compact('horario', 'fechas', 'sesiones'));
    }

    /**
     * Update the specified schedule in storage.
     */
    public function update(Request $request, Horario $horario)
    {
        $validated = $request->validate([
            'fecha_id' => 'required|exists:fechas,id',
            'sesion_id' => 'required|exists:sesiones_definicion,id',
            'horario' => 'required|date',
            'duracion' => 'required|string',
            'observaciones' => 'nullable|string|max:1000',
        ]);

        $horario->update($validated);

        Session::flash('success', 'Horario actualizado exitosamente.');
        return Redirect::route('admin.horarios.index');
    }

    /**
     * Remove the specified schedule from storage.
     */
    public function destroy(Horario $horario)
    {
        $horario->delete();
        Session::flash('success', 'Horario eliminado exitosamente.');
        return Redirect::route('admin.horarios.index');
    }
}