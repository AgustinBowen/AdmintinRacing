<div class="d-flex justify-content-between align-items-center mb-3" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem; gap:14px; flex-wrap:wrap;">
    <div class="search-box" style="width: min(320px, 46vw);">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
        <input type="text" id="searchInput" placeholder="Buscar..." autocomplete="off" value="{{ request('search') }}">
    </div>
    
    <div class="head-actions">
        @if(isset($createRoute))
        <a href="{{ $createRoute }}" class="btn">+ {{ $createText ?? 'Crear' }}</a>
        @endif
        @if(isset($extraButtons))
            @foreach($extraButtons as $btn)
            <a href="{{ $btn['url'] }}" class="btn {{ str_replace('btn-secondary-modern', 'ghost', $btn['class'] ?? 'ghost') }}">
                @if(isset($btn['icon'])) <i class="{{ $btn['icon'] }} me-1"></i> @endif
                {{ $btn['text'] }}
            </a>
            @endforeach
        @endif
    </div>
</div>

{{-- Incluir filtros si están configurados --}}
@if(isset($filters) && count($filters) > 0)
<div style="margin-bottom:1rem;">
    @include('components.partials.filters', [
        'filters' => $filters,
        'filterOptions' => $filterOptions ?? []
    ])
</div>
@endif

<div class="divider"></div>

<div id="table-container">
    @if(isset($requireFilter) && $requireFilter)
    <div class="empty-hint">
        Seleccioná un campeonato para ver los resultados
    </div>
    @elseif($items->count() > 0)
        @include('components.partials.partial-table', [
            'columns' => $columns,
            'items' => $items,
            'routePrefix' => $routePrefix ?? '',
            'showActions' => $showActions ?? true,
            'showView' => $showView ?? true,
            'showEdit' => $showEdit ?? true,
            'showDelete' => $showDelete ?? true,
            'nameField' => $nameField ?? 'name',
            'rowActions' => $rowActions ?? null
        ])
    @else
    <div class="empty-hint" id="empty-state">
        {{ request('search') || collect($filters ?? [])->some(function($filter) { return !empty(request($filter['key'])); }) 
            ? 'No se encontraron resultados para tu búsqueda' 
            : ($emptyMessage ?? 'No hay elementos para mostrar') }}
    </div>
    @endif
</div>

{{-- Script para búsqueda dinámica --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        let searchTimeout;

        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(performSearch, 300);
            });
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

            if (searchTerm) searchParams.set('search', searchTerm);
            else searchParams.delete('search');
            searchParams.delete('page');

            const searchUrl = currentUrl.pathname + '?' + searchParams.toString();
            
            $('#table-container').css('opacity', '0.5');

            fetch(searchUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html'
                }
            })
            .then(res => res.text())
            .then(html => {
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = html;
                const newContent = $(tempDiv).find('#table-container').html();
                if(newContent) {
                    $('#table-container').html(newContent);
                } else {
                    // Fallback
                    $('#table-container').html(html);
                }
                history.pushState({}, '', searchUrl);
            })
            .finally(() => {
                $('#table-container').css('opacity', '1');
            });
        }

        $(document).on('click', 'a[href*="page="]', function(e) {
            e.preventDefault();
            const url = new URL(this.href);
            const currentSearch = searchInput ? searchInput.value.trim() : '';
            if (currentSearch) url.searchParams.set('search', currentSearch);

            $('#table-container').css('opacity', '0.5');
            fetch(url.href, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html'
                }
            })
            .then(res => res.text())
            .then(html => {
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = html;
                const newContent = $(tempDiv).find('#table-container').html();
                if(newContent) {
                    $('#table-container').html(newContent);
                } else {
                    $('#table-container').html(html);
                }
                history.pushState({}, '', url.href);
            })
            .finally(() => {
                $('#table-container').css('opacity', '1');
            });
        });
    });
</script>