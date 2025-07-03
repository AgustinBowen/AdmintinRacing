@if(count($filters) > 0)
<div class="filters-container mb-3">
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <span class="fw-semibold text-muted me-2">Filtros:</span>
        
        @foreach($filters as $filter)
            @php
                $filterKey = $filter['key'];
                $filterValue = request($filterKey);
                $filterType = $filter['type'] ?? 'select';
            @endphp

            <div class="filter-item">
                @switch($filterType)
                    @case('select')
                        <select name="{{ $filterKey }}" 
                                id="filter_{{ $filterKey }}"
                                class="form-select form-select-sm filter-select"
                                style="min-width: 150px;">
                            <option value="">{{ $filter['placeholder'] ?? 'Todos' }}</option>
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
                               class="form-control form-control-sm filter-input"
                               value="{{ $filterValue }}"
                               placeholder="{{ $filter['placeholder'] ?? 'Fecha' }}"
                               style="width: 150px;">
                        @break

                    @case('date_range')
                        <input type="text" 
                               name="{{ $filterKey }}" 
                               id="filter_{{ $filterKey }}"
                               class="form-control form-control-sm filter-daterange"
                               value="{{ $filterValue }}"
                               placeholder="{{ $filter['placeholder'] ?? 'Rango de fechas' }}"
                               style="width: 200px;"
                               readonly>
                        @break

                    @case('boolean')
                        <select name="{{ $filterKey }}" 
                                id="filter_{{ $filterKey }}"
                                class="form-select form-select-sm filter-select"
                                style="min-width: 120px;">
                            <option value="">{{ $filter['placeholder'] ?? 'Todos' }}</option>
                            <option value="1" {{ $filterValue == '1' ? 'selected' : '' }}>Sí</option>
                            <option value="0" {{ $filterValue == '0' ? 'selected' : '' }}>No</option>
                        </select>
                        @break

                    @case('number_range')
                        <input type="text" 
                               name="{{ $filterKey }}" 
                               id="filter_{{ $filterKey }}"
                               class="form-control form-control-sm filter-input"
                               value="{{ $filterValue }}"
                               placeholder="{{ $filter['placeholder'] ?? 'Rango numérico (min-max)' }}"
                               style="width: 180px;">
                        @break

                    @case('text')
                        <input type="text" 
                               name="{{ $filterKey }}" 
                               id="filter_{{ $filterKey }}"
                               class="form-control form-control-sm filter-input"
                               value="{{ $filterValue }}"
                               placeholder="{{ $filter['placeholder'] ?? 'Filtrar...' }}"
                               style="width: 150px;">
                        @break

                    @case('relation')
                        <select name="{{ $filterKey }}" 
                                id="filter_{{ $filterKey }}"
                                class="form-select form-select-sm filter-select"
                                style="min-width: 150px;"
                                {{ isset($filter['multiple']) && $filter['multiple'] ? 'multiple' : '' }}>
                            @if(!isset($filter['multiple']) || !$filter['multiple'])
                                <option value="">{{ $filter['placeholder'] ?? 'Todos' }}</option>
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
            </div>
        @endforeach

        {{-- Botón para limpiar filtros --}}
        @if(collect($filters)->some(function($filter) { return !empty(request($filter['key'])); }))
            <button type="button" id="clearFilters" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-times me-1"></i>
                Limpiar filtros
            </button>
        @endif
    </div>
</div>

{{-- Script para manejo de filtros --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Manejar cambios en filtros
    document.addEventListener('change', function(e) {
        if (e.target.matches('.filter-select, .filter-input')) {
            applyFilters();
        }
    });

    // Manejar input en filtros de texto (con debounce)
    let filterTimeout;
    document.addEventListener('input', function(e) {
        if (e.target.matches('.filter-input')) {
            clearTimeout(filterTimeout);
            filterTimeout = setTimeout(function() {
                applyFilters();
            }, 500);
        }
    });

    // Inicializar date range picker si existe
    const dateRangeInputs = document.querySelectorAll('.filter-daterange');
    if (dateRangeInputs.length > 0 && typeof flatpickr !== 'undefined') {
        dateRangeInputs.forEach(input => {
            flatpickr(input, {
                mode: 'range',
                dateFormat: 'Y-m-d',
                onChange: function(selectedDates, dateStr, instance) {
                    if (selectedDates.length === 2) {
                        applyFilters();
                    }
                }
            });
        });
    }

    // Limpiar filtros
    document.getElementById('clearFilters')?.addEventListener('click', function() {
        // Limpiar todos los filtros
        document.querySelectorAll('.filter-select, .filter-input, .filter-daterange').forEach(filter => {
            filter.value = '';
            if (filter.tagName === 'SELECT' && filter.multiple) {
                Array.from(filter.options).forEach(option => option.selected = false);
            }
        });
        
        // Aplicar filtros vacíos
        applyFilters();
    });

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
        
        // Mostrar indicador de carga
        const tableContainer = document.getElementById('table-container');
        if (tableContainer) {
            tableContainer.style.opacity = '0.6';
            tableContainer.style.pointerEvents = 'none';
        }
        
        // Realizar petición AJAX
        fetch(filterUrl, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html'
            }
        })
        .then(response => response.text())
        .then(html => {
            // Actualizar contenido de la tabla
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = html;
            const newTableContainer = tempDiv;
            const newEmptyState = tempDiv.querySelector('#empty-state');
            
            if (newTableContainer && tableContainer) {
                tableContainer.innerHTML = newTableContainer.innerHTML;
                tableContainer.style.display = 'block';
                document.getElementById('empty-state')?.remove();
            } else if (newEmptyState) {
                if (tableContainer) {
                    tableContainer.style.display = 'none';
                }
                // Insertar estado vacío
                const emptyStateContainer = document.querySelector('.card-modern .p-0');
                const existingEmptyState = document.getElementById('empty-state');
                if (existingEmptyState) {
                    existingEmptyState.remove();
                }
                emptyStateContainer.appendChild(newEmptyState);
            }
            
            // Actualizar URL del navegador
            history.pushState({}, '', filterUrl);
            
            // Actualizar botón de limpiar filtros
            updateClearFiltersButton();
        })
        .catch(error => {
            console.error('Error aplicando filtros:', error);
        })
        .finally(() => {
            // Remover indicador de carga
            if (tableContainer) {
                tableContainer.style.opacity = '1';
                tableContainer.style.pointerEvents = 'auto';
            }
        });
    }
    
    function updateClearFiltersButton() {
        const clearButton = document.getElementById('clearFilters');
        const hasActiveFilters = Array.from(document.querySelectorAll('.filter-select, .filter-input, .filter-daterange'))
            .some(filter => filter.value.trim() !== '');
        
        if (clearButton) {
            clearButton.style.display = hasActiveFilters ? 'inline-flex' : 'none';
        }
    }
});
</script>
@endif