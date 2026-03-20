<div class="card-modern">
    <div class="card-header-modern d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-semibold">{{ $title }}</h5>
        <div class="d-flex align-items-center gap-2">
            <div class="px-4 py-3">
                <input type="text" id="searchInput" class="input-modern"
                    placeholder="Buscar..." autocomplete="off"
                    value="{{ request('search') }}">
            </div>
            @if(isset($createRoute))
            <a href="{{ $createRoute }}" class="btn-modern btn-primary-modern">
                <i class="fas fa-plus me-1"></i> {{ $createText ?? 'Crear' }}
            </a>
            @endif
            @if(isset($extraButtons))
                @foreach($extraButtons as $btn)
                <a href="{{ $btn['url'] }}" class="btn-modern {{ $btn['class'] ?? 'btn-secondary-modern' }}">
                    @if(isset($btn['icon'])) <i class="{{ $btn['icon'] }} me-1"></i> @endif
                    {{ $btn['text'] }}
                </a>
                @endforeach
            @endif
        </div>
    </div>

    {{-- Incluir filtros si están configurados --}}
    @if(isset($filters) && count($filters) > 0)
    <div class="card-body-modern" style="border-bottom: 1px solid hsl(var(--border));">
        @include('components.partials.filters', [
        'filters' => $filters,
        'filterOptions' => $filterOptions ?? []
        ])
    </div>
    @endif

    <div class="p-0">
        @if(isset($requireFilter) && $requireFilter)
        {{-- Estado: requiere seleccionar filtro primero --}}
        <div class="text-center py-5" id="require-filter-state">
            <div class="mb-3">
                <i class="{{ $requireFilterIcon ?? 'fas fa-filter' }}" style="font-size: 3rem; color: hsl(var(--muted-foreground)); opacity: 0.5;"></i>
            </div>
            <h6 class="mb-2" style="color: hsl(var(--foreground));">
                Seleccioná un campeonato
            </h6>
            <p class="mb-0" style="color: hsl(var(--muted-foreground)); font-size: 0.875rem;">
                {{ $requireFilterMessage ?? 'Seleccioná un filtro para ver los resultados' }}
            </p>
        </div>
        @elseif($items->count() > 0)
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
            'nameField' => $nameField ?? 'name',
            'rowActions' => $rowActions ?? null
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
                    ? ($emptyMessage ?? 'Intenta con otros términos de búsqueda o filtros')
                    : ($emptyMessage ?? 'No hay elementos para mostrar') }}
            </p>
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
                    const contentArea = document.querySelector('.card-modern .p-0');
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = html;

                    const hasRequireFilter = html.includes('id="require-filter-state"');
                    const hasTableData = html.includes('<table') || html.includes('<tr');

                    // Clear all existing states
                    const currentTable = document.getElementById('table-container');
                    const currentEmpty = document.getElementById('empty-state');
                    const currentRequire = document.getElementById('require-filter-state');
                    if (currentTable) currentTable.remove();
                    if (currentEmpty) currentEmpty.remove();
                    if (currentRequire) currentRequire.remove();

                    if (hasRequireFilter) {
                        contentArea.innerHTML = html;
                    } else if (hasTableData) {
                        const tableDiv = document.createElement('div');
                        tableDiv.id = 'table-container';
                        tableDiv.innerHTML = html;
                        contentArea.innerHTML = '';
                        contentArea.appendChild(tableDiv);
                    } else {
                        contentArea.innerHTML = '';
                        const emptyDiv = document.createElement('div');
                        emptyDiv.className = 'text-center py-5';
                        emptyDiv.id = 'empty-state';
                        emptyDiv.innerHTML = `
                            <div class="mb-3">
                                <i class="fas fa-inbox" style="font-size: 3rem; color: hsl(var(--muted-foreground)); opacity: 0.5;"></i>
                            </div>
                            <h6 class="mb-2" style="color: hsl(var(--foreground));">No se encontraron resultados</h6>
                            <p class="mb-0" style="color: hsl(var(--muted-foreground)); font-size: 0.875rem;">
                                No hay pilotos para este campeonato
                            </p>
                        `;
                        contentArea.appendChild(emptyDiv);
                    }

                    // Actualizar URL del navegador sin recargar
                    history.pushState({}, '', searchUrl);
                })
                .catch(error => {
                    console.error('Error en la búsqueda:', error);
                })
                .finally(() => {
                    // Remover indicador de carga
                    const tc = document.getElementById('table-container');
                    if (tc) {
                        tc.style.opacity = '1';
                        tc.style.pointerEvents = 'auto';
                    }
                });
        }

        // Manejar clics en paginación
        document.addEventListener('click', function(e) {
            const paginationLink = e.target.closest('a[href*="page="]');
            if (paginationLink) {
                e.preventDefault();

                const url = new URL(paginationLink.href, window.location.origin);
                url.protocol = window.location.protocol;
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
                const currentTableContainer = document.getElementById('table-container');
                if (currentTableContainer) {
                    currentTableContainer.style.opacity = '0.6';
                    currentTableContainer.style.pointerEvents = 'none';
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
                        const tc = document.getElementById('table-container');
                        if (tc) {
                            const tempDiv = document.createElement('div');
                            tempDiv.id = 'table-container';
                            tempDiv.innerHTML = html;
                            tc.innerHTML = tempDiv.innerHTML;
                        }
                        // Actualizar URL del navegador
                        history.pushState({}, '', url.href);
                    })
                    .catch(error => {
                        console.error('Error en la paginación:', error);
                    })
                    .finally(() => {
                        // Remover indicador de carga
                        const tc = document.getElementById('table-container');
                        if (tc) {
                            tc.style.opacity = '1';
                            tc.style.pointerEvents = 'auto';
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