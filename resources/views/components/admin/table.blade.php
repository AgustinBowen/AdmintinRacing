<div class="card-modern">
    <div class="card-header-modern d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-semibold">{{ $title }}</h5>
        <div class="d-flex align-items-center gap-2">
            <div class="px-4 py-3">
                <input type="text" id="searchInput" class="form-control form-control-modern"
                    placeholder="Buscar..." autocomplete="off"
                    value="{{ request('search') }}">
            </div>
            @if(isset($createRoute) && $items->count() >= 1)
            <a href="{{ $createRoute }}" class="btn-modern btn-primary-modern">
                <i class="fas fa-plus me-1"></i> {{ $createText ?? 'Crear' }}
            </a>
            @endif
        </div>
    </div>
    
    {{-- Incluir filtros si están configurados --}}
    @if(isset($filters) && count($filters) > 0)
        <div class="card-body-modern border-bottom">
            @include('components.partials.filters', [
                'filters' => $filters,
                'filterOptions' => $filterOptions ?? []
            ])
        </div>
    @endif
    
    <div class="p-0">
        @if($items->count() > 0)
        <div id="table-container">
            @include('components.partials.partial-table', [
                'columns' => $columns,
                'items' => $items,
                'routePrefix' => $routePrefix ?? '',
                'showActions' => $showActions ?? true,
                'showView' => $showView ?? true,
                'showEdit' => $showEdit ?? true,
                'showDelete' => $showDelete ?? true,
                'deleteModalId' => $deleteModalId ?? 'deleteModal',
                'nameField' => $nameField ?? 'name'
            ])
        </div>
        @else
        <div class="text-center py-5" id="empty-state">
            <div class="mb-3">
                <i class="fas fa-inbox" style="font-size: 3rem; color: hsl(var(--muted-foreground)); opacity: 0.5;"></i>
            </div>
            <h6 class="mb-2" style="color: hsl(var(--foreground));">
                {{ request('search') || collect($filters ?? [])->some(function($filter) { return !empty(request($filter['key'])); }) 
                    ? 'No se encontraron resultados' 
                    : 'No hay datos' }}
            </h6>
            <p class="mb-3" style="color: hsl(var(--muted-foreground)); font-size: 0.875rem;">
                {{ request('search') || collect($filters ?? [])->some(function($filter) { return !empty(request($filter['key'])); })
                    ? 'Intenta con otros términos de búsqueda o filtros' 
                    : ($emptyMessage ?? 'No hay elementos para mostrar') }}
            </p>
            @if(isset($createRoute) && !request('search') && !collect($filters ?? [])->some(function($filter) { return !empty(request($filter['key'])); }))
            <a href="{{ $createRoute }}" class="btn-modern btn-primary-modern">
                <i class="fas fa-plus me-1"></i> {{ $createText ?? 'Crear el primero' }}
            </a>
            @endif
        </div>
        @endif
    </div>
</div>

{{-- Incluir el modal de eliminación --}}
@include('components.admin.delete-modal', [
    'modalId' => $deleteModalId ?? 'deleteModal',
    'title' => $deleteModalTitle ?? 'Confirmar Eliminación',
    'message' => $deleteModalMessage ?? '¿Estás seguro de que deseas eliminar',
    'warningText' => $deleteModalWarning ?? 'Esta acción no se puede deshacer.',
    'confirmText' => $deleteModalConfirmText ?? 'Eliminar',
    'cancelText' => $deleteModalCancelText ?? 'Cancelar'
])

{{-- Script para búsqueda dinámica --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const tableContainer = document.getElementById('table-container');
    let searchTimeout;
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            
            searchTimeout = setTimeout(function() {
                performSearch();
            }, 300); // Debounce de 300ms
        });
        
        // Búsqueda al presionar Enter
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                clearTimeout(searchTimeout);
                performSearch();
            }
        });
    }
    
    function performSearch() {
        const searchTerm = searchInput.value.trim();
        const currentUrl = new URL(window.location.href);
        const searchParams = new URLSearchParams(currentUrl.searchParams);
        
        // Agregar/quitar término de búsqueda
        if (searchTerm) {
            searchParams.set('search', searchTerm);
        } else {
            searchParams.delete('search');
        }
        
        // Reset pagination
        searchParams.delete('page');
        
        // Mostrar indicador de carga
        if (tableContainer) {
            tableContainer.style.opacity = '0.6';
            tableContainer.style.pointerEvents = 'none';
        }
        
        // Construir URL para la búsqueda
        const searchUrl = currentUrl.pathname + '?' + searchParams.toString();
        
        // Realizar petición AJAX
        fetch(searchUrl, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html'
            }
        })
        .then(response => response.text())
        .then(html => {
            // Actualizar el contenido de la tabla
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
            
            // Actualizar URL del navegador sin recargar
            history.pushState({}, '', searchUrl);
        })
        .catch(error => {
            console.error('Error en la búsqueda:', error);
        })
        .finally(() => {
            // Remover indicador de carga
            if (tableContainer) {
                tableContainer.style.opacity = '1';
                tableContainer.style.pointerEvents = 'auto';
            }
        });
    }
    
    // Manejar clics en paginación
    document.addEventListener('click', function(e) {
        const paginationLink = e.target.closest('a[href*="page="]');
        if (paginationLink) {
            e.preventDefault();
            
            const url = new URL(paginationLink.href);
            const currentSearch = searchInput.value.trim();
            
            if (currentSearch) {
                url.searchParams.set('search', currentSearch);
            }
            
            // Mantener filtros activos
            const activeFilters = document.querySelectorAll('.filter-select, .filter-input, .filter-daterange');
            activeFilters.forEach(filter => {
                if (filter.value.trim()) {
                    url.searchParams.set(filter.name, filter.value.trim());
                }
            });
            
            // Mostrar indicador de carga
            if (tableContainer) {
                tableContainer.style.opacity = '0.6';
                tableContainer.style.pointerEvents = 'none';
            }
            
            fetch(url.href, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html'
                }
            })
            .then(response => response.text())
            .then(html => {
                const tempDiv = document.createElement('div');
                tempDiv.id = 'table-container';
                tempDiv.innerHTML = html;
                const newTableContainer = tempDiv;          
                tableContainer.innerHTML = newTableContainer.innerHTML;     
                // Actualizar URL del navegador
                history.pushState({}, '', url.href);
            })
            .catch(error => {
                console.error('Error en la paginación:', error);
            })
            .finally(() => {
                // Remover indicador de carga
                if (tableContainer) {
                    tableContainer.style.opacity = '1';
                    tableContainer.style.pointerEvents = 'auto';
                }
            });
        }
    });
    
    // Manejar botón de retroceso del navegador
    window.addEventListener('popstate', function() {
        location.reload();
    });
});
</script>