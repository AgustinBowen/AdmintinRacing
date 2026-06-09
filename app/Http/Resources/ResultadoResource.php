<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResultadoResource extends JsonResource
{
    protected $numeroAutoMap;
    protected $fechaId;

    public function __construct($resource, $numeroAutoMap, $fechaId)
    {
        parent::__construct($resource);
        $this->numeroAutoMap = $numeroAutoMap;
        $this->fechaId = $fechaId;
    }

    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'fecha_id'           => $this->fechaId,
            'piloto_id'          => $this->piloto_id,
            'piloto'             => $this->piloto ? [
                'id'     => $this->piloto->id,
                'nombre' => $this->piloto->nombre,
                'pais'   => $this->piloto->pais
            ] : null,
            'posicion'           => $this->posicion,
            'puntos'             => $this->puntos,
            'vueltas'            => $this->vueltas,
            'mejor_tiempo'       => $this->mejor_tiempo,
            'tiempo_total'       => $this->tiempo_total,
            'diferencia_primero' => $this->diferencia_primero,
            'sector_1'           => $this->sector_1,
            'sector_2'           => $this->sector_2,
            'sector_3'           => $this->sector_3,
            'excluido'           => $this->excluido,
            'presente'           => $this->presente,
            'numeroAuto'         => $this->numeroAutoMap->get($this->piloto_id),
        ];
    }
}
