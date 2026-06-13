@if(count($filters) > 0)
<div class="form-card" style="margin-bottom: 32px; max-width: none;">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
        <h5 style="font-family: var(--font-oswald); font-size: 18px; text-transform: uppercase; margin: 0; color: var(--white);">
            Filtros
        </h5>
        @if(collect($filters)->some(function($filter) { return !empty(request($filter['key'])); }))
            <button type="button" id="clearFilters" class="btn ghost" style="padding: 6px 12px; font-size: 12px;">
                <x-heroicon-o-x-mark style="width:1em; height:1em; vertical-align:-0.125em;" /> Limpiar filtros
            </button>
        @endif
    </div>
    
    <div class="fgrid" style="align-items: end;">
        @foreach($filters as $filter)
            @php
                $filterKey = $filter['key'];
                $filterValue = request($filterKey);
                $filterType = $filter['type'] ?? 'select';
            @endphp

            <div class="field" style="position: relative;">
                <label for="filter_{{ $filterKey }}">
                    {{ $filter['label'] ?? ucfirst(str_replace('_', ' ', $filterKey)) }}
                </label>
                
                @switch($filterType)
                    @case('select')
                        <select name="{{ $filterKey }}" 
                                id="filter_{{ $filterKey }}"
                                class="filter-select">
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
                               class="filter-input"
                               value="{{ $filterValue }}"
                               placeholder="{{ $filter['placeholder'] ?? 'Seleccionar fecha' }}">
                        @break

                    @case('date_range')
                        <input type="text" 
                               name="{{ $filterKey }}" 
                               id="filter_{{ $filterKey }}"
                               class="filter-daterange"
                               value="{{ $filterValue }}"
                               placeholder="{{ $filter['placeholder'] ?? 'Rango de fechas' }}"
                               readonly>
                        @break

                    @case('boolean')
                        <select name="{{ $filterKey }}" 
                                id="filter_{{ $filterKey }}"
                                class="filter-select">
                            <option value="">{{ $filter['placeholder'] ?? 'Todos' }}</option>
                            <option value="1" {{ $filterValue == '1' ? 'selected' : '' }}>
                                Sí
                            </option>
                            <option value="0" {{ $filterValue == '0' ? 'selected' : '' }}>
                                No
                            </option>
                        </select>
                        @break

                    @case('number_range')
                        <input type="text" 
                               name="{{ $filterKey }}" 
                               id="filter_{{ $filterKey }}"
                               class="filter-input"
                               value="{{ $filterValue }}"
                               placeholder="{{ $filter['placeholder'] ?? 'Ej: 100-500' }}">
                        @break

                    @case('text')
                        <input type="text" 
                               name="{{ $filterKey }}" 
                               id="filter_{{ $filterKey }}"
                               class="filter-input"
                               value="{{ $filterValue }}"
                               placeholder="{{ $filter['placeholder'] ?? 'Buscar...' }}">
                        @break

                    @case('relation')
                        <select name="{{ $filterKey }}" 
                                id="filter_{{ $filterKey }}"
                                class="filter-select"
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
                
                @if(!empty($filterValue))
                    <div style="position: absolute; top: -5px; right: -5px; width: 8px; height: 8px; background: var(--racing); border-radius: 50%;"></div>
                @endif
            </div>
        @endforeach
    </div>
    
    @php
        $activeFilters = collect($filters)->filter(function($filter) {
            return !empty(request($filter['key']));
        });
    @endphp
    
    @if($activeFilters->count() > 0)
        <div style="margin-top: 24px; padding-top: 24px; border-top: 1px solid var(--carbon); display: flex; flex-wrap: wrap; gap: 8px; align-items: center;">
            <span style="color: var(--gray); font-size: 13px; margin-right: 8px;">
                Filtros activos:
            </span>
            @foreach($activeFilters as $filter)
                @php
                    $filterKey = $filter['key'];
                    $filterValue = request($filterKey);
                    $displayValue = $filterValue;
                    
                    if(isset($filterOptions[$filterKey]) && isset($filterOptions[$filterKey][$filterValue])) {
                        $displayValue = $filterOptions[$filterKey][$filterValue];
                    }
                @endphp
                <span style="background: var(--carbon); border: 1px solid var(--line); color: var(--bone); font-size: 12px; padding: 4px 10px; border-radius: 4px;">
                    {{ $filter['label'] ?? ucfirst(str_replace('_', ' ', $filterKey)) }}: 
                    <strong style="color: var(--white);">{{ $displayValue }}</strong>
                </span>
            @endforeach
        </div>
    @endif
