<?php

namespace App\Http\Controllers;

use App\Models\Horario;
use App\Models\Fecha;
use App\Models\SesionDefinicion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

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
            'fecha_id' => 'required|uuid|exists:fechas,id',
            'sesion_id' => [
                'required',
                'uuid',
                'exists:sesiones_definicion,id',
                function ($attribute, $value, $fail) {
                    if (Horario::existePorSesion($value)) {
                        $fail('Ya existe un horario para esta sesión.');
                    }
                },
            ],
            'horario' => 'required|date_format:H:i',
            'duracion' => 'required|string|max:255',
            'observaciones' => 'nullable|string',
        ]);

        try {
            $fecha = Fecha::findOrFail($validated['fecha_id']);
            $fechaBase = Carbon::parse($fecha->fecha_desde)->format('Y-m-d');
            $timestamp = Carbon::parse("$fechaBase {$validated['horario']}");
            $validated['horario'] = $timestamp;

            Horario::create($validated);

            Session::flash('success', 'Horario creado exitosamente.');
            return Redirect::route('admin.horarios.index');
        } catch (\Exception $e) {
            Session::flash('error', 'Error al crear el horario: ' . $e->getMessage());
            return Redirect::back()->withInput();
        }
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
        $fechas = Fecha::pluck('nombre', 'id');
        $sesiones = SesionDefinicion::pluck('tipo', 'id');

        return view('admin.horarios.edit', compact('horario', 'fechas', 'sesiones'));
    }

    /**
     * Update the specified schedule in storage.
     */
    public function update(Request $request, Horario $horario)
    {
        $validated = $request->validate([
            'fecha_id' => 'required|uuid|exists:fechas,id',
            'sesion_id' => [
                'required',
                'uuid',
                'exists:sesiones_definicion,id',
                function ($attribute, $value, $fail) {
                    if (Horario::existePorSesion($value)) {
                        $fail('Ya existe un horario para esta sesión.');
                    }
                },
            ],
            'horario' => 'required|date_format:H:i',
            'duracion' => 'required|string|max:255',
            'observaciones' => 'nullable|string',
        ]);
        try {
            $fecha = Fecha::findOrFail($validated['fecha_id']);
            $fechaBase = Carbon::parse($fecha->fecha_desde)->format('Y-m-d');
            $timestamp = Carbon::parse("$fechaBase {$validated['horario']}");
            $validated['horario'] = $timestamp;

            $horario->update($validated);

            Session::flash('success', 'Horario actualizado exitosamente.');
            return Redirect::route('admin.horarios.index');
        } catch (\Exception $e) {
            Session::flash('error', 'Error al actualizar el horario: ' . $e->getMessage());
            return Redirect::back()->withInput();
        }
        
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
