<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FechaResource extends JsonResource
{
    protected $numeroAutoMap;
    protected $campeonatoInfo;
    protected $isNext;

    public function __construct($resource, $numeroAutoMap = null, $campeonatoInfo = null, $isNext = false)
    {
        parent::__construct($resource);
        $this->numeroAutoMap = $numeroAutoMap ?? collect();
        $this->campeonatoInfo = $campeonatoInfo ?? ($resource->campeonato ? ['id' => $resource->campeonato->id, 'nombre' => $resource->campeonato->nombre, 'anio' => $resource->campeonato->anio] : null);
        $this->isNext = $isNext;
    }

    public function toArray(Request $request): array
    {
        $res = [
            'id'               => $this->id,
            'nombre'           => $this->nombre,
            'fecha_desde'      => $this->fecha_desde?->toDateString(),
            'fecha_hasta'      => $this->fecha_hasta?->toDateString(),
            'campeonato'       => $this->campeonatoInfo,
            'circuitoNombre'   => $this->circuito?->nombre ?? ($this->isNext ? null : 'Circuito no especificado'),
            'circuitoDistancia'=> $this->circuito?->distancia,
        ];

        // The "next" endpoint in the original code didn't return campeonato_id, status, winner, or sesiones.
        if (!$this->isNext) {
            $today = now()->startOfDay();
            $fechaDate = $this->fecha_desde;

            if ($fechaDate < $today) {
                $status = 'completed';
            } elseif ($fechaDate->toDateString() === $today->toDateString()) {
                $status = 'live';
            } else {
                $status = 'upcoming';
            }

            $res['campeonato_id'] = $this->campeonato_id;
            $res['status'] = $status;

            if ($this->relationLoaded('sesiones')) {
                $sesionesPorTipo = $this->sesiones->keyBy('tipo');
                $carreraFinal = $sesionesPorTipo->get('carrera_final');
                $winner = null;
                if ($carreraFinal && $carreraFinal->relationLoaded('resultados')) {
                    $ganador = $carreraFinal->resultados->where('posicion', 1)->first();
                    $winner = $ganador?->piloto?->nombre ?? null;
                }

                $orden = \App\Models\SesionDefinicion::ORDEN;
                $sesionesOrdenadas = $this->sesiones->sortBy(fn($s) => $orden[$s->tipo] ?? 99)->values();

                $res['winner'] = $winner;
                $res['sesiones'] = $sesionesOrdenadas->map(fn($s) => new SesionResource($s, $this->numeroAutoMap, $this->id));
            }
        }

        return $res;
    }
}