</div>

<style>
/* Loading state */
.filters-loading {
    position: relative;
    opacity: 0.6;
    pointer-events: none;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filtersContainer = document.querySelector('.form-card');
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

    // Limpiar filtros
    document.getElementById('clearFilters')?.addEventListener('click', function() {
        if (confirm('¿Estás seguro de que quieres limpiar todos los filtros?')) {
            showLoadingState();
            
            document.querySelectorAll('.filter-select, .filter-input, .filter-daterange').forEach(filter => {
                filter.value = '';
                if (filter.tagName === 'SELECT' && filter.multiple) {
                    Array.from(filter.options).forEach(option => option.selected = false);
                }
            });
            
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
        
        const searchInput = document.getElementById('searchInput');
        if (searchInput && searchInput.value.trim()) {
            searchParams.set('search', searchInput.value.trim());
        }
        
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
        
        searchParams.delete('page');
        
        const filterUrl = currentUrl.pathname + '?' + searchParams.toString();
        
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
            // Note: Since we are using dark UI, the container structure might have changed from '.card-modern .p-0'
            const contentArea = document.querySelector('.tbl-wrap') ? document.querySelector('.tbl-wrap').parentNode : document.body;
            
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = html;

            const hasRequireFilter = tempDiv.querySelector('#require-filter-state') || html.includes('id="require-filter-state"');
            const hasEmptyState = tempDiv.querySelector('#empty-state');
            const hasTableData = html.includes('<table') || html.includes('<tr');

            const currentTable = document.getElementById('table-container');
            const currentEmpty = document.getElementById('empty-state');
            const currentRequire = document.getElementById('require-filter-state');

            if (currentTable) currentTable.remove();
            if (currentEmpty) currentEmpty.remove();
            if (currentRequire) currentRequire.remove();

            // We append below the form-card (the filters)
            const insertionPoint = filtersContainer.nextElementSibling || contentArea.lastElementChild;

            if (hasRequireFilter) {
                const requireDiv = tempDiv.querySelector('#require-filter-state') || tempDiv;
                insertionPoint.insertAdjacentElement('beforebegin', requireDiv.querySelector('#require-filter-state') || (() => {
                    const d = document.createElement('div');
                    d.innerHTML = html;
                    return d.firstElementChild || d;
                })());
            } else if (hasTableData && !hasEmptyState) {
                const tableDiv = document.createElement('div');
                tableDiv.id = 'table-container';
                // Extract only the tbl-wrap or table container from the new HTML if possible
                const newTbl = tempDiv.querySelector('.tbl-wrap') || tempDiv;
                tableDiv.innerHTML = newTbl.outerHTML || html;
                insertionPoint.insertAdjacentElement('beforebegin', tableDiv);
            } else {
                const emptyDiv = document.createElement('div');
                emptyDiv.className = 'form-card text-center';
                emptyDiv.id = 'empty-state';
                emptyDiv.style.padding = '40px';
                emptyDiv.innerHTML = `
                    <div style="margin-bottom: 16px;">
                        <x-heroicon-o-inbox style="width:1em; height:1em; vertical-align:-0.125em; font-size: 32px; color: var(--gray);" />
                    </div>
                    <h6 style="color: var(--white); font-family: var(--font-oswald); font-size: 18px; text-transform: uppercase;">No se encontraron resultados</h6>
                    <p style="margin: 0; color: var(--gray); font-size: 14px;">
                        No hay elementos que coincidan con estos filtros
                    </p>
                `;
                insertionPoint.insertAdjacentElement('beforebegin', emptyDiv);
            }
            
            history.pushState({}, '', filterUrl);
        })
        .catch(error => {
            console.error('Error aplicando filtros:', error);
            window.location.href = filterUrl; // fallback
        })
        .finally(() => {
            hideLoadingState();
        });
    }
});
</script>
@endif