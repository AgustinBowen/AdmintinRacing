<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Campeonato;
use Illuminate\Http\JsonResponse;

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

        return response()->json($this->formatCampeonatoWithStandings($campeonato));
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

        return response()->json($this->formatCampeonatoWithStandings($campeonato));
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

        $formatted = $fechas->map(fn($fecha) => $this->formatFechaWithResults($fecha, $numeroAutoMap, $campeonato));

        return response()->json($formatted);
    }

    // -------------------------------------------------------
    // Helpers
    // -------------------------------------------------------

    private function formatCampeonatoWithStandings(Campeonato $campeonato): array
    {
        // Mapa piloto_id => numero_auto
        $numeroAutoMap = $campeonato->pilotos
            ->mapWithKeys(fn($p) => [$p->id => $p->pivot->numero_auto]);

        $standings = $campeonato->posicionesCampeonato
            ->sortByDesc('puntos_totales')
            ->values()
            ->map(function ($pos, $index) use ($numeroAutoMap) {
                return [
                    'position'   => $index + 1,
                    'piloto'     => [
                        'id'     => $pos->piloto?->id,
                        'nombre' => $pos->piloto?->nombre,
                        'pais'   => $pos->piloto?->pais,
                    ],
                    'puntos'     => $pos->puntos_totales ?? 0,
                    'numeroAuto' => $numeroAutoMap->get($pos->piloto_id),
                ];
            });

        return [
            'id'       => $campeonato->id,
            'nombre'   => $campeonato->nombre,
            'anio'     => $campeonato->anio,
            'standings' => $standings,
        ];
    }

    private function formatFechaWithResults($fecha, $numeroAutoMap, $campeonato): array
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

        // Sesiones mapeadas por tipo
        $sesionesPorTipo = $fecha->sesiones->keyBy('tipo');

        // Ganador de la carrera final
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
            'campeonato'       => ['id' => $campeonato->id, 'nombre' => $campeonato->nombre, 'anio' => $campeonato->anio],
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
        $orden = \App\Models\SesionDefinicion::ORDEN;

        $sesionesOrdenadas = $sesiones->sortBy(fn($s) => $orden[$s->tipo] ?? 99);

        foreach ($sesionesOrdenadas as $sesion) {
            $resultados = $sesion->resultados
                ->sortBy(fn($r) => $r->excluido ? 9999 : ($r->posicion ?? 9998))
                ->values()
                ->map(fn($r) => $this->formatResultado($r, $numeroAutoMap, $fechaId));

            $result[] = [
                'id'      => $sesion->id,
                'tipo'    => $sesion->tipo,
                'nombre'  => \App\Models\SesionDefinicion::TIPOS[$sesion->tipo] ?? $sesion->tipo,
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
