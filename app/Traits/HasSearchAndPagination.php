<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\Paginator;

trait HasSearchAndPagination
{
    /**
     * Aplicar búsqueda a una consulta
     */
    protected function applySearch(Builder $query, Request $request, array $searchFields = [])
    {
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;

            $query->where(function ($q) use ($searchTerm, $searchFields) {
                foreach ($searchFields as $field) {
                    if (str_contains($field, '.')) {
                        // Búsqueda en relaciones
                        [$relationName, $relationField] = explode('.', $field);

                        $q->orWhereHas($relationName, function ($relationQuery) use ($relationField, $searchTerm) {
                            $relationQuery->whereRaw("unaccent(lower($relationField)) LIKE unaccent(lower(?))", ["%$searchTerm%"]);
                        });
                    } else {
                        // Búsqueda en campos directos
                        $q->orWhereRaw("unaccent(lower($field)) LIKE unaccent(lower(?))", ["%$searchTerm%"]);
                    }
                }
            });
        }

        return $query;
    }


    /**
     * Manejar respuesta de índice con búsqueda y paginación
     */
    protected function handleIndexResponse(
        Request $request,
        Builder $query,
        array $columns,
        string $routePrefix,
        array $config = []
    ) {
        // Configuración por defecto
        $config = array_merge([
            'perPage' => 10,
            'orderBy' => 'id',
            'orderDirection' => 'desc',
            'showActions' => true,
            'showView' => true,
            'showEdit' => true,
            'showDelete' => true,
            'deleteModalId' => 'deleteModal',
            'nameField' => 'name'
        ], $config);

        // Aplicar ordenamiento
        $query->orderBy($config['orderBy'], $config['orderDirection']);

        // Obtener resultados paginados
        $items = $query->paginate($config['perPage']);

        // Mantener parámetros de búsqueda en la paginación
        $items->appends($request->query());

        // Si es una petición AJAX, devolver solo la tabla
        if ($request->ajax()) {
            return view('components.partials.partial-table', [
                'items' => $items,
                'columns' => $columns,
                'routePrefix' => $routePrefix,
                'showActions' => $config['showActions'],
                'showView' => $config['showView'],
                'showEdit' => $config['showEdit'],
                'showDelete' => $config['showDelete'],
                'deleteModalId' => $config['deleteModalId'],
                'nameField' => $config['nameField']
            ])->render();
        }

        return $items;
    }

    /**
     * Configurar vista de paginación
     */
    protected function setupPagination()
    {
        Paginator::defaultView('components.admin.paginator');
    }
}
