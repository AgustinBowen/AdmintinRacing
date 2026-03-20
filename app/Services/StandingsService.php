<?php

namespace App\Services;

use App\Models\Campeonato;
use App\Models\SistemaPuntaje;
use App\Models\SistemaPuntajeFecha;
use App\Models\ResultadoSesion;
use App\Models\PosicionCampeonato;
use App\Models\SesionDefinicion;
use Illuminate\Support\Facades\DB;

class StandingsService
{
    /**
     * Map session tipo → scoring category.
     * entrenamiento_* sessions are not scored.
     */
    private const TIPO_MAP = [
        'clasificacion'          => 'clasificacion',
        'serie_clasificatoria_1' => 'serie',
        'serie_clasificatoria_2' => 'serie',
        'serie_clasificatoria_3' => 'serie',
        'carrera_final'          => 'final',
    ];

    /**
     * Build a scoring map (tipo_sesion -> posicion|'all' -> puntos)
     * from a collection of scoring rows.
     */
    private function buildMap(iterable $rows): array
    {
        $map = [];
        foreach ($rows as $row) {
            $key = is_null($row->posicion) ? 'all' : (int)$row->posicion;
            $map[$row->tipo_sesion][$key] = (int)$row->puntos;
        }
        return $map;
    }

    /**
     * Calculate standings for a campeonato.
     */
    public function calcular(Campeonato $campeonato): array
    {
        // Campeonato-level scoring (fallback)
        $campeonatoScoring = SistemaPuntaje::where('campeonato_id', $campeonato->id)->get();
        $campeonatoMap     = $this->buildMap($campeonatoScoring);

        // Fecha-level overrides keyed by fecha_id
        $fechaOverrides = SistemaPuntajeFecha::whereHas('fecha', fn($q) => $q->where('campeonato_id', $campeonato->id))
            ->get()
            ->groupBy('fecha_id');

        $campeonato->load([
            'fechas.sesiones.resultados.piloto.campeonatos',
            'pilotos',
        ]);

        $pilotos   = $campeonato->pilotos->keyBy('id');
        $standings = [];

        foreach ($campeonato->fechas as $fecha) {
            // Use fecha-level override if it exists, otherwise campeonato defaults
            $scoringMap = isset($fechaOverrides[$fecha->id])
                ? $this->buildMap($fechaOverrides[$fecha->id])
                : $campeonatoMap;

            // Collect all pilot IDs appearing in any session of this fecha
            $pilotosEnFecha = [];
            foreach ($fecha->sesiones as $sesion) {
                foreach ($sesion->resultados as $res) {
                    $pilotosEnFecha[$res->piloto_id] = true;
                }
            }

            foreach (array_keys($pilotosEnFecha) as $pilotoId) {
                if (!isset($standings[$pilotoId])) {
                    $standings[$pilotoId] = [
                        'piloto' => $pilotos->get($pilotoId),
                        'total'  => 0,
                        'fechas' => [],
                    ];
                }

                $fechaData = [
                    'total'           => 0,
                    'presentacion'    => 0,
                    'clasificacion'   => 0,
                    'series'          => 0,
                    'final'           => 0,
                    'excluido_evento' => false,
                    'tiene_override'  => isset($fechaOverrides[$fecha->id]),
                ];

                // Gather all results for this pilot in this fecha
                $resultadosPiloto = [];
                foreach ($fecha->sesiones as $sesion) {
                    $res = $sesion->resultados->where('piloto_id', $pilotoId)->first();
                    if ($res) {
                        $resultadosPiloto[] = ['sesion' => $sesion, 'resultado' => $res];
                    }
                }

                // Check if excluded from ALL sessions
                $totalResults  = count($resultadosPiloto);
                $excluidoCount = count(array_filter($resultadosPiloto, fn($r) => $r['resultado']->excluido));
                $excluidoDelEvento = $totalResults > 0 && $excluidoCount === $totalResults;

                if ($excluidoDelEvento) {
                    $fechaData['excluido_evento'] = true;
                    $standings[$pilotoId]['fechas'][$fecha->id] = $fechaData;
                    continue;
                }

                // Award presentation points
                $fechaData['presentacion'] = $scoringMap['presentacion']['all'] ?? 0;

                $seriePtsMax = null;

                foreach ($resultadosPiloto as $r) {
                    $sesion    = $r['sesion'];
                    $resultado = $r['resultado'];

                    if ($resultado->excluido || is_null($resultado->posicion)) continue;

                    $tipoScoring = self::TIPO_MAP[$sesion->tipo] ?? null;
                    if (!$tipoScoring) continue;

                    // Source priority: DB points (if present), else Rule calculation
                    $pts = $resultado->puntos ?? 0;

                    if ($tipoScoring === 'clasificacion') {
                        $fechaData['clasificacion'] += $pts;
                    } elseif ($tipoScoring === 'serie') {
                        // For series, we still might want the max if multiple, but here we'll just sum or take max
                        if ($seriePtsMax === null || $pts > $seriePtsMax) {
                            $seriePtsMax = $pts;
                        }
                    } elseif ($tipoScoring === 'final') {
                        $fechaData['final'] += $pts;
                    }
                }

                if ($seriePtsMax !== null) {
                    $fechaData['series'] = $seriePtsMax;
                }

                $fechaData['total'] = $fechaData['presentacion']
                    + $fechaData['clasificacion']
                    + $fechaData['series']
                    + $fechaData['final'];

                $standings[$pilotoId]['fechas'][$fecha->id] = $fechaData;
                $standings[$pilotoId]['total'] += $fechaData['total'];
            }
        }

        usort($standings, fn($a, $b) => $b['total'] <=> $a['total']);
        foreach ($standings as $i => &$row) {
            $row['posicion'] = $i + 1;
        }

        return $standings;
    }

