<?php

namespace App\Services;

use App\Models\ResultadoSesion;
use App\Models\SesionDefinicion;
use App\Helpers\TimeHelper;

class ResultImportService
{
    /**
     * Bulk imports session results and synchronizes championship points.
     * Returns the number of imported records.
     */
    public function importResults(string $sesionId, array $items): int
    {
        $guardados = 0;

        foreach ($items as $item) {
            if (empty($item['piloto_id'])) {
                continue;
            }

            ResultadoSesion::updateOrCreate([
                'sesion_id' => $sesionId,
                'piloto_id' => $item['piloto_id']
            ], [
                'posicion'           => (isset($item['posicion']) && in_array(strtoupper(trim($item['posicion'])), ['NT', 'EX'])) ? null : ($item['posicion'] ?? null),
                'vueltas'            => $item['vueltas'] ?? null,
                'tiempo_total'       => TimeHelper::timeToSeconds($item['tiempo_total'] ?? null),
                'mejor_tiempo'       => TimeHelper::timeToSeconds($item['mejor_tiempo'] ?? null),
                'diferencia_primero' => TimeHelper::timeToSeconds($item['diferencia'] ?? null),
                'sector_1'           => TimeHelper::timeToSeconds($item['sector_1'] ?? null),
                'sector_2'           => TimeHelper::timeToSeconds($item['sector_2'] ?? null),
                'sector_3'           => TimeHelper::timeToSeconds($item['sector_3'] ?? null),
                'excluido'           => (isset($item['posicion']) && strtoupper(trim($item['posicion'])) === 'EX') ? 'true' : 'false',
            ]);
            $guardados++;
        }

        $sesion = SesionDefinicion::find($sesionId);
        
        if ($sesion) {
            (new \App\Services\StandingsService())->syncFechaPuntos($sesion->fecha);
        }

        return $guardados;
    }
}
