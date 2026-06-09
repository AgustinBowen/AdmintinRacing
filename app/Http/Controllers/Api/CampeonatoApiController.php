<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Campeonato;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\CampeonatoResource;
use App\Http\Resources\FechaResource;

class CampeonatoApiController extends Controller
{
    /**
     * GET /api/campeonatos
     * Devuelve todos los campeonatos ordenados por año descendente.
     */
    public function index(): JsonResponse
    {
        $campeonatos = Campeonato::orderBy('anio', 'desc')
            ->get(['id', 'nombre', 'anio']);

        return response()->json($campeonatos);
    }

    /**
     * GET /api/campeonatos/current
     * Devuelve el campeonato del año actual con standings completos.
     */
    public function current(): JsonResponse
    {
        $currentYear = now()->year;

        $campeonato = Campeonato::with([
            'posicionesCampeonato.piloto',
            'pilotos' => fn($q) => $q->select('pilotos.id', 'nombre', 'pais'),
        ])->where('anio', $currentYear)->first();

        if (!$campeonato) {
            return response()->json(null);
        }

        return response()->json(new CampeonatoResource($campeonato));
    }

    /**
     * GET /api/campeonatos/{id}/standings
     * Devuelve las posiciones de un campeonato específico.
     */
    public function standings(string $id): JsonResponse
    {
        $campeonato = Campeonato::with([
            'posicionesCampeonato.piloto',
            'pilotos' => fn($q) => $q->select('pilotos.id', 'nombre', 'pais'),
        ])->findOrFail($id);

        return response()->json(new CampeonatoResource($campeonato));
    }

    /**
     * GET /api/campeonatos/{id}/fechas
     * Devuelve todas las fechas/carreras de un campeonato.
     */
    public function fechas(string $id): JsonResponse
    {
        $campeonato = Campeonato::findOrFail($id);

        $pilotos = $campeonato->pilotos()->select('pilotos.id', 'nombre', 'pais')->get();
        $numeroAutoMap = $pilotos->mapWithKeys(fn($p) => [$p->id => $p->pivot->numero_auto]);

        $fechas = $campeonato->fechas()
            ->with(['circuito:id,nombre,distancia', 'sesiones.resultados.piloto'])
            ->orderBy('fecha_desde')
            ->get();

        $campeonatoInfo = ['id' => $campeonato->id, 'nombre' => $campeonato->nombre, 'anio' => $campeonato->anio];
        $formatted = $fechas->map(fn($fecha) => new FechaResource($fecha, $numeroAutoMap, $campeonatoInfo));

        return response()->json($formatted);
    }

    // -------------------------------------------------------
    // Los helpers de formateo se movieron a API Resources
    // -------------------------------------------------------
}
