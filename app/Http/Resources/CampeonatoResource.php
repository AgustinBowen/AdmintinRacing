<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CampeonatoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // If we are showing standings:
        if ($this->relationLoaded('posicionesCampeonato') && $this->relationLoaded('pilotos')) {
            $numeroAutoMap = $this->pilotos->mapWithKeys(fn($p) => [$p->id => $p->pivot->numero_auto]);

            $standings = $this->posicionesCampeonato
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
                'id'       => $this->id,
                'nombre'   => $this->nombre,
                'anio'     => $this->anio,
                'standings' => $standings,
            ];
        }

        // Default representation (just the basic info)
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'anio' => $this->anio,
        ];
    }
}