    /**
     * Persist calculated points to the database.
     */
    public function sincronizar(Campeonato $campeonato): void
    {
        DB::transaction(function () use ($campeonato) {
            // 1. Update individual session points for ALL fechas in the campeonato
            // Note: This OVERWRITES manual points from rules.
            foreach ($campeonato->fechas as $fecha) {
                $this->syncFechaPuntos($fecha);
            }

            // 2. Update championship totals (standings)
            $this->syncChampionshipTotals($campeonato);
        });
    }

    /**
     * Recalculate standings totals (Ranking) based on CURRENT points inresultados_sesion.
     */
    public function syncChampionshipTotals(Campeonato $campeonato): void
    {
        $standings = $this->calcular($campeonato);

        foreach ($standings as $row) {
            if (!isset($row['piloto']) || !$row['piloto']) continue;

            PosicionCampeonato::updateOrCreate(
                [
                    'campeonato_id' => $campeonato->id,
                    'piloto_id'     => $row['piloto']->id,
                ],
                [
                    'puntos_totales' => $row['total'],
                ]
            );
        }
    }

    /**
     * Synchronize points for a specific fecha and save them in the database.
     */
    public function syncFechaPuntos(\App\Models\Fecha $fecha): void
    {
        $fecha->load(['sesiones.resultados', 'campeonato']);
        $campeonato = $fecha->campeonato;

        if (!$campeonato) return;

        // Determine scoring map for this fecha
        $scoring = SistemaPuntajeFecha::where('fecha_id', $fecha->id)->get();
        if ($scoring->isEmpty()) {
            $scoring = SistemaPuntaje::where('campeonato_id', $campeonato->id)->get();
        }
        $scoringMap = $this->buildMap($scoring);

        // Map: pilot_id => boolean (has received presentation points in this fecha)
        $puntosPresenciaEntregados = [];
        $puntosPresenciaValor = $scoringMap['presentacion']['all'] ?? 0;

        // Sort sessions by ORDEN to ensure we award presence points to the first available scoring session
        $sesionesOrdenadas = $fecha->sesiones->sortBy(function($s) {
            return SesionDefinicion::ORDEN[$s->tipo] ?? 99;
        });

        foreach ($sesionesOrdenadas as $sesion) {
            $tipoScoring = self::TIPO_MAP[$sesion->tipo] ?? null;
            
            foreach ($sesion->resultados as $resultado) {
                $puntosFinales = 0;

                // 1. Calculate rank points if applicable
                if ($tipoScoring && !$resultado->excluido && !is_null($resultado->posicion)) {
                    $pos = (int)$resultado->posicion;
                    $puntosFinales = $scoringMap[$tipoScoring][$pos] ?? 0;
                }

                // 2. We NO LONGER add presentation points here. 
                // Presence points are awarded dynamically by the View/Standings calculation
                // to avoid double counting and breakdown confusion.

                $resultado->update(['puntos' => $puntosFinales]);
            }
        }
    }

    /**
     * Seed the campeonato-level default scoring if none exists.
     */
    public static function seedDefaultScoring(string $campeonatoId): void
    {
        if (SistemaPuntaje::where('campeonato_id', $campeonatoId)->exists()) return;

        foreach (SistemaPuntaje::DEFAULT_SCORING as $row) {
            SistemaPuntaje::create([
                'campeonato_id' => $campeonatoId,
                'tipo_sesion'   => $row['tipo_sesion'],
                'posicion'      => $row['posicion'],
                'puntos'        => $row['puntos'],
            ]);
        }
    }

    /**
     * Seed fecha-level override from campeonato defaults.
     */
    public static function seedFechaScoring(string $fechaId, string $campeonatoId): void
    {
        if (SistemaPuntajeFecha::where('fecha_id', $fechaId)->exists()) return;

        $campeonatoRows = SistemaPuntaje::where('campeonato_id', $campeonatoId)->get();
        foreach ($campeonatoRows as $row) {
            SistemaPuntajeFecha::create([
                'fecha_id'    => $fechaId,
                'tipo_sesion' => $row->tipo_sesion,
                'posicion'    => $row->posicion,
                'puntos'      => $row->puntos,
            ]);
        }
    }
}
