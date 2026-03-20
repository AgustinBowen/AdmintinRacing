<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Fecha;
use App\Models\Campeonato;
use App\Models\SesionDefinicion;
use Illuminate\Http\JsonResponse;

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
            $fechas->map(fn($fecha) => $this->formatFecha($fecha, $numeroAutoMap, $campeonatoInfo))
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

        return response()->json([
            'id'               => $fecha->id,
            'nombre'           => $fecha->nombre,
            'fecha_desde'      => $fecha->fecha_desde?->toDateString(),
            'fecha_hasta'      => $fecha->fecha_hasta?->toDateString(),
            'campeonato'       => $fecha->campeonato ? ['id' => $fecha->campeonato->id, 'nombre' => $fecha->campeonato->nombre, 'anio' => $fecha->campeonato->anio] : null,
            'circuitoNombre'   => $fecha->circuito?->nombre,
            'circuitoDistancia'=> $fecha->circuito?->distancia,
        ]);
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

        $fechaDate = $fecha->fecha_desde;
        $todayDate = now()->startOfDay();
        $status = $fechaDate->toDateString() === $todayDate->toDateString() ? 'live' : 'completed';

        return response()->json([
            'id'               => $fecha->id,
            'nombre'           => $fecha->nombre,
            'fecha_desde'      => $fecha->fecha_desde?->toDateString(),
            'fecha_hasta'      => $fecha->fecha_hasta?->toDateString(),
            'campeonato_id'    => $fecha->campeonato_id,
            'campeonato'       => $campeonatoInfo,
            'circuitoNombre'   => $fecha->circuito?->nombre,
            'circuitoDistancia'=> $fecha->circuito?->distancia,
            'status'           => $status,
            'sesiones'         => $this->formatSesiones($fecha->sesiones, $numeroAutoMap, $fecha->id),
        ]);
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

        $today = now()->startOfDay();
        $fechaDate = $fecha->fecha_desde;

        if ($fechaDate < $today) {
            $status = 'completed';
        } elseif ($fechaDate->toDateString() === $today->toDateString()) {
            $status = 'live';
        } else {
            $status = 'upcoming';
        }

        return response()->json([
            'id'               => $fecha->id,
            'nombre'           => $fecha->nombre,
            'fecha_desde'      => $fecha->fecha_desde?->toDateString(),
            'fecha_hasta'      => $fecha->fecha_hasta?->toDateString(),
            'campeonato_id'    => $fecha->campeonato_id,
            'campeonato'       => $campeonatoInfo,
            'circuitoNombre'   => $fecha->circuito?->nombre,
            'circuitoDistancia'=> $fecha->circuito?->distancia,
            'status'           => $status,
            'sesiones'         => $this->formatSesiones($fecha->sesiones, $numeroAutoMap, $fecha->id),
        ]);
    }

    // -------------------------------------------------------
    // Helpers
    // -------------------------------------------------------

    private function formatFecha($fecha, $numeroAutoMap, $campeonatoInfo): array
    {
        $today = now()->startOfDay();
        $fechaDate = $fecha->fecha_desde;

        if ($fechaDate < $today) {
            $status = 'completed';
        } elseif ($fechaDate->toDateString() === $today->toDateString()) {
            $status = 'live';
        } else {
            $status = 'upcoming';
        }

        // Ganador carrera final
        $sesionesPorTipo = $fecha->sesiones->keyBy('tipo');
        $carreraFinal = $sesionesPorTipo->get('carrera_final');
        $winner = null;
        if ($carreraFinal) {
            $ganador = $carreraFinal->resultados->where('posicion', 1)->first();
            $winner = $ganador?->piloto?->nombre ?? null;
        }

        return [
            'id'               => $fecha->id,
            'nombre'           => $fecha->nombre,
            'fecha_desde'      => $fecha->fecha_desde?->toDateString(),
            'fecha_hasta'      => $fecha->fecha_hasta?->toDateString(),
            'campeonato_id'    => $fecha->campeonato_id,
            'campeonato'       => $campeonatoInfo,
            'circuitoNombre'   => $fecha->circuito?->nombre ?? 'Circuito no especificado',
            'circuitoDistancia'=> $fecha->circuito?->distancia,
            'status'           => $status,
            'winner'           => $winner,
            'sesiones'         => $this->formatSesiones($fecha->sesiones, $numeroAutoMap, $fecha->id),
        ];
    }

    private function formatSesiones($sesiones, $numeroAutoMap, $fechaId): array
    {
        $result = [];
        $orden = SesionDefinicion::ORDEN;

        $sesionesOrdenadas = $sesiones->sortBy(fn($s) => $orden[$s->tipo] ?? 99);

        foreach ($sesionesOrdenadas as $sesion) {
            $resultados = $sesion->resultados
                ->sortBy(fn($r) => $r->excluido ? 9999 : ($r->posicion ?? 9998))
                ->values()
                ->map(fn($r) => $this->formatResultado($r, $numeroAutoMap, $fechaId));

            $result[] = [
                'id'         => $sesion->id,
                'tipo'       => $sesion->tipo,
                'nombre'     => SesionDefinicion::TIPOS[$sesion->tipo] ?? $sesion->tipo,
                'resultados' => $resultados,
            ];
        }

        return $result;
    }

    private function formatResultado($r, $numeroAutoMap, $fechaId): array
    {
        return [
            'id'                 => $r->id,
            'fecha_id'           => $fechaId,
            'piloto_id'          => $r->piloto_id,
            'piloto'             => $r->piloto ? ['id' => $r->piloto->id, 'nombre' => $r->piloto->nombre, 'pais' => $r->piloto->pais] : null,
            'posicion'           => $r->posicion,
            'puntos'             => $r->puntos,
            'vueltas'            => $r->vueltas,
            'mejor_tiempo'       => $r->mejor_tiempo,
            'tiempo_total'       => $r->tiempo_total,
            'diferencia_primero' => $r->diferencia_primero,
            'sector_1'           => $r->sector_1,
            'sector_2'           => $r->sector_2,
            'sector_3'           => $r->sector_3,
            'excluido'           => $r->excluido,
            'presente'           => $r->presente,
            'numeroAuto'         => $numeroAutoMap->get($r->piloto_id),
        ];
    }
}
