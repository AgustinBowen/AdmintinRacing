<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

trait SearchableSelectTrait
{
    /**
     * Búsqueda genérica para selects con AJAX
     */
    public function searchSelect(Request $request, $model, $searchFields = ['nombre'], $relations = [], $customQuery = null)
    {
        $query = $request->get('q', '');
        $limit = $request->get('limit', 10);
        
        // Crear instancia del modelo
        $modelInstance = app($model);
        
        // Aplicar relaciones si existen
        if (!empty($relations)) {
            $modelInstance = $modelInstance->with($relations);
        }
        
        // Aplicar query personalizada si existe
        if ($customQuery && is_callable($customQuery)) {
            $modelInstance = $customQuery($modelInstance);
        }
        
        // Aplicar búsqueda
        if (!empty($query)) {
            $modelInstance = $modelInstance->where(function (Builder $q) use ($query, $searchFields) {
                foreach ($searchFields as $field) {
                    // Soporte para campos relacionados (ej: 'sesion.fecha.nombre')
                    if (str_contains($field, '.')) {
                        $parts = explode('.', $field);
                        $relationField = array_pop($parts);
                        $relationName = implode('.', $parts);
                        
                        $q->orWhereHas($relationName, function (Builder $subQ) use ($relationField, $query) {
                            $subQ->whereRaw("unaccent(lower(CAST($relationField AS TEXT))) LIKE unaccent(lower(?))", ["%$query%"]);
                        });
                    } else {
                        $q->orWhereRaw("unaccent(lower(CAST($field AS TEXT))) LIKE unaccent(lower(?))", ["%$query%"]);
                    }
                }
            });
        }
        
        $results = $modelInstance->limit($limit)->get();
        
        return response()->json([
            'results' => $results->map(function ($item) {
                return [
                    'id' => $item->id,
                    'text' => $this->formatSelectText($item),
                ];
            })
        ]);
    }
    
    /**
     * Método para formatear el texto del select
     * Puede ser sobrescrito en cada controlador
     */
    protected function formatSelectText($item)
    {
        // Formato por defecto
        if (isset($item->nombre)) {
            return $item->nombre;
        }
        
        // Si hay relación con fecha
        if (isset($item->tipo) && isset($item->fecha)) {
            return $item->tipo . ' - ' . ($item->fecha->nombre ?? 'Sin fecha');
        }
        
        return $item->id;
    }
    
    /**
     * Método helper para preparar datos iniciales del select
     */
    protected function prepareSelectData($model, $relations = [], $customQuery = null)
    {
        $modelInstance = app($model);
        
        if (!empty($relations)) {
            $modelInstance = $modelInstance->with($relations);
        }
        
        if ($customQuery && is_callable($customQuery)) {
            $modelInstance = $customQuery($modelInstance);
        }
        
        return $modelInstance->limit(20)->get()->mapWithKeys(function ($item) {
            return [$item->id => $this->formatSelectText($item)];
        });
    }
}