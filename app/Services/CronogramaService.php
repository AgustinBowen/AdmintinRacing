<?php

namespace App\Services;

use App\Models\Fecha;
use App\Models\SesionDefinicion;
use App\Models\Horario;
use Carbon\Carbon;

class CronogramaService
{
    /**
     * Generar un cronograma estándar de sesiones y horarios para una fecha
     */
    public function generarCronogramaEstandar(Fecha $fecha)
    {
        $dia1 = Carbon::parse($fecha->fecha_desde);
        $dia2 = $dia1->copy()->addDay();
        $diaFinal = Carbon::parse($fecha->fecha_hasta);

        $sesiones = [
            ['tipo' => 'entrenamiento_1',        'hora' => '09:00', 'duracion' => '15 min',    'dia' => $dia1],
            ['tipo' => 'entrenamiento_2',        'hora' => '09:30', 'duracion' => '15 min',    'dia' => $dia1],
            ['tipo' => 'entrenamiento_3',        'hora' => '10:00', 'duracion' => '15 min',    'dia' => $dia1],
            ['tipo' => 'entrenamiento_4',        'hora' => '10:30', 'duracion' => '15 min',    'dia' => $dia1],
            ['tipo' => 'acumulados',             'hora' => '11:00', 'duracion' => 'Calculado', 'dia' => $dia1],
            ['tipo' => 'clasificacion',          'hora' => '09:00', 'duracion' => '10 min',    'dia' => $dia2],
            ['tipo' => 'serie_clasificatoria_1', 'hora' => '12:00', 'duracion' => '6 vueltas', 'dia' => $dia2],
            ['tipo' => 'serie_clasificatoria_2', 'hora' => '13:00', 'duracion' => '6 vueltas', 'dia' => $dia2],
            ['tipo' => 'serie_clasificatoria_3', 'hora' => '14:00', 'duracion' => '6 vueltas', 'dia' => $dia2],
            ['tipo' => 'carrera_final',          'hora' => '16:00', 'duracion' => '12 vueltas','dia' => $diaFinal],
        ];

        foreach ($sesiones as $s) {
            // Skip if a session of this type already exists for this fecha
            $existe = SesionDefinicion::where('fecha_id', $fecha->id)
                ->where('tipo', $s['tipo'])
                ->exists();

            if ($existe) {
                continue;
            }

            $sesion = SesionDefinicion::create([
                'fecha_id'     => $fecha->id,
                'tipo'         => $s['tipo'],
                'fecha_sesion' => $s['dia']->format('Y-m-d'),
            ]);

            Horario::create([
                'fecha_id'      => $fecha->id,
                'sesion_id'     => $sesion->id,
                'horario'       => Carbon::parse($s['dia']->format('Y-m-d') . ' ' . $s['hora']),
                'duracion'      => $s['duracion'],
                'observaciones' => null,
            ]);
        }
    }
}
