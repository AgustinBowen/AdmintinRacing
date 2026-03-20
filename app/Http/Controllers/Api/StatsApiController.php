<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Campeonato;
use App\Models\Fecha;
use Illuminate\Http\JsonResponse;

class StatsApiController extends Controller
{
    /**
     * GET /api/stats
     * Devuelve estadísticas generales del campeonato del año actual.
     */
    public function index(): JsonResponse
    {
        $currentYear = now()->year;
        $today = now()->toDateString();

        $campeonato = Campeonato::where('anio', $currentYear)->first();

        if (!$campeonato) {
            return response()->json([
                'totalRaces'     => 0,
                'completedRaces' => 0,
                'remainingRaces' => 0,
                'activePilots'   => 0,
            ]);
        }

        $totalRaces = Fecha::where('campeonato_id', $campeonato->id)->count();

        $completedRaces = Fecha::where('campeonato_id', $campeonato->id)
            ->where('fecha_desde', '<', $today)
            ->count();

        $activePilots = $campeonato->pilotos()->count();

        return response()->json([
            'totalRaces'     => $totalRaces,
            'completedRaces' => $completedRaces,
            'remainingRaces' => $totalRaces - $completedRaces,
            'activePilots'   => $activePilots,
        ]);
    }
}
