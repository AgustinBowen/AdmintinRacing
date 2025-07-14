@if(count($filters) > 0)
<div class="card-modern mb-4">
    <div class="card-body-modern">
        <div class="d-flex align-items-center justify-content-between mb-3">
            {{-- Botón para limpiar filtros --}}
            @if(collect($filters)->some(function($filter) { return !empty(request($filter['key'])); }))
                <button type="button" id="clearFilters" class="btn-secondary-modern btn-modern">
                    <i class="fas fa-times me-1"></i>
                    Limpiar filtros
                </button>
            @endif
        </div>
        
        <div class="filters-grid">
            @foreach($filters as $filter)
                @php
                    $filterKey = $filter['key'];
                    $filterValue = request($filterKey);
                    $filterType = $filter['type'] ?? 'select';
                @endphp

                <div class="filter-item-modern">
                    <label for="filter_{{ $filterKey }}" class="filter-label">
                        {{ $filter['label'] ?? ucfirst(str_replace('_', ' ', $filterKey)) }}
                    </label>
                    
                    @switch($filterType)
                        @case('select')
                            <select name="{{ $filterKey }}" 
                                    id="filter_{{ $filterKey }}"
                                    class="input-modern filter-select">
                                <option value="">{{ $filter['placeholder'] ?? 'Seleccionar...' }}</option>
                                @if(isset($filterOptions[$filterKey]))
                                    @foreach($filterOptions[$filterKey] as $value => $label)
                                        <option value="{{ $value }}" {{ $filterValue == $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            @break

                        @case('date')
                            <input type="date" 
                                   name="{{ $filterKey }}" 
                                   id="filter_{{ $filterKey }}"
                                   class="input-modern filter-input"
                                   value="{{ $filterValue }}"
                                   placeholder="{{ $filter['placeholder'] ?? 'Seleccionar fecha' }}">
                            @break

                        @case('date_range')
                            <input type="text" 
                                   name="{{ $filterKey }}" 
                                   id="filter_{{ $filterKey }}"
                                   class="input-modern filter-daterange"
                                   value="{{ $filterValue }}"
                                   placeholder="{{ $filter['placeholder'] ?? 'Rango de fechas' }}"
                                   readonly>
                            @break

                        @case('boolean')
                            <select name="{{ $filterKey }}" 
                                    id="filter_{{ $filterKey }}"
                                    class="input-modern filter-select">
                                <option value="">{{ $filter['placeholder'] ?? 'Todos' }}</option>
                                <option value="1" {{ $filterValue == '1' ? 'selected' : '' }}>
                                    <i class="fas fa-check me-1"></i>Sí
                                </option>
                                <option value="0" {{ $filterValue == '0' ? 'selected' : '' }}>
                                    <i class="fas fa-times me-1"></i>No
                                </option>
                            </select>
                            @break

                        @case('number_range')
                            <input type="text" 
                                   name="{{ $filterKey }}" 
                                   id="filter_{{ $filterKey }}"
                                   class="input-modern filter-input"
                                   value="{{ $filterValue }}"
                                   placeholder="{{ $filter['placeholder'] ?? 'Ej: 100-500' }}">
                            @break

                        @case('text')
                            <div class="input-group-modern">
                                <span class="input-group-icon">
                                    <i class="fas fa-search"></i>
                                </span>
                                <input type="text" 
                                       name="{{ $filterKey }}" 
                                       id="filter_{{ $filterKey }}"
                                       class="input-modern filter-input"
                                       value="{{ $filterValue }}"
                                       placeholder="{{ $filter['placeholder'] ?? 'Buscar...' }}">
                            </div>
                            @break

                        @case('relation')
                            <select name="{{ $filterKey }}" 
                                    id="filter_{{ $filterKey }}"
                                    class="input-modern filter-select"
                                    {{ isset($filter['multiple']) && $filter['multiple'] ? 'multiple' : '' }}>
                                @if(!isset($filter['multiple']) || !$filter['multiple'])
                                    <option value="">{{ $filter['placeholder'] ?? 'Seleccionar...' }}</option>
                                @endif
                                @if(isset($filterOptions[$filterKey]))
                                    @foreach($filterOptions[$filterKey] as $value => $label)
                                        @if(isset($filter['multiple']) && $filter['multiple'])
                                            <option value="{{ $value }}" 
                                                    {{ in_array($value, (array)$filterValue) ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @else
                                            <option value="{{ $value }}" {{ $filterValue == $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endif
                                    @endforeach
                                @endif
                            </select>
                            @break
                    @endswitch
                    
                    {{-- Indicador de filtro activo --}}
                    @if(!empty($filterValue))
                        <div class="filter-active-indicator">
                            <span class="badge-primary badge-modern">
                                <i class="fas fa-check me-1"></i>Activo
                            </span>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
        
        {{-- Resumen de filtros activos --}}
        @php
            $activeFilters = collect($filters)->filter(function($filter) {
                return !empty(request($filter['key']));
            });
        @endphp
        
        @if($activeFilters->count() > 0)
            <div class="active-filters-summary mt-3 pt-3" style="border-top: 1px solid hsl(var(--border));">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="fw-medium" style="color: hsl(var(--muted-foreground)); font-size: 0.875rem;">
                        Filtros activos:
                    </span>
                    @foreach($activeFilters as $filter)
                        @php
                            $filterKey = $filter['key'];
                            $filterValue = request($filterKey);
                            $displayValue = $filterValue;
                            
                            // Obtener valor legible para selects
                            if(isset($filterOptions[$filterKey]) && isset($filterOptions[$filterKey][$filterValue])) {
                                $displayValue = $filterOptions[$filterKey][$filterValue];
                            }
                        @endphp
                        <span class="badge-secondary badge-modern">
                            {{ $filter['label'] ?? ucfirst(str_replace('_', ' ', $filterKey)) }}: 
                            <strong>{{ $displayValue }}</strong>
                        </span>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>

{{-- Estilos adicionales para los filtros --}}
<style>
.filters-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    align-items: end;
}

.filter-item-modern {
    position: relative;
}

.filter-label {
    display: block;
    font-size: 0.875rem;
    font-weight: 500;
    color: hsl(var(--foreground));
    margin-bottom: 0.5rem;
}

.input-group-modern {
    position: relative;
}

.input-group-icon {
    position: absolute;
    left: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    color: hsl(var(--muted-foreground));
    z-index: 1;
    pointer-events: none;
}

.input-group-modern .input-modern {
    padding-left: 2.5rem;
}

.filter-active-indicator {
    position: absolute;
    top: -0.5rem;
    right: -0.5rem;
    z-index: 10;
}

.active-filters-summary {
    background-color: hsl(var(--muted) / 0.3);
    border-radius: var(--radius);
    padding: 0.75rem;
}

/* Mejoras para select múltiple */
select[multiple].input-modern {
    min-height: 100px;
    padding: 0.5rem;
}

select[multiple].input-modern option {
    padding: 0.25rem 0.5rem;
    margin: 0.125rem 0;
    border-radius: calc(var(--radius) * 0.5);
}

select[multiple].input-modern option:checked {
    background-color: hsl(var(--accent));
    color: hsl(var(--accent-foreground));
}

/* Loading state */
.filters-loading {
    position: relative;
    opacity: 0.6;
    pointer-events: none;
}

.filters-loading::after {
    content: "";
    position: absolute;
    top: 50%;
    left: 50%;
    width: 1.5rem;
    height: 1.5rem;
    margin: -0.75rem 0 0 -0.75rem;
    border: 2px solid hsl(var(--border));
    border-top-color: hsl(var(--accent));
    border-radius: 50%;
    animation: spin 1s linear infinite;
    z-index: 1000;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .filters-grid {
        grid-template-columns: 1fr;
        gap: 0.75rem;
    }
    
    .active-filters-summary .d-flex {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
}
</style>

{{-- Script mejorado para manejo de filtros --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const filtersContainer = document.querySelector('.card-modern');
    let filterTimeout;

    // Manejar cambios en filtros
    document.addEventListener('change', function(e) {
        if (e.target.matches('.filter-select, .filter-input')) {
            showLoadingState();
            applyFilters();
        }
    });

    // Manejar input en filtros de texto (con debounce)
    document.addEventListener('input', function(e) {
        if (e.target.matches('.filter-input')) {
            clearTimeout(filterTimeout);
            showLoadingState();
            filterTimeout = setTimeout(function() {
                applyFilters();
            }, 800);
        }
    });

    // Inicializar date range picker si existe
    const dateRangeInputs = document.querySelectorAll('.filter-daterange');
    if (dateRangeInputs.length > 0 && typeof flatpickr !== 'undefined') {
        dateRangeInputs.forEach(input => {
            flatpickr(input, {
                mode: 'range',
                dateFormat: 'Y-m-d',
                locale: 'es',
                onChange: function(selectedDates, dateStr, instance) {
                    if (selectedDates.length === 2) {
                        showLoadingState();
                        applyFilters();
                    }
                }
            });
        });
    }

    // Limpiar filtros
    document.getElementById('clearFilters')?.addEventListener('click', function() {
        // Mostrar confirmación
        if (confirm('¿Estás seguro de que quieres limpiar todos los filtros?')) {
            showLoadingState();
            
            // Limpiar todos los filtros
            document.querySelectorAll('.filter-select, .filter-input, .filter-daterange').forEach(filter => {
                filter.value = '';
                if (filter.tagName === 'SELECT' && filter.multiple) {
                    Array.from(filter.options).forEach(option => option.selected = false);
                }
            });
            
            // Aplicar filtros vacíos
            applyFilters();
        }
    });

    function showLoadingState() {
        if (filtersContainer) {
            filtersContainer.classList.add('filters-loading');
        }
        
        const tableContainer = document.getElementById('table-container');
        if (tableContainer) {
            tableContainer.style.opacity = '0.6';
            tableContainer.style.pointerEvents = 'none';
        }
    }

    function hideLoadingState() {
        if (filtersContainer) {
            filtersContainer.classList.remove('filters-loading');
        }
        
        const tableContainer = document.getElementById('table-container');
        if (tableContainer) {
            tableContainer.style.opacity = '1';
            tableContainer.style.pointerEvents = 'auto';
        }
    }

    function applyFilters() {
        const currentUrl = new URL(window.location.href);
        const searchParams = new URLSearchParams();
        
        // Mantener búsqueda si existe
        const searchInput = document.getElementById('searchInput');
        if (searchInput && searchInput.value.trim()) {
            searchParams.set('search', searchInput.value.trim());
        }
        
        // Agregar filtros
        document.querySelectorAll('.filter-select, .filter-input, .filter-daterange').forEach(filter => {
            const value = filter.value.trim();
            if (value) {
                if (filter.multiple) {
                    const selectedValues = Array.from(filter.selectedOptions).map(option => option.value);
                    if (selectedValues.length > 0) {
                        searchParams.set(filter.name, selectedValues.join(','));
                    }
                } else {
                    searchParams.set(filter.name, value);
                }
            }
        });
        
        // Remover página para resetear paginación
        searchParams.delete('page');
        
        // Construir URL final
        const filterUrl = currentUrl.pathname + '?' + searchParams.toString();
        
        // Realizar petición AJAX
        fetch(filterUrl, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Error en la respuesta del servidor');
            }
            return response.text();
        })
        .then(html => {
            // Actualizar contenido de la tabla
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = html;
            const newTableContainer = tempDiv.querySelector('#table-container');
            const newEmptyState = tempDiv.querySelector('#empty-state');
            const currentTableContainer = document.getElementById('table-container');
            
            if (newTableContainer && currentTableContainer) {
                currentTableContainer.innerHTML = newTableContainer.innerHTML;
                currentTableContainer.style.display = 'block';
                document.getElementById('empty-state')?.remove();
            } else if (newEmptyState) {
                if (currentTableContainer) {
                    currentTableContainer.style.display = 'none';
                }
                // Insertar estado vacío
                const emptyStateContainer = document.querySelector('.card-modern .p-0') || document.body;
                const existingEmptyState = document.getElementById('empty-state');
                if (existingEmptyState) {
                    existingEmptyState.remove();
                }
                emptyStateContainer.appendChild(newEmptyState);
            }
            
            // Actualizar URL del navegador
            history.pushState({}, '', filterUrl);
            
        })
        .catch(error => {
            console.error('Error aplicando filtros:', error);
        })
        .finally(() => {
            hideLoadingState();
        });
    }
});
</script>
@endif