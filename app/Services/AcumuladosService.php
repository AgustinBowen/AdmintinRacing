<?php

namespace App\Services;

use App\Models\Fecha;
use App\Models\SesionDefinicion;
use App\Models\ResultadoSesion;

class AcumuladosService
{
    /**
     * Generar la sesión de resultados ACUMULADOS (mejor tiempo de cada piloto en entrenamientos)
     * Returns true on success, false if no training results found.
     */
    public function generarAcumulados(Fecha $fecha): bool
    {
        // 1. Obtener o crear la sesión de Acumulados
        $sesionAcumulados = SesionDefinicion::firstOrCreate([
            'fecha_id' => $fecha->id,
            'tipo'     => 'acumulados'
        ], [
            'fecha_sesion' => $fecha->fecha_desde // Por defecto el primer día
        ]);

        // 2. Limpiar resultados anteriores de acumulados
        $sesionAcumulados->resultados()->delete();

        // 3. Obtener todos los resultados de entrenamientos de esta fecha
        $resultadosEntrenamientos = ResultadoSesion::whereHas('sesion', function($q) use ($fecha) {
            $q->where('fecha_id', $fecha->id)
              ->where('tipo', 'like', 'entrenamiento_%');
        })->get();

        if ($resultadosEntrenamientos->isEmpty()) {
            return false;
        }

        // 4. Agrupar por piloto y encontrar el mejor tiempo
        $mejorPorPiloto = [];
        foreach ($resultadosEntrenamientos as $res) {
            if (!$res->mejor_tiempo) continue;
            
            if (!isset($mejorPorPiloto[$res->piloto_id]) || $res->mejor_tiempo < $mejorPorPiloto[$res->piloto_id]['tiempo']) {
                $sessionName = SesionDefinicion::TIPOS[$res->sesion->tipo] ?? $res->sesion->tipo;
                $mejorPorPiloto[$res->piloto_id] = [
                    'tiempo' => $res->mejor_tiempo,
                    'vueltas' => max($res->vueltas ?? 0, isset($mejorPorPiloto[$res->piloto_id]) ? $mejorPorPiloto[$res->piloto_id]['vueltas'] : 0),
                    'en_sesion' => $sessionName
                ];
            }
        }

        // 5. Ordenar por tiempo ascendente
        uasort($mejorPorPiloto, function($a, $b) {
            return $a['tiempo'] <=> $b['tiempo'];
        });

        // 6. Crear los nuevos resultados de acumulados
        $posicion = 1;
        $primerTiempo = !empty($mejorPorPiloto) ? reset($mejorPorPiloto)['tiempo'] : 0;

        foreach ($mejorPorPiloto as $pilotoId => $data) {
            ResultadoSesion::create([
                'sesion_id'          => $sesionAcumulados->id,
                'piloto_id'          => $pilotoId,
                'posicion'           => $posicion++,
                'mejor_tiempo'       => $data['tiempo'],
                'vueltas'            => $data['vueltas'],
                'diferencia_primero' => $data['tiempo'] - $primerTiempo,
                'observaciones'      => $data['en_sesion'],
                'puntos'             => 0,
                'presente'           => true
            ]);
        }
        
        return true;
    }
}
