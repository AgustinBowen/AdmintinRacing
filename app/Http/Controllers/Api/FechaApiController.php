<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Fecha;
use App\Models\Campeonato;
use App\Models\SesionDefinicion;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\FechaResource;

class FechaApiController extends Controller
{
    /**
     * GET /api/fechas
     * Devuelve todas las fechas del campeonato del año actual.
     */
    public function index(): JsonResponse
    {
        $currentYear = now()->year;

        $campeonato = Campeonato::where('anio', $currentYear)->first();
        if (!$campeonato) {
            return response()->json([]);
        }

        $pilotos = $campeonato->pilotos()->select('pilotos.id', 'nombre', 'pais')->get();
        $numeroAutoMap = $pilotos->mapWithKeys(fn($p) => [$p->id => $p->pivot->numero_auto]);

        $fechas = Fecha::with(['circuito:id,nombre,distancia', 'sesiones.resultados.piloto'])
            ->where('campeonato_id', $campeonato->id)
            ->orderBy('fecha_desde')
            ->get();

        $campeonatoInfo = ['id' => $campeonato->id, 'nombre' => $campeonato->nombre, 'anio' => $campeonato->anio];

        return response()->json(
            $fechas->map(fn($fecha) => new FechaResource($fecha, $numeroAutoMap, $campeonatoInfo))
        );
    }

    /**
     * GET /api/fechas/next
     * Devuelve la próxima fecha futura (o la de hoy).
     */
    public function next(): JsonResponse
    {
        $today = now()->toDateString();

        $fecha = Fecha::with(['campeonato:id,nombre,anio', 'circuito:id,nombre,distancia'])
            ->where('fecha_desde', '>=', $today)
            ->orderBy('fecha_desde')
            ->first();

        if (!$fecha) {
            return response()->json(null);
        }

        return response()->json(new FechaResource($fecha, null, null, true));
    }

    /**
     * GET /api/fechas/latest
     * Devuelve la última fecha pasada con todos sus resultados.
     */
    public function latest(): JsonResponse
    {
        $today = now()->toDateString();

        $fecha = Fecha::with(['campeonato:id,nombre,anio', 'circuito:id,nombre,distancia', 'sesiones.resultados.piloto'])
            ->where('fecha_desde', '<=', $today)
            ->orderBy('fecha_desde', 'desc')
            ->first();

        if (!$fecha) {
            return response()->json(null);
        }

        $campeonato = $fecha->campeonato;
        $pilotos = $campeonato->pilotos()->select('pilotos.id', 'nombre', 'pais')->get();
        $numeroAutoMap = $pilotos->mapWithKeys(fn($p) => [$p->id => $p->pivot->numero_auto]);

        $campeonatoInfo = $campeonato ? ['id' => $campeonato->id, 'nombre' => $campeonato->nombre, 'anio' => $campeonato->anio] : null;

        return response()->json(new FechaResource($fecha, $numeroAutoMap, $campeonatoInfo));
    }

    /**
     * GET /api/fechas/{id}
     * Devuelve una fecha específica con todos sus resultados.
     */
    public function show(string $id): JsonResponse
    {
        $fecha = Fecha::with(['campeonato:id,nombre,anio', 'circuito:id,nombre,distancia', 'sesiones.resultados.piloto'])
            ->findOrFail($id);

        $campeonato = $fecha->campeonato;
        $pilotos = $campeonato->pilotos()->select('pilotos.id', 'nombre', 'pais')->get();
        $numeroAutoMap = $pilotos->mapWithKeys(fn($p) => [$p->id => $p->pivot->numero_auto]);

        $campeonatoInfo = $campeonato ? ['id' => $campeonato->id, 'nombre' => $campeonato->nombre, 'anio' => $campeonato->anio] : null;

        return response()->json(new FechaResource($fecha, $numeroAutoMap, $campeonatoInfo));
    }

    // -------------------------------------------------------
    // Los helpers de formateo se movieron a API Resources
    // -------------------------------------------------------
}
