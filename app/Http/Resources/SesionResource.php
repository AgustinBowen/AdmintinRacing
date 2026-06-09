<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SesionResource extends JsonResource
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
        $resultados = collect();
        
        if ($this->relationLoaded('resultados')) {
            $resultados = $this->resultados
                ->sortBy(fn($r) => $r->excluido ? 9999 : ($r->posicion ?? 9998))
                ->values()
                ->map(fn($r) => new ResultadoResource($r, $this->numeroAutoMap, $this->fechaId));
        }

        return [
            'id'         => $this->id,
            'tipo'       => $this->tipo,
            'nombre'     => \App\Models\SesionDefinicion::TIPOS[$this->tipo] ?? $this->tipo,
            'resultados' => $resultados,
        ];
    }
}
