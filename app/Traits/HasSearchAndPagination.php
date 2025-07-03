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
     * Aplicar filtros dinámicos a una consulta
     */
    protected function applyFilters(Builder $query, Request $request, array $filters = [])
    {
        foreach ($filters as $filter) {
            $filterKey = $filter['key'];
            $filterValue = $request->get($filterKey);

            // Si no hay valor para este filtro, continuar
            if (empty($filterValue) && $filterValue !== '0') {
                continue;
            }

            $filterType = $filter['type'] ?? 'select';
            $field = $filter['field'] ?? $filterKey;

            switch ($filterType) {
                case 'select':
                    $this->applySelectFilter($query, $field, $filterValue);
                    break;

                case 'date_range':
                    $this->applyDateRangeFilter($query, $field, $filterValue, $filter);
                    break;

                case 'date':
                    $this->applyDateFilter($query, $field, $filterValue);
                    break;

                case 'boolean':
                    $this->applyBooleanFilter($query, $field, $filterValue);
                    break;

                case 'number_range':
                    $this->applyNumberRangeFilter($query, $field, $filterValue, $filter);
                    break;

                case 'relation':
                    $this->applyRelationFilter($query, $field, $filterValue);
                    break;

                case 'custom':
                    if (isset($filter['callback']) && is_callable($filter['callback'])) {
                        $filter['callback']($query, $filterValue, $request);
                    }
                    break;
            }
        }

        return $query;
    }

    /**
     * Aplicar filtro de selección
     */
    private function applySelectFilter(Builder $query, string $field, $value)
    {
        if (str_contains($field, '.')) {
            [$relationName, $relationField] = explode('.', $field, 2);
            $query->whereHas($relationName, function ($relationQuery) use ($relationField, $value) {
                $relationQuery->where($relationField, $value);
            });
        } else {
            $query->where($field, $value);
        }
    }

    /**
     * Aplicar filtro de rango de fechas
     */
    private function applyDateRangeFilter(Builder $query, string $field, $value, array $filter)
    {
        $dates = explode(' - ', $value);
        if (count($dates) === 2) {
            $startDate = $dates[0];
            $endDate = $dates[1];

            if (str_contains($field, '.')) {
                [$relationName, $relationField] = explode('.', $field, 2);
                $query->whereHas($relationName, function ($relationQuery) use ($relationField, $startDate, $endDate) {
                    $relationQuery->whereBetween($relationField, [$startDate, $endDate]);
                });
            } else {
                $query->whereBetween($field, [$startDate, $endDate]);
            }
        }
    }

    /**
     * Aplicar filtro de fecha
     */
    private function applyDateFilter(Builder $query, string $field, $value)
    {
        if (str_contains($field, '.')) {
            [$relationName, $relationField] = explode('.', $field, 2);
            $query->whereHas($relationName, function ($relationQuery) use ($relationField, $value) {
                $relationQuery->whereDate($relationField, $value);
            });
        } else {
            $query->whereDate($field, $value);
        }
    }

    /**
     * Aplicar filtro booleano
     */
    private function applyBooleanFilter(Builder $query, string $field, $value)
    {
        $boolValue = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        
        if ($boolValue !== null) {
            if (str_contains($field, '.')) {
                [$relationName, $relationField] = explode('.', $field, 2);
                $query->whereHas($relationName, function ($relationQuery) use ($relationField, $boolValue) {
                    $relationQuery->where($relationField, $boolValue);
                });
            } else {
                $query->where($field, $boolValue);
            }
        }
    }

    /**
     * Aplicar filtro de rango numérico
     */
    private function applyNumberRangeFilter(Builder $query, string $field, $value, array $filter)
    {
        $range = explode('-', $value);
        if (count($range) === 2) {
            $min = (float) $range[0];
            $max = (float) $range[1];

            if (str_contains($field, '.')) {
                [$relationName, $relationField] = explode('.', $field, 2);
                $query->whereHas($relationName, function ($relationQuery) use ($relationField, $min, $max) {
                    $relationQuery->whereBetween($relationField, [$min, $max]);
                });
            } else {
                $query->whereBetween($field, [$min, $max]);
            }
        }
    }

    /**
     * Aplicar filtro de relación
     */
    private function applyRelationFilter(Builder $query, string $field, $value)
    {
        if (str_contains($field, '.')) {
            [$relationName, $relationField] = explode('.', $field, 2);
            $query->whereHas($relationName, function ($relationQuery) use ($relationField, $value) {
                if (is_array($value)) {
                    $relationQuery->whereIn($relationField, $value);
                } else {
                    $relationQuery->where($relationField, $value);
                }
            });
        } else {
            if (is_array($value)) {
                $query->whereIn($field, $value);
            } else {
                $query->where($field, $value);
            }
        }
    }

    /**
     * Obtener opciones para filtros de selección
     */
    protected function getFilterOptions(array $filters)
    {
        $options = [];

        foreach ($filters as $filter) {
            if (isset($filter['options'])) {
                if (is_callable($filter['options'])) {
                    $options[$filter['key']] = $filter['options']();
                } elseif (is_array($filter['options'])) {
                    $options[$filter['key']] = $filter['options'];
                }
            }
        }

        return $options;
    }

    /**
     * Manejar respuesta de índice con búsqueda, filtros y paginación
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
            'nameField' => 'name',
            'filters' => []
        ], $config);

        // Aplicar filtros si están configurados
        if (!empty($config['filters'])) {
            $this->applyFilters($query, $request, $config['filters']);
        }

        // Aplicar ordenamiento
        $query->orderBy($config['orderBy'], $config['orderDirection']);

        // Obtener resultados paginados
        $items = $query->paginate($config['perPage']);

        // Mantener parámetros de búsqueda y filtros en la paginación
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